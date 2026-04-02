function isExtensionValid(image_name) {
    var allowed_extensions = new Array("jpg","jpeg","png","gif");
    var file_extension = image_name.split('.').pop().toLowerCase();
    for(var i = 0; i <= allowed_extensions.length; i++) {
        if(allowed_extensions[i] == file_extension) {
            return true;
        }
    }
    return false;
}

function getRoundOffString(roundOff) {
    return (roundOff > 0 ? '+' : '') + roundOff.toFixed(2);
}

// Function to update the serial numbers (S.No) in the table
function updateSerialNumbers(tableSelector) {
    // Update the serial number cell for each row
    $(tableSelector + " tbody tr").each(function(index) {
        $(this).find("td:first").text(index + 1);
    });
}

function findRow(sno, tableSelector) {
    let foundRow = null;
    $(tableSelector + " tbody tr").each(function() {
        const rowId = $(this).find('td:nth-child(1)').text();
        if (rowId === sno) {
            foundRow = $(this);
            return false; // Exit the loop
        }
    });
    return foundRow;
}

function handleEnterKey(e) {
    if (e.which === 13) { // Enter key
        e.preventDefault();
        let currentTabIndex = $(this).attr('tabindex');
        let nextElement = $('[tabindex="' + (parseInt(currentTabIndex) + 1) + '"]');
        if (nextElement.length) {
            nextElement.focus();
        }
    }
}

function getKeyByValue(map, value) {
    for (let [key, val] of map.entries()) {
        if (val === value) {
            return key; // Return the key if the value matches
        }
    }
    return null; // Return null if the value is not found
}

// Utility function to extract ID from element
function getIdFromElement(element, prefix) {
    return $(element).attr('id').replace(prefix, '');
}

// Function to show a Swal alert with a custom message
function showAlert(message) {
    Swal.fire('Attention', message, 'warning');
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

function autocompleteSource1(sourceMap) {
    return function(request, response) {
        const results = Array.from(sourceMap.entries())
            .map(([key, value]) => ({
                label: value,
                value: value
            }))
            .filter(item => item.label.toLowerCase().startsWith(request.term.toLowerCase()));

        response(results);
    };
}

function getRoundOffString(roundOff) {
    let formattedRoundOff = (roundOff > 0 ? '+' : '') + roundOff.toFixed(2);
    return formattedRoundOff;
}

function formatIndianNumber(num) {
    let str = num.toString();
    let lastThree = str.slice(-3);
    let otherDigits = str.slice(0, -3);
    if (otherDigits !== '') {
        lastThree = ',' + lastThree;
    }
    return otherDigits.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
}

function formatToIndianNumberFormat(number, precision=false) {
    // Ensure number has exactly 2 decimal places
    let [intPart, decPart] = Number(number).toFixed(2).split('.');

    // Format integer part in Indian style
    let lastThree = intPart.slice(-3);
    let otherDigits = intPart.slice(0, -3);
    if (otherDigits !== '') {
        lastThree = ',' + lastThree;
    }
    let formattedInt = otherDigits.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;

    if(precision)
        return `${formattedInt}.${decPart}`;
    else
        return formattedInt;
}

// Re-index S.No of table
function reindexTable(tableId) {
    $(tableId + ' tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}
