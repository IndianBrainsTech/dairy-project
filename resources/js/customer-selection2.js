let customers = new Map();

function initializeCustomerSelection(customerSelector, routeSelector, nextFieldSelector, customersUrl) {
    return new Promise((resolve, reject) => {
        $(routeSelector).on('change', function () {
            handleRouteChange(customerSelector, routeSelector, customersUrl);
        });

        handleRouteChange(customerSelector, routeSelector, customersUrl).then(() => {
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
                        $(nextFieldSelector)[0].focus();
                        enterCount = 0;
                    }
                }
            });

            $(customerSelector).on('blur', function() {
                enterCount = 0;
                if(!$("#customer").val())
                    $("#customerId").val(0);
            });

            resolve(); // tell caller that init is complete
        });
    });
}

function handleRouteChange(customerSelector, routeSelector, customersUrl) {
    return new Promise((resolve, reject) => {
        var id = $(routeSelector).val();
        $(customerSelector).val('');
        customers = new Map();
        let url = customersUrl.replace(':id', id);
        $.get(url, function (data) {
            let customerList = data.customers;
            customerList.forEach(function(customer) {
                customers.set(customer.customer_name, customer.id);
            });            
            if ($(customerSelector).data('ui-autocomplete')) {
                $(customerSelector).autocomplete('option', 'source', autocompleteSource(customers));
                $("#customerId").val(0);
            }
            resolve(); // resolve when data is loaded
        }).fail(reject);
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
