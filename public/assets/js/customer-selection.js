let customers = new Map();

function initializeCustomerSelection(customerSelector, routeSelector, nextFieldSelector, customersUrl) {    
    $(routeSelector).on('change', function () {
        handleRouteChange(customerSelector, routeSelector, customersUrl);
    });

    handleRouteChange(customerSelector, routeSelector, customersUrl);    

    // AutoComplete Setup
    $(customerSelector).autocomplete({
        source: autocompleteSource(customers),
        autoFocus: true,
        minLength: 0,
        select: function(event, ui) {
            var name = ui.item.value;
            var id = customers.get(name);
            console.log("Selected Customer => ID: " + id + ", Name: " + name);
            $(`${customerSelector}Id`).val(id).trigger('change');
        }
    });

    // Next Field Focus on 'Enter'
    let enterCount = 0;

    $(customerSelector).on('keydown', function(e) {
        if (e.key === "Enter") {
            enterCount++;
            
            if (enterCount === 2) {
                // Shift focus to the next input field on the second 'Enter' press
                $(nextFieldSelector)[0].focus();                
                // Reset counter after focus is shifted
                enterCount = 0; 
            }
        }
    });

    $(customerSelector).on('blur', function() {
        // Reset counter when focus leaves the input field
        enterCount = 0;
        if(!$("#customer").val())
            $("#customerId").val(0);
    });
}

function handleRouteChange(customerSelector, routeSelector, customersUrl) {
    var id = $(routeSelector).val();
    $(customerSelector).val('');
    customers = new Map();
    let url = customersUrl.replace(':id', id);
    $.get(url, function (data) {
        let customerList = data.customers;
        customerList.forEach(function(customer) {
            customers.set(customer.customer_name, customer.id); // key, value
        });
        // Update the autocomplete source after updating customers
        $(customerSelector).autocomplete('option', 'source', autocompleteSource(customers));
        $("#customerId").val(0);
    });
}

function autocompleteSource(sourceMap) {
    return function(request, response) {
        let results = Array.from(sourceMap.entries()).map(function([key, value]) {
            return {
                label: key,
                value: key
            };
        }).filter(function(item) {
            return item.label.toLowerCase().startsWith(request.term.toLowerCase());
        });
        response(results);
    };
}

$("#customer").on('blur', function() {
    if(!$("#customer").val())
        $("#customerId").val(0);
});
