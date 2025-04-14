<?php

require_once(dirname(__FILE__) . '/../database/dbinfo.php');

/**
 * Trims a given input string, then removes any
 * SQL- or HTML-sensitive characters (', <, etc.)
 * from the string, then returns the resulting string.
 */
function _sanitize($connection, $input) {
    $input = trim($input);
    // Escapes the string for use in an SQL statement
    $input = mysqli_real_escape_string($connection, $input);
    // Converts special characters to HTML entities for safe output
    $input = htmlspecialchars($input);
    return $input;
}

/**
 * Takes an associative array ($_POST or $_GET, for example)
 * and creates a new associative array with the same keys and
 * values, but with the values stripped of any SQL- or HTML-sensitive
 * characters. Also trims the input.
 *
 * Also accepts an optional ignore list, which is an array
 * of keys that should not be sanitized.
 *
 * This function now also handles nested arrays.
 */
if (!function_exists('sanitize')) {
    function sanitize($input, $ignoreList = null) {
        $sanitized = [];
        $connection = connect();
        
        // Process each key/value in the input array
        foreach ($input as $key => $value) {
            if ($ignoreList && in_array($key, $ignoreList)) {
                $sanitized[$key] = $value;
            } else {
                if (is_array($value)) {
                    // Recursively sanitize arrays
                    $sanitized[$key] = sanitize($value, $ignoreList);
                } else {
                    $sanitized[$key] = _sanitize($connection, $value);
                }
            }
        }
        mysqli_close($connection);
        return $sanitized;
    }
}

/**
 * Trims a given input string, then removes any
 * SQL- or HTML-sensitive characters (', <, etc.)
 * from the string, then returns the resulting string.
 */
function sql_safe_input($connection, $input) {
    $input = trim($input);
    $input = mysqli_real_escape_string($connection, $input);
    $input = htmlspecialchars($input);
    return $input;
}

/**
 * Processes an associative array (such as $_POST or $_GET),
 * safely escaping its values. Allows an optional ignore list.
 */
function sql_safe_associative_array($input, $ignoreList = null) {
    $sanitized = [];
    $connection = connect();
    if ($ignoreList) {
        foreach ($input as $key => $value) {
            if (in_array($key, $ignoreList)) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = sql_safe_input($connection, $value);
            }
        }
    } else {
        foreach ($input as $key => $value) {
            $sanitized[$key] = sql_safe_input($connection, $value);
        }
    }
    mysqli_close($connection);
    return $sanitized;
}

/**
 * Validates that a given date string conforms to a particular format.
 * Credit: https://www.codexworld.com/how-to/validate-date-input-string-in-php/
 *
 * @param string $date The date string to validate.
 * @param string $format The expected format (default is 'Y-m-d').
 * @return mixed The original date string if valid, or false if invalid.
 */
function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        return $date;
    }
    return false;
}

function validate24hTimeRange($start, $end) {
    if (!validate24hTime($start) || !validate24hTime($end)) {
        return false;
    }
    if ($start >= $end) {
        return false;
    }
    return true;
}

function validate24hTime($time) {
    $exp = "/([0-1][0-9]|2[0-3]):[0-5][0-9]/";
    if (!preg_match($exp, $time)) {
        return false;
    }
    return true;
}

function validate12hTimeRangeAndConvertTo24h($start, $end) {
    $start = validate12hTimeAndConvertTo24h($start);
    $end = validate12hTimeAndConvertTo24h($end);
    if (!$start || !$end) {
        return false;
    }
    if ($start >= $end) {
        return false;
    }
    return array($start, $end);
}

function validate12hTimeAndConvertTo24h($time) {
    $exp = "/^([1-9]|(1[0-2])):[0-5][0-9] ?[ap]m$/i";
    if (!preg_match($exp, $time)) {
        return false;
    }
    return date("H:i", strtotime($time));
}

function validateAndFilterPhoneNumber($number) {
    $number = preg_replace("/[^0-9]/", "", $number);
    if (strlen($number) != 10) {
        return false;
    }
    return $number;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function wereRequiredFieldsSubmitted($args, $fieldsRequired, $blankOkay = true) {
    foreach ($fieldsRequired as $field) {
        if (!isset($args[$field]) || (!$args[$field] && !$blankOkay)) {
            return false;
        }
    }
    return true;
}

function validateZipcode($zip) {
    $zip = preg_replace("/[^0-9]/", "", $zip);
    if (strlen($zip) != 5) {
        return false;
    }
    return $zip;
}

function valueConstrainedTo($value, $values) {
    return in_array($value, $values);
}

function convertYouTubeURLToEmbedLink($url) {
    if (preg_match('/^https:\/\/(www\.)?youtube\.com\/.*/i', $url)) {
        // Look for the "v=<video id>" argument.
        $pattern = "/[&?]v=([^&]+)/i";
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    } else if (preg_match('/^https:\/\/youtu\.be\/.*/i', $url)){
        $pattern = "/youtu\.be\/([^\/]+)/";
        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    } else {
        return null;
    }
}

function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

/* Function to return an array of all state abbreviations. */
function getStateAbbreviations() {
    return [
        'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA',
        'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
        'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM',
        'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX',
        'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY'
    ];
}

?>
