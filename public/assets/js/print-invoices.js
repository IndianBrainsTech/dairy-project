$(function() {
    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });    

    $('#btnPrint').on("click", function () {
        // Get the Actual Page Content
        let originalContents = $('body').html();
        let printDiv = $('<div></div>');

        // Select all .invoice-wrapper elements and loop through them
        $('.invoice-wrapper').each(function(index, element) {
            // Within each .invoice-wrapper, find the .sales-invoice-wrapper and .tax-invoice-wrapper
            let salesInvoiceWrapper = $(element).find('#sales-invoice-wrapper');
            let taxInvoiceWrapper = $(element).find('#tax-invoice-wrapper');
            
            // Get the HTML content of the invoice-wrappers
            let salesContents = salesInvoiceWrapper.html();
            let taxContents = taxInvoiceWrapper.html();

            if(index != 0) {
                printDiv.append('<div class="page-break"></div>');
            }

            if(salesContents) {
                printInvoice(printDiv, salesContents);
            }

            // Add page break if sales and tax invoices exists
            if(salesContents && taxContents) {
                printDiv.append('<div class="page-break"></div>');
            }

            if(taxContents) {
                printInvoice(printDiv, taxContents);
            }
        });

        // Load the print div to the body
        $('body').html(printDiv);

        // Print the page
        window.print();

        // Replace the original content after printing
        $('body').html(originalContents);
    });

    $('.tax-table td:nth-last-child(-n+3)').each(function() {
        if ($(this).text().trim() === '-') {
            $(this).addClass('centered');
        }
    });

    function printInvoice(printDiv, contents) {
        let tempDiv = $('<div class="print-content temp-measure"></div>').html(contents);
        $('body').append(tempDiv);
        let contentHeight = tempDiv.outerHeight();
        tempDiv.remove();
        console.log(contentHeight);

        // Create a new div for the invoice content
        let invoiceDiv = $('<div class="print-content"></div>').html(contents);        
        console.log("Content Height : " + contentHeight);

        // Small Invoice (2 copy in a page)
        if (contentHeight < 600) {
            // Append a cutting line in half of the page
            invoiceDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

            // Append a duplicate of the invoice content
            invoiceDiv.append('<div class="print-duplicate">' + contents + '</div>');

            // Append invoice to the print content
            printDiv.append(invoiceDiv);
        }
        // Large Invoice (Each copy in individual page)
        else { 
            // Append a cutting line below invoice
            invoiceDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

            // Append invoice to the print content
            printDiv.append(invoiceDiv);

            // Next page
            printDiv.append('<div class="page-break"></div>');

            // Append a duplicate of the invoice content
            invoiceDiv = $('<div class="print-duplicate pt-1"></div>').html(contents);            

            // Append a cutting line below invoice
            invoiceDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

            // Append invoice to the print content
            printDiv.append(invoiceDiv);
        }
    }
});