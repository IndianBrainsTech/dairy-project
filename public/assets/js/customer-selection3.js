let routes = new Map();
let customers = new Map();
loadRoutes();

function loadRoutes() {
    $('#route-id').val(0);
    $('#route-name').val('');
    $.get(ROUTES_LIST_URL)
        .done(function(response) {
            let routeList = response.routes;
            routeList.forEach(function(route) {
                routes.set(route.name, route.id); // key = name, value = id
            });

            $('#route-name').autocomplete({
                source: autocompleteSource(routes),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const name = ui.item.value;
                    const id = routes.get(name);
                    console.log("Selected Route => ID: " + id + ", Name: " + name);
                    $('#route-id').val(id);
                    loadCustomers(); // Load customers for the selected route
                }
            });

            loadCustomers(); // Load all customers for fresh load

            if(selectedRoute) {
                $('#route-id').val(selectedRoute.id);
                $('#route-name').val(selectedRoute.name);
            }
            if(selectedCustomer) {
                $('#customer-id').val(selectedCustomer.id);
                $('#customer-name').val(selectedCustomer.name);
            }
        })
        .fail(function(xhr) {
            console.log("Error: " + xhr.responseText);
        });
}

function loadCustomers() {
    $('#customer-id').val(0);
    $('#customer-name').val('');
    customers = new Map();
    let routeId = $('#route-id').val();
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
    });
}

$('#route-name').on('blur', function () {
    let routeName = $('#route-name').val();
    if(routeName == "") {
        $('#route-id').val(0);
        loadCustomers(); // Load all customers
    }
    else if(!routes.has(routeName)){
        const routeId = $('#route-id').val();
        routeName = getKeyByValue(routes, routeId);
        $('#route-name').val(routeName);
    }
});

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