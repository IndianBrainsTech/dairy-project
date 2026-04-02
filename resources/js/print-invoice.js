$(function() {
    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });

    $('#btnPrint').on("click", function () {
        // Get the Actual Page Content
        var originalContents = $('body').html();

        // Get the HTML content of the invoice-wrapper
        var printContents = $('#invoice-wrapper').html();

        // Create a new div for the printed content
        var printDiv = $('<div class="print-content"></div>');
        printDiv.html(printContents);

        // Append a cutting line in half of the page
        printDiv.append('<div class="cutting-line"><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span><span class="scissor-symbol">✂</span></div>');

        // Append a duplicate of the printed content to the print div
        printDiv.append('<div class="print-duplicate">' + printContents + '</div>');

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