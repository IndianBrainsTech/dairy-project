/**
 * Downloads an Excel file from the specified URL using an AJAX GET request.
 * 
 * This function sends an asynchronous request to retrieve a binary file (Excel),
 * handles the `Content-Disposition` header to extract the filename if available,
 * and triggers a client-side download of the file using a temporary anchor element.
 * 
 * Displays an error alert using SweetAlert2 if the download fails.
 * 
 * @param {string} url - The URL endpoint to fetch the Excel file from.
 */
function downloadExcel(url) {
    $.ajax({
        url: url,
        method: 'GET',
        xhrFields: {
            responseType: 'blob' // handle binary response
        },
        success: function (data, status, xhr) {
            console.log('AJAX Status (downloadXml):', status);
            console.log('Response Headers:', xhr.getAllResponseHeaders());
            console.log('HTTP Status Code:', xhr.status);

            let filename = "download.xlsx";
            const disposition = xhr.getResponseHeader('Content-Disposition');

            if (disposition && disposition.indexOf('filename=') !== -1) {
                filename = disposition.split('filename=')[1].split(';')[0].replace(/['"]/g, '');
            }

            const blob = new Blob([data]);
            const downloadUrl = window.URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();

            window.URL.revokeObjectURL(downloadUrl);
        },
        error: function (xhr, status, error) {            
            console.error('AJAX Error (downloadExcel):', {
                status: status,
                error: error,                
                response: xhr.responseText,
            });
            Swal.fire('Sorry!','Download failed!','error');            
        }
    });
}

/**
 * Validates the selected file before form submission.
 * 
 * Checks if a file is selected and whether it has a valid image extension.
 * If validation fails, shows an appropriate SweetAlert message and prevents form submission.
 * 
 * @param {string} fileName - The name of the selected file (from the input element).
 * @param {Event} event - The event object from the form submission or button click.
 */
function validateImageFileBeforeSubmit(fileName, event) {
    if (fileName) {
        if (!hasValidImageExtension(fileName)) {
            Swal.fire('Invalid File', 'Uploaded file is not a supported image type.', 'error');
            event.preventDefault();
        }
    } 
    else {
        Swal.fire('Missing File', 'Please select an image to upload.', 'warning');
        event.preventDefault();
    }
}

/**
 * Checks if the given file name has a valid image extension.
 * 
 * Valid extensions are: jpg, jpeg, png, gif.
 * 
 * @param {string} fileName - The name of the file to validate.
 * @returns {boolean} True if the file has a valid image extension; otherwise, false.
 */
function hasValidImageExtension(fileName) {
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    const extension = fileName.split('.').pop().toLowerCase();
    return allowedExtensions.includes(extension);
}