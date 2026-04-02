// Restricts input to integer only
function restrictToInteger(e) {
    const key = String.fromCharCode(e.keyCode);
    if (key.match(/[^0-9]/g)) return false;
    return true;
}

// Restricts input to float only
function restrictToFloat(e) {
    const key = String.fromCharCode(e.keyCode);
    
    // Check if the entered character matches the regular expression
    if (key.match(/[^0-9.]/g))
        return false;

    // Ensure only one decimal point
    if (key === '.' && e.target.value.indexOf('.') !== -1)
        return false;
    
    return true;
}

// Restricts input to vehicle number only
function restrictToVehicleNumber(e) {    
    // Get the character from the event and convert it to uppercase
    let key = String.fromCharCode(e.keyCode).toUpperCase();
    
    // Allow only alphabets (A-Z), numbers (0-9), space, and hyphen
    if (key.match(/[^A-Z0-9 \-]/)) {
        return false;
    }

    // If the input is a lowercase letter, convert it to uppercase
    if (e.key !== key) {
        e.preventDefault();
        e.target.value += key;
        return false;
    }

    return true;
}

// Restricts input to today and tomorrow only
function restrictToTodayAndTomorrow(dateControl) {
    // Get today's date
    let today = new Date();
    let tomorrow = new Date();
    
    // Set tomorrow's date
    tomorrow.setDate(today.getDate() + 1);
    
    // Format the dates to 'YYYY-MM-DD' which is the required format for date input fields
    let todayFormatted = today.toISOString().split('T')[0];
    let tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    // Set the min and max attributes to allow only today and tomorrow
    $(dateControl).attr('min', todayFormatted);
    $(dateControl).attr('max', tomorrowFormatted);
}

// Restricts input to yesterday and today only
function restrictToYesterdayAndToday(dateControl) {
    // Get today's date
    let today = new Date();
    let yesterday = new Date();
    
    // Set yesterday's date
    yesterday.setDate(today.getDate() - 1);
    
    // Format the dates to 'YYYY-MM-DD' which is the required format for date input fields
    let todayFormatted = today.toISOString().split('T')[0];
    let yesterdayFormatted = yesterday.toISOString().split('T')[0];
    
    // Set the min and max attributes to allow only yesterday and today
    $(dateControl).attr('min', yesterdayFormatted);
    $(dateControl).attr('max', todayFormatted);
}

// Restricts input to yesterday, today, and tomorrow only
function restrictToYesterdayTodayAndTomorrow(dateControl) {
    // Get today's date
    let today = new Date();
    let yesterday = new Date();
    let tomorrow = new Date();
    
    // Set yesterday's and tomorrow's dates
    yesterday.setDate(today.getDate() - 1);
    tomorrow.setDate(today.getDate() + 1);
    
    // Format the dates to 'YYYY-MM-DD' which is the required format for date input fields    
    let yesterdayFormatted = yesterday.toISOString().split('T')[0];
    let tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    // Set the min and max attributes to allow only yesterday, today, and tomorrow
    $(dateControl).attr('min', yesterdayFormatted);
    $(dateControl).attr('max', tomorrowFormatted);
}


// Restricts max input to tomorrow
function restrictMaxToTomorrow(dateControl) {
    // Get today's date
    let today = new Date();
    let tomorrow = new Date();
    
    // Set tomorrow's date
    tomorrow.setDate(today.getDate() + 1);
    
    // Format the dates to 'YYYY-MM-DD' which is the required format for date input fields    
    let tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    
    // Set the max attribute to allow upto tomorrow    
    $(dateControl).attr('max', tomorrowFormatted);
}

function getFormattedDate(dateString) {
    if(dateString === 'yesterday' || dateString === 'today' || dateString === 'tomorrow') {
        let date = new Date();
        if(dateString === 'yesterday')
            date.setDate(date.getDate() - 1);
        else if(dateString === 'tomorrow')
            date.setDate(date.getDate() + 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;        
    }
    else {
        return dateString; // return it as-is
    }
}

// Restricts Dates from Min to Max
function restrictDates(dateControl, minDate, maxDate) {
    let minFormatted = getFormattedDate(minDate);
    let maxFormatted = getFormattedDate(maxDate);
    
    // Set the min and max attributes
    $(dateControl).attr('min', minFormatted);
    $(dateControl).attr('max', maxFormatted);
}