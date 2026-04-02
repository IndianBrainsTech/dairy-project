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

function autocompleteSourceIncludes(sourceMap) {
    return function(request, response) {
        const term = request.term.toLowerCase();

        const results = Array.from(sourceMap, ([id, name]) => ({
            label: name,
            value: name,
            id: id
        })).filter(item => item.label.toLowerCase().includes(term));

        response(results);
    };
}

// Contact Number Input
function restrictToNumbersAndHyphen(selector) {
    $(document).on('keypress', selector, function(e) {
        const char = String.fromCharCode(e.which);
        const allowed = /[0-9-]/;
        if (!allowed.test(char)) e.preventDefault();
    });

    $(document).on('paste', selector, function(e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');
        if (!/^[0-9-]+$/.test(pasteData)) e.preventDefault();
    });
}

// Pin Number Input
function restrictToNumbers(selector) {
    // Keypress event
    $(document).on('keypress', selector, function(e) {
        const char = String.fromCharCode(e.which);
        const allowed = /[0-9]/;

        // Allow only digits, ignore control keys like backspace (handled automatically)
        if (!allowed.test(char)) {
            e.preventDefault();
        }
    });

    // Paste event
    $(document).on('paste', selector, function(e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');
        if (!/^[0-9]+$/.test(pasteData)) {
            e.preventDefault();
        }
    });
}

/**
 * Restrict input to numbers and alphabets
 * Useful for: PAN Number
 */
function restrictToNumbersAndAlphabets(selector) {
    // Keypress: allow only letters and digits
    $(document).on('keypress', selector, function(e) {
        const char = String.fromCharCode(e.which);
        const allowed = /[A-Za-z0-9]/;
        if (!allowed.test(char)) e.preventDefault();
    });

    // Paste: allow only letters and digits
    $(document).on('paste', selector, function(e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');
        if (!/^[A-Za-z0-9]+$/.test(pasteData)) e.preventDefault();
    });
}

function restrictToNumbersAlphabetsHyphenSpace(selector) {
    // Keypress: allow only numbers, letters, hyphen, and space
    $(document).on('keypress', selector, function(e) {
        const char = String.fromCharCode(e.which);
        const allowed = /[A-Za-z0-9\- ]/;
        if (!allowed.test(char)) e.preventDefault();
    });

    // Paste: allow only numbers, letters, hyphen, and space
    $(document).on('paste', selector, function(e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');
        if (!/^[A-Za-z0-9\- ]+$/.test(pasteData)) e.preventDefault();
    });
}

function restrictToFloatNumbers(selector) {
    // Keypress event
    $(document).on('keypress', selector, function(e) {
        const char = String.fromCharCode(e.which);
        const allowed = /[0-9.]/;

        // Allow only digits and decimal point
        if (!allowed.test(char)) {
            e.preventDefault();
            return;
        }

        // Prevent more than one decimal point
        if (char === '.' && $(this).val().includes('.')) {
            e.preventDefault();
        }
    });

    // Paste event
    $(document).on('paste', selector, function(e) {
        const pasteData = e.originalEvent.clipboardData.getData('text');

        // Allow digits and a single decimal point
        if (!/^\d*\.?\d*$/.test(pasteData)) {
            e.preventDefault();
            return;
        }

        // Prevent multiple decimal points after paste
        const currentValue = $(this).val();
        if ((currentValue + pasteData).split('.').length > 2) {
            e.preventDefault();
        }
    });
}

function moveToNextOnEnter() {
    $(document).on('keypress', 'form', function(e) {
        if (e.which === 13 && $(e.target).is('input, select, textarea')) {
            e.preventDefault();
            const fields = $(this).find('input:visible, select:visible, textarea:visible').filter(':enabled');
            const index = fields.index(e.target);
            if (index > -1 && index < fields.length - 1) {
                fields.eq(index + 1).focus();
            }
        }
    });
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

/**
 * Convert date from 'd-m-Y' to 'Y-m-d'
 * @param {string} dateStr - Date string in 'dd-mm-yyyy' format
 * @returns {string|null} - Converted date in 'yyyy-mm-dd' format or null if invalid
 */
function convertToYMD(dateStr) {
    if (!dateStr || typeof dateStr !== 'string') return null;

    const parts = dateStr.split('-');
    if (parts.length !== 3) return null;

    const [day, month, year] = parts;

    // Basic validation
    if (day.length !== 2 || month.length !== 2 || year.length !== 4) return null;

    return `${year}-${month}-${day}`;
}

function setMenuItemActive(menuId, submenuId, menuItemId) {
    // Open main menu (simulate click)
    $('a[href="#Menu' + menuId + '"]').trigger('click');

    // Open submenu
    $('#' + submenuId)
        .addClass('show')
        .attr('aria-expanded', 'true')
        .slideDown(0);

    // Set active class on menu item <li> and <a>
    $('#' + menuItemId).addClass('active');
    $('#' + menuItemId + ' > a').addClass('active');
}

function setMenuItemActive(menuId, submenuId, submenu2Id, menuItemId) {

    const $menuLink   = $('a[href="#Menu' + menuId + '"]');
    const $submenu    = $('#' + submenuId);
    const $submenu2   = $('#' + submenu2Id);
    const $menuItem   = $('#' + menuItemId);

    // 1️⃣ Open main menu
    if ($menuLink.length) {
        $menuLink.trigger('click');
    }

    // 2️⃣ Open first-level submenu
    if ($submenu.length) {
        $submenu
            .addClass('show')
            .attr('aria-expanded', 'true')
            .slideDown(0);
    }

    // 3️⃣ Open second-level submenu
    if ($submenu2.length) {
        $submenu2
            .addClass('show')
            .attr('aria-expanded', 'true')
            .slideDown(0);
    }

    // 4️⃣ Set active states
    if ($menuItem.length) {
        $menuItem.addClass('active');
        $menuItem.children('a').addClass('active');

        // Optional: activate parent submenu items
        $menuItem.closest('li').parents('li').addClass('active');
    }
}