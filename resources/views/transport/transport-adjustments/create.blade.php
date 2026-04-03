@extends('app-layouts.admin-master')
@section('title', 'Add Transport Adjustment')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Transport Adjustment @endslot
            @slot('item1') Transport @endslot
            @slot('item2') Add Transport Adjustment @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-8 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.transport-adjustments.store') }}">@csrf 
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Adjustment Number <small class="text-danger">*</small></label>
                        <input type="text" name="adjustment_number" value="{{ old('adjustment_number', $adjustmentNumber) }}" class="form-control @error('adjustment_number') is-invalid @enderror">
                        @error('adjustment_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Date <small class="text-danger">*</small></label>
                        <input type="date" name="adjustment_date" value="{{ old('adjustment_date', now()->format('Y-m-d')) }}" class="form-control @error('adjustment_date') is-invalid @enderror">
                        @error('adjustment_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Vehicle</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Type <small class="text-danger">*</small></label>
                        <select name="adjustment_type" class="form-control @error('adjustment_type') is-invalid @enderror">
                            <option value="debit"  @selected(old('adjustment_type','debit')==='debit')>Debit (reduce payment)</option>
                            <option value="credit" @selected(old('adjustment_type')==='credit')>Credit (increase payment)</option>
                        </select>
                        @error('adjustment_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Reason <small class="text-danger">*</small></label>
                        <select name="reason" class="form-control @error('reason') is-invalid @enderror">
                            @foreach(['damage','shortage','delay','toll','loading_unloading','other'] as $r)
                                <option value="{{ $r }}" @selected(old('reason')===$r)>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                            @endforeach
                        </select>
                        @error('reason')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Amount <small class="text-danger">*</small></label>
                        <input type="number" name="amount" step="0.01" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" min="0.01">
                        @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="form-group"><label>Reason Description</label>
                    <input type="text" name="reason_description" value="{{ old('reason_description') }}" class="form-control" maxlength="255">
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.transport-adjustments.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop