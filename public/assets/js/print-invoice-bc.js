$(function() {
    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });
 
    window.printInvoice = function(labels) {
        // Save the original page content
        var originalContents = $('body').html();
    
        // Container to hold all copies for printing
        var printContainer = $('<div></div>');
    
        // Loop through each label and create a copy of the invoice
        labels.forEach(function(label) {
            // Create a div for each invoice
            var invoiceDiv = $('<div class="print-content"></div>');

            // Clone the invoice wrapper
            var invoiceClone = $('#invoice-wrapper').clone();
    
            // Set the label text
            invoiceClone.find('#invoice-for').text(label).removeClass("d-none");
    
            // Add some space between copies (optional)
            invoiceClone.css('page-break-after', 'always');

            // Load invoice wrapper into invoice div
            invoiceDiv.html(invoiceClone);
    
            // Append the invoice to the print container
            printContainer.append(invoiceDiv);
        });
    
        // Replace the body content with the print container
        $('body').html(printContainer.html());

        // Print the page
        window.print();
    
        // Restore the original content after printing
        $('body').html(originalContents);
    }
    
});