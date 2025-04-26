<?php
// confirmhours.php

session_cache_expire(30);
session_start();

// Ensure user is logged in
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    header('Location: login.php');
    die();
}

// Include necessary files
require_once('include/input-validation.php');
require_once('./database/dbCheckIn.php');
require_once('database/dbEvents.php');
require_once('database/dbPersons.php');
require_once('include/time.php');


// Sanitize GET and POST data
$args = sanitize($_GET);
$post_args = sanitize($_POST);

// We require event_id and the volunteers array from POST
if (!isset($post_args['event_id']) || !isset($post_args['volunteers'])) {
    echo "Required data missing.";
    die();
}

$event_id = $post_args['event_id'];
$volunteerIDs = $post_args['volunteers']; // Array of volunteer IDs

// Retrieve event details using your existing function
$event_info = fetch_event_by_id($event_id);
if (!$event_info) {
    echo "Invalid event.";
    die();
}

// Calculate event duration using helper functions
$event_duration = calculateHourDuration($event_info['startTime'], $event_info['endTime']);
$event_duration = floatPrecision($event_duration, 2);
$event_persons = getvolunteers_byevent($id);

// Loop through each volunteer and update their hours
foreach ($event_persons as $volunteer_id) {
    if (in_array($volunteer_id, $volunteerIDs)) {
        // Retrieve the volunteer’s record
        $person = retrieve_person($volunteer_id);
        if (!$person) {
            continue;
        }
        // Append the new hours entry using update_hours()
        // Ensure $event_info['date'] is in YYYY-MM-DD format.
        $update_result = update_hours($volunteer_id, $event_duration, $event_info['date']);
        $attendance_result = mark_present($event_id, $volunteer_id);
        if (!($update_result && $attendance_result)) {
            // You can log this or notify; here we simply echo an error.
            echo "Failed to update hours for volunteer ID " . htmlspecialchars($volunteer_id);
        }
    } else {
        $person = retrieve_person($volunteer_id);
        if (!$person) {
            continue;
        }
        $attendance_result = mark_absent($event_id, $volunteer_id);
        if (!$attendance_result) {
            // You can log this or notify; here we simply echo an error.
            echo "Failed to update hours for volunteer ID " . htmlspecialchars($volunteer_id);
        }
    }
}

// After processing, redirect back to the event view page with confirmation parameter
header('Location: event.php?id=' . $event_id . '&hoursUpdated=1');
die();
?>
