let customers = new Map();
loadCustomers();

function loadCustomers() {    
    $('#customer-id').val(0);
    $('#customer-name').val('');
    customers = new Map();
    let routeId = 0;
    let url = CUSTOMERS_BY_ROUTE_URL.replace(':id', routeId);
    $.get(url, function (response) {
        let customerList = response.customers;
        customerList.forEach(function(customer) {
            customers.set(customer.customer_name, customer.id); // key = name, value = id
        });

        $('#customer-name').autocomplete({
            source: autocompleteSource(customers),
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                var name = ui.item.value;
                var id = customers.get(name);
                console.log("Selected Customer => ID: " + id + ", Name: " + name);
                $('#customer-id').val(id).trigger('change');
            }
        });

        if ($('#customer-name').data('ui-autocomplete')) {
            $('#customer-name').autocomplete('option', 'source', autocompleteSource(customers));
            $("#customer-id").val(0);
        }

        if(selectedCustomer) {
            $('#customer-id').val(selectedCustomer.id);
            $('#customer-name').val(selectedCustomer.name);
        }
    });
}

$('#customer-name').on('blur', function () { 
    let customerName = $('#customer-name').val();
    if(customerName == "") {
        $('#customer-id').val(0);
    }
    else if(!customers.has(customerName)){
        const customerId = $('#customer-id').val();
        customerName = getKeyByValue(customers, customerId);
        $('#customer-name').val(customerName);
    }
});

function autocompleteSource(sourceMap) {
    return function(request, response) {
        let results = Array.from(sourceMap.keys()).map(function(key) {
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

function getKeyByValue(map, searchValue) {
    for (let [key, value] of map.entries()) {
        if (value == searchValue) {
            return key;
        }
    }
    return null; // Not found
}