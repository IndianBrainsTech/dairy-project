<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transport\DieselBillStatement;

class PdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
/*
    public function generatePDF(Request $request)
    {
        // Get the HTML content sent from the client
        $html = $request->input('html');        

        // Load the HTML into DomPDF
        $pdf = Pdf::loadHTML($html);

        // Option 1: Download the PDF
        // return $pdf->download('statement.pdf');

        // Option 2: Stream in browser (uncomment if preferred)
        return $pdf->stream('statement.pdf');
    }
*/

    public function generatePDF(Request $request)
    {        
        $record = DieselBillStatement::findOrFail(6);
        $pdf = Pdf::loadView('transactions.diesel-bills.statements.show-pdf', [
            'record' => $record,
        ]);
        return $pdf->download('statement.pdf');
    }
}
