<?php
//if (isset($_SESSION['_id'])) {

if (isset($_POST['submitTime'])) {
    $current_time = date('h:i:sa'); // Format: "HH:MM" (24-hour format)
    // Insert foreign key of whoever is logged in
    // Event ID as foreign key
    // Checkin time 
    // Checkout time
    $sql = "INSERT INTO checkintime (id, first_name, last_name, checkin_time) VALUES ('1', 'Joseph', 'Tsibu-Gyan','$current_time')";
}
//}
var_dump($current_time);
