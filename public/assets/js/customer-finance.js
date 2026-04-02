$(function() {
    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });

    // Initialize DataTable with custom length menu and default page length
    $('#datatable').dataTable({
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        pageLength: -1
    });

    // Handle edit button click event
    $('body').on('click', '[id^=edit]', function (event) {
        event.preventDefault();
        let id = getIdFromElement(this, 'edit');
        resetData(`#amount${id}`, false);
        resetData(`#date${id}`, false);
        toggleEditMode(id, true);
        $(`#amount${id}`).focus();
    });

    // Handle update button click event
    $('body').on('click', '[id^=update]', function (event) {
        event.preventDefault();
        let id = getIdFromElement(this, 'update');
        let amount = $(`#amount${id}`).val();
        let date = $(`#date${id}`).val();

        // Validate input fields
        if (!amount && date) {
            showAlert('Please Enter Amount');
        } else if (!date && amount) {
            showAlert('Please Enter Date');
        } else {
            updateCustomerData(id, amount, date);
        }
    });

    // Handle clear button click event
    $('body').on('click', '[id^=clear]', function (event) {
        event.preventDefault();
        let id = getIdFromElement(this, 'clear');
        loadData(`#amount${id}`);
        loadData(`#date${id}`);
        toggleEditMode(id, false);
    });

    // Utility function to extract ID from element
    function getIdFromElement(element, prefix) {
        return $(element).attr('id').replace(prefix, '');
    }

    // Function to toggle edit mode UI
    function toggleEditMode(id, isEditing) {
        $(`#amount${id}, #date${id}`).prop('disabled', !isEditing);
        $(`#update${id}, #clear${id}`).toggleClass('d-none', !isEditing);
        $(`#edit${id}`).toggleClass('d-none', isEditing);
    }

    // Function to reset data value for an input
    function resetData(selector, disabled = true) {
        let element = $(selector);
        element.data('value', element.val());
        element.attr('data-value', element.val());
        element.prop('disabled', disabled);
    }

    // Function to load data value back into an input
    function loadData(selector) {
        let element = $(selector);
        element.val(element.data('value'));
    }

    // Function to update customer data via AJAX
    function updateCustomerData(id, amount, date) {
        let name = $(`#name${id}`).text();
        $.ajax({
            url: getRouteUrl(),
            type: "POST",
            data: {
                cust_id: id,
                name: name,
                amount: amount,
                tdate: date
            },
            dataType: 'json',
            success: function (data) {
                let amt = Math.round($(`#amount${id}`).val());
                $(`#amount${id}`).val(amt);
                resetData(`#amount${id}`);
                resetData(`#date${id}`);
                console.log(data);
                console.log("Customer Data Updated!");
                toggleEditMode(id, false);
            },
            error: function (data) {
                showAlert(data.responseText);
                console.log(data);
            }
        });
    }

    // Function to show a Swal alert with a custom message
    function showAlert(message) {
        Swal.fire('Attention', message, 'warning');
    }

    // Restrict input to numbers and a single decimal point
    $(".amount-cell").on("keydown", function (e) {
        let key = e.key;
    
        // Allow numbers, backspace, and one decimal point
        if (
            !(
                (key >= '0' && key <= '9') || // Allow numbers
                key === '.' && !this.value.includes('.') || // Allow one decimal point
                key === 'Backspace' || // Allow backspace
                key === 'Tab' || // Allow tab navigation
                key === 'ArrowLeft' || // Allow left arrow
                key === 'ArrowRight' || // Allow right arrow
                key === 'Delete' || // Allow delete
                key === '-' // Allow minus sign
            )
        ) {
            e.preventDefault();
        }
    });
    
});