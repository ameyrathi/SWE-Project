<?php
// this will autoload the class that we need in our code
spl_autoload_register(function($class) { // this only loads CLASSES, not functions/normal code
    // we are assuming that it is in the same directory as common.php
    // otherwise we have to do
    // $path = 'path/to/' . $class . ".php"    
    require_once "$class.php" ;
});


// session related stuff

session_start();

function printErrors() {
    if(isset($_SESSION['errors'])){
        echo "<ul id='errors' style='color:red;'>";
        
        foreach ($_SESSION['errors'] as $value) {
            echo "<li>" . $value . "</li>";
        }
        
        echo "</ul>";   
        unset($_SESSION['errors']);
    }    
}

function isMissingOrEmpty($name) {
    if (!isset($_REQUEST[$name])) {
        return "missing $name";
    }

    // client did send the value over
    $value = $_REQUEST[$name];
    if ($value == "") {
        return "blank $name";
    }
}

# check if an int input is an int and non-negative
function isNonNegativeInt($var) {
    if (is_numeric($var) && $var >= 0 && $var == round($var))
        return TRUE;
}

# check if a float input is is numeric and non-negative
function isNonNegativeFloat($var) {
    if (is_numeric($var) && $var >= 0)
        return TRUE;
}

# this is better than empty when use with array, empty($var) returns FALSE even when
# $var has only empty cells
function isEmpty($var) {
    if (isset($var) && is_array($var))
        foreach ($var as $key => $value) {
            if (empty($value)) {
               unset($var[$key]);
            }
        }

    if (empty($var))
        return TRUE;
}

/**
 * checks if given string is a valid time format (8:30, 12:50, 23:40)
 * @
 */
function isValidTime($time) {
    return preg_match("/^([1][0-9]|[1-9]|[2][0-3]):([0-5][0-9])$/", $time);
}

/**
 * checks if given string is a valid date format by converting it to another date format to confirm
 * The Y (4 digits year) returns true for any integer with any number of digits so changing the comparison from == to === fixes the issue.
 * @
 */
function validateDate($date, $format = 'Ymd')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

?>