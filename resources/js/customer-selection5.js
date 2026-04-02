let customers = new Map();
loadCustomers();

function loadCustomers() {    
    $('#hdn-customer-id').val(0);
    $('#act-customer-name').val('');
    customers = new Map();
    let routeId = 0;
    let url = CUSTOMERS_BY_ROUTE_URL.replace(':id', routeId);
    $.get(url, function (response) {
        let customerList = response.customers;
        customerList.forEach(function(customer) {
            customers.set(customer.customer_name, customer.id); // key = name, value = id
        });

        $('#act-customer-name').autocomplete({
            source: autocompleteSource(customers),
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                var name = ui.item.value;
                var id = customers.get(name);
                console.log("Selected Customer => ID: " + id + ", Name: " + name);
                $('#hdn-customer-id').val(id).trigger('change');
            }
        });

        if ($('#act-customer-name').data('ui-autocomplete')) {
            $('#act-customer-name').autocomplete('option', 'source', autocompleteSource(customers));
            $("#hdn-customer-id").val(0);
        }

        if(selectedCustomer) {
            $('#hdn-customer-id').val(selectedCustomer.id);
            $('#act-customer-name').val(selectedCustomer.name);
        }
    });
}

$('#act-customer-name').on('blur', function () { 
    let customerName = $('#act-customer-name').val();
    if(customerName == "") {
        $('#hdn-customer-id').val(0);
    }
    else if(!customers.has(customerName)){
        const customerId = $('#hdn-customer-id').val();
        customerName = getKeyByValue(customers, customerId);
        $('#act-customer-name').val(customerName);
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