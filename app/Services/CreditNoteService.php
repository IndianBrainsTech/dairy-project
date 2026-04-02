<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Models\Transactions\CreditNotes\CreditNoteItem;
use App\Services\InvoiceService;
use App\Enums\DocumentStatus;
use App\Enums\SqlAction;

class CreditNoteService
{
    public function generateDocumentNumber(): string
    {
        $lastNumber = CreditNote::latest('id')->value('document_number');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 4)) + 1 : 1;
        return 'CRN-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function adjustReceivables(array $receivables, int $creditNoteId): Collection
    {
        $adjustments = CreditNoteItem::where('credit_note_id', $creditNoteId)
            ->get(['id', 'invoice_number', 'adjusted_amount'])
            ->keyBy('invoice_number');

        $receivables = collect($receivables)->map(function ($item) use ($adjustments) {
            $adjustment = $adjustments->get($item['invoice_num']);

            $item['adjustment'] = $adjustment ? (float) $adjustment->adjusted_amount : null;
            $item['record_id'] = $adjustment ? $adjustment->id : -1;

            if($item['adjustment']) {
                $item['outstanding'] += $item['adjustment'];
                $item['draft_amt'] -= $item['adjustment'];
            }

            return $item;
        });

        return $receivables;
    }

    public function store(array $data): void
    {
        DB::transaction(function () use ($data) {
            $totalAmount = collect($data['items'])->sum('adjusted_amount');

            $creditNote = CreditNote::create([
                'document_number' => $this->generateDocumentNumber(),
                'document_date'   => $data['document_date'],
                'customer_id'     => $data['customer_id'],
                'reason'          => $data['reason'],
                'narration'       => $data['narration'],
                'amount'          => $totalAmount,
                'status'          => DocumentStatus::DRAFT,
                'current_version' => 1,
            ]);

            $items = collect($data['items'])
                ->map(function ($item) use ($data) {
                    if ($item['invoice_number'] === 'Opening Amount') {
                        $item['invoice_number'] = $data['customer_id'] . ' - OpeningAmt';
                    }
                    return $item;
                })
                ->toArray();

            $creditNote->items()->createMany($items);
        });
    }

    public function update(CreditNote $creditNote, array $data): void
    {
        DB::transaction(function () use ($creditNote, $data) {
            // Lock row (prevents version race condition)
            $creditNote = CreditNote::where('id', $creditNote->id)
                ->lockForUpdate()
                ->first();

            $userId = auth()->id();
            $now = now();

            // Generate version number
            $currentVersion = $creditNote->current_version;
            $newVersion = $currentVersion + 1;

            $totalAmount = collect($data['items'])->sum('adjusted_amount');

            // Load current items
            $currentItems = DB::table('credit_note_items')
                ->where('credit_note_id', $creditNote->id)
                ->get()
                ->keyBy('id');

            // Prepare trackers
            $recordChanges = [];
            $itemChanges = [];
            $hasChanges = false;

            // -------------------------
            // 1. Bootstrap history (safe)
            // -------------------------
            $hasHistory = DB::table('credit_notes_history')
                ->where('credit_note_id', $creditNote->id)
                ->exists();

            if (!$hasHistory) {
                $recordChanges[] = $this->snapshotRecord($creditNote, 1, $creditNote->created_by, SqlAction::CREATE);

                foreach ($currentItems as $item) {
                    $itemChanges[] = $this->snapshotItem($item, 1, $creditNote->created_by, SqlAction::CREATE);
                }
            }

            // -------------------------
            // 2. Track credit note changes
            // -------------------------
            if ($this->hasRecordChanged($creditNote, $data, $totalAmount)) {

                $recordChanges[] = [
                    ...$this->recordPayload($data, $totalAmount),
                    'credit_note_id'  => $creditNote->id,
                    'document_number' => $creditNote->document_number,
                    'version_code'    => $newVersion,
                    'user_id'         => $userId,
                    'sql_action'      => SqlAction::UPDATE,
                    'created_at'      => $creditNote->created_at,
                    'updated_at'      => $creditNote->updated_at,
                ];

                $creditNote->update([
                    ...$this->recordPayload($data, $totalAmount),
                    'updated_by'      => $userId,
                    'updated_at'      => $now,
                ]);

                $hasChanges = true;
            }

            // -------------------------
            // 3. Track credit note items changes
            // -------------------------
            foreach ($data['items'] as $item) {

                $recordId = (int) $item['record_id'];

                if ($recordId === -1) {
                    // NEW record → INSERT
                    $newId = DB::table('credit_note_items')->insertGetId([
                        ...$this->itemPayload($item, $creditNote->id),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $inserted = DB::table('credit_note_items')->where('id', $newId)->first();

                    $itemChanges[] = $this->snapshotItem($inserted, $newVersion, $userId, SqlAction::INSERT);

                    $hasChanges = true;
                }
                elseif (isset($currentItems[$recordId])) {
                    // EXISTING record → UPDATE check
                    $current = (array) $currentItems[$recordId];

                    if ($this->hasItemChanged($current, $item)) {

                        $itemPayload = $this->itemPayload($item, $creditNote->id);

                        $itemChanges[] = [
                            ...$itemPayload,
                            'version_code'       => $newVersion,
                            'record_id'          => $recordId,
                            'user_id'            => $userId,
                            'sql_action'         => SqlAction::UPDATE,
                            'created_at'         => $creditNote->created_at,
                            'updated_at'         => $now,
                        ];

                        DB::table('credit_note_items')
                            ->where('id', $recordId)
                            ->update([
                                ...$itemPayload,
                                'updated_at' => $now,
                            ]);

                        $hasChanges = true;
                    }

                    unset($currentItems[$recordId]); // mark processed
                }
            }

            // -------------------------
            // 4. DELETE detection
            // -------------------------
            foreach ($currentItems as $deleted) {

                $itemChanges[] = $this->snapshotItem($deleted, $newVersion, $userId, SqlAction::DELETE);

                DB::table('credit_note_items')
                    ->where('id', $deleted->id)
                    ->delete();

                $hasChanges = true;
            }

            // -------------------------
            // 5. Persist history
            // -------------------------
            if (!empty($recordChanges)) {
                DB::table('credit_notes_history')->insert($recordChanges);
            }

            if (!empty($itemChanges)) {
                DB::table('credit_note_items_history')->insert($itemChanges);
            }

            // -------------------------
            // 6. Version bump
            // -------------------------
            if ($hasChanges) {
                $creditNote->update([
                    'current_version' => $newVersion
                ]);
            }
        });
    }

    public function updateApproval(array $ids, InvoiceService $invoiceService): void
    {
        DB::transaction(function () use ($ids, $invoiceService) {
            CreditNote::whereIn('id', $ids)
                ->update([
                    'status' => DocumentStatus::APPROVED,
                    'actioned_by' => auth()->id(),
                    'actioned_at' => now(),
                ]);

            $invoiceNumbers = CreditNoteItem::whereIn('credit_note_id', $ids)
                ->whereColumn('outstanding_amount', 'adjusted_amount')
                ->pluck('invoice_number')
                ->toArray();

            $invoiceService->updateStatusAsPaid($invoiceNumbers);
        });
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private function hasRecordChanged(CreditNote $cn, array $data, float $total): bool
    {
        return (
            $cn->document_date != $data['document_date'] ||
            $cn->customer_id   != $data['customer_id'] ||
            $cn->reason        != $data['reason'] ||
            $cn->narration     != $data['narration'] ||
            (float)$cn->amount !== (float)$total
        );
    }

    private function hasItemChanged(array $current, array $item): bool
    {
        return (
            $current['invoice_number']      != $item['invoice_number'] ||
            $current['invoice_date']        != $item['invoice_date'] ||
            (float)$current['invoice_amount'] != (float)$item['invoice_amount'] ||
            (float)$current['paid_amount']    != (float)$item['paid_amount'] ||
            (float)$current['outstanding_amount'] != (float)$item['outstanding_amount'] ||
            (float)$current['adjusted_amount']    != (float)$item['adjusted_amount']
        );
    }

    private function snapshotRecord(CreditNote $cn, int $version, int $userId, SqlAction $action): array
    {
        return [
            'credit_note_id'  => $cn->id,
            'document_number' => $cn->document_number,
            'document_date'   => $cn->document_date,
            'customer_id'     => $cn->customer_id,
            'reason'          => $cn->reason,
            'narration'       => $cn->narration,
            'amount'          => $cn->amount,
            'version_code'    => $version,
            'user_id'         => $userId,
            'sql_action'      => $action,
            'created_at'      => $cn->created_at,
            'updated_at'      => $cn->updated_at,
        ];
    }

    private function snapshotItem(object $item, int $version, int $userId, SqlAction $action): array
    {
        return [
            'credit_note_id'     => $item->credit_note_id,
            'invoice_number'     => $item->invoice_number,
            'invoice_date'       => $item->invoice_date,
            'invoice_amount'     => $item->invoice_amount,
            'paid_amount'        => $item->paid_amount,
            'outstanding_amount' => $item->outstanding_amount,
            'adjusted_amount'    => $item->adjusted_amount,
            'version_code'       => $version,
            'record_id'          => $item->id,
            'user_id'            => $userId,
            'sql_action'         => $action,
            'created_at'         => $item->created_at ?? now(),
            'updated_at'         => now(),
        ];
    }

    private function recordPayload(array $record, float $amount): array
    {
        return [
            'document_date' => $record['document_date'],
            'customer_id'   => $record['customer_id'],
            'reason'        => $record['reason'],
            'narration'     => $record['narration'],
            'amount'        => $amount,
        ];
    }

    private function itemPayload(array $item, int $creditNoteId): array
    {
        return [
            'credit_note_id'     => $creditNoteId,
            'invoice_number'     => $item['invoice_number'],
            'invoice_date'       => $item['invoice_date'],
            'invoice_amount'     => $item['invoice_amount'],
            'paid_amount'        => $item['paid_amount'],
            'outstanding_amount' => $item['outstanding_amount'],
            'adjusted_amount'    => $item['adjusted_amount'],
        ];
    }
}