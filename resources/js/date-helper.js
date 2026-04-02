function getFormattedDate(dateString) {
    if(dateString === 'yesterday' || dateString === 'today' || dateString === 'tomorrow') {
        let date = new Date();
        if(dateString === 'yesterday')
            date.setDate(date.getDate() - 1);
        else if(dateString === 'tomorrow')
            date.setDate(date.getDate() + 1);
        
        const year  = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day   = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;        
    }
    else {
        return dateString; // return it as-is
    }
}

function getYesterday() {
    return getFormattedDate('yesterday');
}

function getToday() {
    return getFormattedDate('today');
}

// Restricts Dates from Min to Max
function restrictDates(dateControl, minDate, maxDate) {
    let minFormatted = getFormattedDate(minDate);
    let maxFormatted = getFormattedDate(maxDate);
    
    // Set the min and max attributes
    $(dateControl).attr('min', minFormatted);
    $(dateControl).attr('max', maxFormatted);
}