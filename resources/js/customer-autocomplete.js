function initCustomerAutocomplete(customersJson) {
    let customers = new Map();

    customersJson.forEach(customer => {
        customers.set(customer.customer_name, customer.id);
    });

    function autocompleteSource(sourceMap) {
        return function(request, response) {
            let results = Array.from(sourceMap.entries()).map(([key]) => ({
                label: key,
                value: key
            })).filter(item =>
                item.label.toLowerCase().startsWith(request.term.toLowerCase())
            );
            response(results);
        };
    }

    $("#customer").autocomplete({
        source: autocompleteSource(customers),
        autoFocus: true,
        minLength: 0,
        select: function(event, ui) {
            let name = ui.item.value;
            let id = customers.get(name);
            console.log("Selected ID: " + id + ", Name: " + name);
            $("#customerId").val(id);
        }
    });

    $("#customer").on('blur', function() {
        if(!$("#customer").val())
            $("#customerId").val(0);
    });

    return customers;
}