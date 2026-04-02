/**
 * Handles AJAX error responses and displays a user-friendly error message using SweetAlert2.
 * 
 * If the response contains validation errors (HTTP 422), it extracts and displays the first error message.
 * Otherwise, it attempts to use the message from the response, the passed error, or a fallback message.
 * Also logs detailed error information to the console for developer debugging.
 * 
 * @param {jqXHR} xhr - The jQuery XMLHttpRequest object containing the full response.
 * @param {string} status - A string describing the status (e.g., "error", "timeout").
 * @param {string} error - The error message thrown by the AJAX engine.
 * @param {string} [fallbackMessage='Something went wrong.'] - Optional fallback message to display if no other message is found.
 */
function handleAjaxError(xhr, status, error, fallbackMessage = 'Something went wrong.') {
    const response = xhr.responseJSON || {};
    let errorMessage = fallbackMessage;

    if (xhr.status === 422 && response.errors) {
        const firstField = Object.keys(response.errors)[0];
        errorMessage = response.errors[firstField]?.[0] || fallbackMessage;
    } else {
        errorMessage = response.message || error || fallbackMessage;
    }

    // Show to user
    Swal.fire('Sorry', errorMessage, 'error');

    // Log to console for developers
    console.error('AJAX Error:', {
        status: status,
        error: error,
        message: response.message || fallbackMessage,
        response: xhr.responseText,
    });
}

/**
 * Creates a jQuery UI Autocomplete `source` function from a Map of items.
 * 
 * Converts each entry in the provided Map into an object with `label`, `value`,
 * and `id` properties, where:
 *   - `label` (string) → text displayed in the dropdown suggestions
 *   - `value` (string) → text inserted into the input when an item is selected
 *   - `id` (number|string) → the original key from the Map, available in the `select` callback
 * 
 * Filters suggestions so that only items whose label starts with the
 * typed search term (case-insensitive) are shown.
 * 
 * @param {Map<number|string, string>} sourceMap 
 *        A Map where the key is the item ID (number or string)
 *        and the value is the item name (string).
 * 
 * @returns {Function} 
 *          A function `(request, response)` that can be passed as the 
 *          `source` option in jQuery UI Autocomplete.
 */ 
function autocompleteSource(sourceMap) {
    return function(request, response) {
        const term = request.term.toLowerCase();

        const results = Array.from(sourceMap, ([id, name]) => ({
            label: name,
            value: name,
            id: id
        })).filter(item => item.label.toLowerCase().startsWith(term));

        response(results);
    };
}

/**
 * Re-indexes the serial numbers (S.No) in the first column of a table's body.
 * 
 * Iterates through all rows in the `<tbody>` of the given table and updates
 * the first `<td>` cell of each row to display its current position (starting from 1).
 * Useful for maintaining correct numbering after adding, removing, or reordering rows.
 * 
 * @param {string} tableId - The selector for the target table (e.g., '#myTable').
 */
function reindexTable(tableId) {
    $(tableId + ' tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

/**
 * Returns an empty string if the given value is zero, otherwise returns the value.
 *
 * This is useful for displaying numeric fields where zero should appear blank
 * in the UI (e.g., quantity columns in a table).
 *
 * @param {number|string} data - The value to check. Can be a number or numeric string.
 * @returns {string|number} - Returns '' if the value is 0, otherwise returns the original value.
 */
function getEmptyForZero(data) {
    return parseInt(data) == 0 ? '' : data;
}



