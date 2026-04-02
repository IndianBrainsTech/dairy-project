$(function() {
    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });

    $('#btnPrint').on("click", function () {
        // Get the Actual Page Content
        var originalContents = $('body').html();

        // Get the HTML content of the invoice-wrappers
        var salesContents = $('#sales-invoice-wrapper').html();
        var taxContents = $('#tax-invoice-wrapper').html();
        var printDiv = $('<div></div>');

        if(salesContents) {
            // Create a new div for the invoice content
            var salesDiv = $('<div class="print-content"></div>');
            salesDiv.html(salesContents);

            // Append a cutting line in half of the page
            salesDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

            // Append a duplicate of the invoice content
            salesDiv.append('<div class="print-duplicate">' + salesContents + '</div>');

            // Append sales invoice to the print content
            printDiv.append(salesDiv);
        }

        // Add page break if sales and tax invoices exists
        if(salesContents && taxContents) {
            printDiv.append('<div class="page-break"></div>');
        }

        if(taxContents) {
            // Create a new div for the invoice content
            var taxDiv = $('<div class="print-content"></div>');
            taxDiv.html(taxContents);

            // Append a cutting line in half of the page
            taxDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

            // Append a duplicate of the invoice content
            taxDiv.append('<div class="print-duplicate">' + taxContents + '</div>');

            // Append tax invoice to the print content
            printDiv.append(taxDiv);
        }

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
});