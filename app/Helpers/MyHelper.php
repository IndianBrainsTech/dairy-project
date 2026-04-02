<?php
    use Carbon\Carbon;

    function displayDate($date) {
        if(!is_null($date)) {
            $date = date("d-m-Y",strtotime($date));
        }
        return $date;
    }
 
    function displayTime($time) {
        if($time<>"") {
            $time = date_create($time);
            $time = $time->format('h:i A');
        }
        return $time;
    }

    function displayDateTime($date) {
        if(!is_null($date)) {
            $date = date("d-m-Y h:i A",strtotime($date));
        }
        return $date;
    }

    function getIndiaData($dateTime, $format) {
        if(!is_null($dateTime)) {
            $dateTime = new DateTime($dateTime, new DateTimeZone('UTC'));
            $dateTime->setTimezone(new DateTimeZone('Asia/Kolkata'));
            $dateTime = $dateTime->format($format);
        }
        return $dateTime;
    }

    function getIndiaDate($dateTime) {
        return getIndiaData($dateTime, 'd-m-Y');
    }
    
    function getIndiaTime($dateTime) {
        return getIndiaData($dateTime, 'h:i A');
    }

    function getIndiaDateTime($dateTime) {
        return getIndiaData($dateTime, 'd-m-Y h:i A');
    }

    function getInvoiceDateTimeForTally($dateTime) {
        return getIndiaData($dateTime, 'd-M-Y \a\t H:i');
    }
    
    function displayDateTimeIST($datetime): string
    {
        if (empty($datetime))
            return '';

        return Carbon::parse($datetime)
            ->timezone('Asia/Kolkata')
            ->format('d-m-Y h:i A');
    }

    function formatDateToDMY($date)
    {
        // Create a DateTime object from the given date
        $dateObject = DateTime::createFromFormat('Y-m-d', $date);

        // Check if the date is valid
        if ($dateObject) {
            // Format the date as 'd-M-y'
            return $dateObject->format('d-M-y');
        }

        // Return null or handle invalid dates
        return null;
    }

    function dateDifference($date){  // In Years
        if(!empty($date)){
            $date = new DateTime($date);
            $today = new DateTime('today');
            $diff = $date->diff($today);
            // $diff = $diff->y . " years (+" . $diff->m . " months, " . $diff->d . " days)";
            $diff = $diff->y . " years";
            return $diff;
        }
        else{
            return 0;
        }
    }

    function timeDifference($timeStart, $timeEnd) {   // In Minutes
        if(!empty($timeEnd)){
            $time1 = new DateTime($timeStart);
            $time2 = new DateTime($timeEnd);
            $diff = $time2->diff($time1);
            $diff = $diff->h * 60 + $diff->i;
            return $diff;
        }
        else{
            return 0;
        }
    }

    function getPreviousDate($date) {
        $date = new DateTime($date);
        $date->modify("-1 days");
        return $date->format('Y-m-d');
    }
    
    function getNextDate($date) {
        $date = new DateTime($date);
        $date->modify("+1 days");
        return $date->format('Y-m-d');
    }

    function getYesterday() {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        return $yesterday;
    }

    function getTomorrow() {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        return $tomorrow;
    }

    function getHoursFromMinutes($minutes) {
        $hours = intdiv($minutes, 60).' hrs '. ($minutes % 60) . " mins ";
        return $hours;
    }

    function getTwoDigitPrecision($number, $nullValue = "0.00") {
        if ($number === null) {
            return $nullValue;
        }
        else if($number === "") {
            return "";
        }
        return number_format($number, 2, '.', '');
    }   
    
    function getNumberOrEmpty($number) {
        if($number == 0)
            return "";
        else
            return number_format($number, 2, '.', '');
    }

    function getNumberOrHyphen($number) {
        if($number == 0)
            return "-";
        else
            return number_format($number, 2, '.', '');
    }

    function getEmptyForZero($number) {
        return ($number == 0) ? "" : $number;
    }

    function getZeroForEmpty($number) {
        return ($number == "") ? 0 : $number;
    }

    function getRoundOffWithSign($number) {
        if($number > 0)
            return "+" . getTwoDigitPrecision($number);
        else
            return getTwoDigitPrecision($number);
    }

    function ceilToNearestQuarter($number) {
        // Ceil the number to the next 0.25 increment
        return ceil($number * 4) / 4;        
    }

    // Function to format a number with commas every two digits
    function formatWithTwoDigitChunks($number) {
        // Convert number to string
        $numberString = strval($number);

        // Split the number into chunks of two digits from right to left
        $chunks = str_split(strrev($numberString), 2);

        // Join the chunks with commas and reverse the result
        $formattedNumber = strrev(implode(',', $chunks));

        return $formattedNumber;
    }

    // Function to format a number with commas according to Indian Numbering System
    function formatIndianNumber($number) {
        $isNegative = $number < 0; // Check if the number is negative
        $number = abs($number); // Work with the absolute value of the number

        if ($number >= 1000) {
            // Separate the number into thousands and remaining digits
            $thousands = intval($number / 1000);
            $remainder = $number - ($thousands * 1000);

            // Pad the remaining digits with zeros to ensure three digits
            $remainder = str_pad($remainder, 3, '0', STR_PAD_LEFT);

            // Format the thousands part with commas using the helper function
            $formattedThousands = formatWithTwoDigitChunks($thousands);

            // Concatenate thousands part and remaining digits with a comma
            $formattedNumber = $formattedThousands . "," . $remainder;
        } 
        else {
            // Return the number as it is if less than 1000
            $formattedNumber = $number;
        }

        // If the number was negative, prepend the negative sign
        return $isNegative ? '-' . $formattedNumber : $formattedNumber;
    }

    // Function to format a number with Indian commas including decimal parts
    function formatNumberWithCommas($number) {
        // Split the number into integer and decimal parts
        $parts = explode('.', $number);

        // Format the integer part with commas according to Indian Numbering System
        $parts[0] = formatIndianNumber($parts[0]);

        // Join the integer and decimal parts back together
        return implode('.', $parts);
    }

    // Function to format a number with Indian commas including decimal parts
    function formatIndianNumberWithDecimal($number) {
        // Round off decimal part to two digits
        $number = getTwoDigitPrecision($number);

        // Split the number into integer and decimal parts
        $parts = explode('.', $number);

        // Format the integer part with commas according to Indian Numbering System
        $parts[0] = formatIndianNumber($parts[0]);

        // Join the integer and decimal parts back together
        return implode('.', $parts);
    }

    function getCurrentFinancialYear($dateString) {
        $date = new DateTime($dateString);
        
        // Check if the current date is before April 1st
        if ($date->format('n') < 4) {
            $startYear = $date->format('Y') - 1;
        } 
        else {
            $startYear = $date->format('Y');
        }
        
        // Financial year starts from April 1st of start year
        $financialYearStart = $startYear . '-04-01';
        
        // Financial year ends on March 31st of the next year
        $endYear = $startYear + 1;
        $financialYearEnd = $endYear . '-03-31';
        
        return [$financialYearStart, $financialYearEnd];
    }

    function formatPrice($price) {
        // Check if the number is an integer or a float
        if (is_int($price))
            return $price; // No formatting for integers
        else
            return round($price, 2); // Format floats to 2 decimal places
    }

    function formatDateRange($fromDate, $toDate) {
        // Convert the dates to DateTime objects for easier manipulation
        $fromDateTime = new DateTime($fromDate);
        $toDateTime = new DateTime($toDate);
    
        // Check if the dates are the same
        if ($fromDateTime == $toDateTime) {
            return "Date : " . $fromDateTime->format('d-m-Y');
        }    

        // Check if $fromDate is the 1st and $toDate is the last date of the same month
        if ($fromDateTime->format('d') == '01' && 
            $toDateTime->format('d') == $toDateTime->format('t') && 
            $fromDateTime->format('Y-m') == $toDateTime->format('Y-m')) { // Check if both dates are in the same month
            return $fromDateTime->format('F Y'); // Example: 'August 2024'
        }
    
        // If the above conditions don't match, return the date range
        return "Date from " . $fromDateTime->format('d-m-Y') . " to " . $toDateTime->format('d-m-Y');
    }

    function formatDateRangeAsDMY($fromDate, $toDate, $singleDateString = "Date : ") {
        // Convert the dates to DateTime objects for easier manipulation
        $fromDateTime = new DateTime($fromDate);
        $toDateTime = new DateTime($toDate);
    
        // Check if the dates are the same
        if ($fromDateTime == $toDateTime) {            
            return $singleDateString . $fromDateTime->format('d-M-y');
        }
    
        // Check if $fromDate is the 1st and $toDate is the last date of the same month
        if ($fromDateTime->format('d') == '01' && 
            $toDateTime->format('d') == $toDateTime->format('t') && 
            $fromDateTime->format('Y-m') == $toDateTime->format('Y-m')) { // Check if both dates are in the same month
            return $fromDateTime->format('F Y'); // Example: 'August 2024'
        }
    
        // If the above conditions don't match, return the date range
        return $fromDateTime->format('d-M-y') . " to " . $toDateTime->format('d-M-y');
    }

    function getDateRange($fromDate, $toDate) {
        // Convert strings to DateTime objects
        $start = new DateTime($fromDate);
        $end = new DateTime($toDate);
        $end->modify('+1 day'); // Include the end date

        // Create a date range
        $interval = new DateInterval('P1D'); // 1 Day interval
        $period = new DatePeriod($start, $interval, $end);

        return $period;
    }

    function getDatesForLoop($fromDate, $toDate)
    {
        // Create DateTime objects from the date strings
        $start = new \DateTime($fromDate);
        $end = new \DateTime($toDate);

        // Add 1 day to the end date to include the end date in the loop
        $end->modify('+1 day');

        // Define the interval as 1 day
        $interval = new \DateInterval('P1D');

        // Create a date period from start to end with the specified interval
        $datePeriod = new \DatePeriod($start, $interval, $end);

        // Return date period
        return $datePeriod;
    }
      
    /* Order Helpers Start */
    function getOrderStatus($invoiceStatus) {
        if($invoiceStatus == "Not Generated")
            return "Invoice Not Generated";
        else if($invoiceStatus == "Generated")
            return "Invoice Generated";
        else if($invoiceStatus == "Cancelled")
            return "Order Cancelled";
        else
            return null;
    }

    function getOrderStatusWithBadge($invoiceStatus) {
        if($invoiceStatus == "Not Generated")
            return "<span class='badge badge-md badge-soft-primary'>Invoice Not Generated</span>";
        else if($invoiceStatus == "Generated")
            return "<span class='badge badge-md badge-soft-success'>Invoice Generated</span>";
        else if($invoiceStatus == "Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Order Cancelled</span>";
        else
            return null;
    }

    function getBulkMilkOrderStatusWithBadge($invoiceStatus) {
        if($invoiceStatus == "Order Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Order Cancelled</span>";
        else
            return getOrderStatusWithBadge($invoiceStatus);
    }
    /* Order Helpers End */

    function getIncentiveStatusWithBadge($status) {
        if($status == "Pending")
            return "<span class='badge badge-md badge-soft-primary'>Pending</span>";
        else if($status == "Accepted")
            return "<span class='badge badge-md badge-soft-success'>Accepted</span>";
        else if($status == "Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Cancelled</span>";
        else
            return null;
    }

    function getJobWorkStatus($status) {
        if($status == "Not Generated")
            return "DC Not Generated";
        else if($status == "Generated")
            return "DC Generated";
        else if($status == "Cancelled")
            return "Cancelled";
        else
            return null;
    }

    function getJobWorkStatusWithBadge($status) {
        if($status == "Job Work Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Job Work Cancelled</span>";
        else if($status == "Not Generated")
            return "<span class='badge badge-md badge-soft-primary'>DC Not Generated</span>";
        else if($status == "Generated")
            return "<span class='badge badge-md badge-soft-success'>DC Generated</span>";
        else if($status == "Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Job Work Cancelled</span>";
        else
            return null;
    }

    /* Receipt Helper Start */
    function formatAmountColumn($records)
    {
        if (empty($records)) {
            return '';
        }

        $formattedValues = array_map(function ($record) {
            $color = $record['status'] === 'Pending' ? 'orange' : 'inherit';
            return "<span style=\"color: {$color}\">{$record['amount']}</span>";
        }, $records);

        return implode(' + ', $formattedValues);
    }
    /* Receipt Helper End */

    /* Report Helper Start */
    function getArrayValuesWithPrecision($array, $key) {
        return implode("<br/>", array_map('getTwoDigitPrecision', array_column($array, $key)));
    } 

    function getReceiptAmountWithBank($records)
    {
        if (count($records) === 0) {
            return '0.00';
        }

        $data = [];
        foreach($records as $record) {
            $amount = getTwoDigitPrecision($record->amount);
            $data[] = "[{$record->bank->display_name}] " . getTwoDigitPrecision($record->amount);
        }

        return implode('<br/>', $data);
    }

    function isEmptyRecord($data, $banks) {
        $isEmpty = true;
        if($data['inv_amt'] || $data['cash'] || $data['incentive'] || $data['deposit'] || $data['others']) {
            $isEmpty = false;
        } 
        else {
            foreach($banks as $bank) {
                if($data[$bank->display_name]) {
                    $isEmpty = false;
                    break;
                }
            }
        }
        return $isEmpty;
    }
    /* Report Helper End */

    /* Tally Helpers Start */
    function getTallyStatusWithBadge($status) {
        if(empty($status))
            return null;
        if($status == "Synced")
            return "<span class='badge badge-md badge-soft-success'>Synced</span>";
        else
            return "<span class='badge badge-md badge-soft-danger'>{$status}</span>";
    }
    /* Tally Helpers End */    

    function getStatusWithBadge($status) {        
        if($status === "Active" || $status === "ACTIVE")
            return "<span class='badge badge-md badge-soft-success'>Active</span>";
        else if($status === "Inactive" || $status === "INACTIVE")
            return "<span class='badge badge-md badge-soft-danger'>Inactive</span>";
        else if($status === "Pending")
            return "<span class='badge badge-md badge-soft-warning'>Pending</span>";
        else if($status === "Paused")
            return "<span class='badge badge-md badge-soft-warning'>Paused</span>";
        else if($status === "Accepted")
            return "<span class='badge badge-md badge-soft-primary'>Accepted</span>";
        else if($status === "Generated")
            return "<span class='badge badge-md badge-soft-success'>Generated</span>";
        else if($status === "Approved")
            return "<span class='badge badge-md badge-soft-success'>Approved</span>";
        else if($status === "Cancelled")
            return "<span class='badge badge-md badge-soft-danger'>Cancelled</span>";
        else if($status === "Rejected")
            return "<span class='badge badge-md badge-soft-danger'>Rejected</span>";
        else if($status === "Superseded")
            return "<span class='badge badge-md badge-soft-warning'>Superseded</span>";
        else if($status === "Scheduled")
            return "<span class='badge badge-md badge-soft-info'>Scheduled</span>";
        else if($status === "Draft")
            return "<span class='badge badge-md badge-soft-primary'>Draft</span>";
        else
            return $status;
    }
?>    