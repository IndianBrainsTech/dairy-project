let items = new Map();
loadItems();

function loadItems() {
    $('#item-id').val(0);
    $('#item-name').val('');
    items = new Map();
    let status = ITEMS_STATUS;
    let url = ITEMS_URL.replace(':status', status);
    $.get(url, function (response) {
        let itemList = response.items;
        itemList.forEach(function(item) {
            items.set(item.name, item.id); // key = name, value = id
        });

        $('#item-name').autocomplete({
            source: autocompleteSource(items),
            autoFocus: true,
            minLength: 0,
            select: function(event, ui) {
                var name = ui.item.value;
                var id = items.get(name);
                console.log("Selected Item => ID: " + id + ", Name: " + name);
                $('#item-id').val(id).trigger('change');
            }
        });

        if ($('#item-name').data('ui-autocomplete')) {
            $('#item-name').autocomplete('option', 'source', autocompleteSource(items));
            $("#item-id").val(0);
        }

        if(selectedItem) {
            $('#item-id').val(selectedItem.id);
            $('#item-name').val(selectedItem.name);
        }
    });
}

$('#item-name').on('blur', function () {
    let itemName = $('#item-name').val();
    if(itemName == "") {
        $('#item-id').val(0);
    }
    else if(!items.has(itemName)){
        const itemId = $('#item-id').val();
        itemName = getKeyByValue(items, itemId);
        $('#item-name').val(itemName);
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