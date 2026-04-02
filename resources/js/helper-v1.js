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

