<?php


include_once('dbinfo.php');
require_once('dbPersons.php');
include_once(dirname(__FILE__) . '/../domain/Event.php');


function insert_checkintime()
{
    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    $date = date('m/d/Y h:i:s a');
    $user = retrieve_person($_SESSION['_id']);
    $personid = $user->get_id();
    $first_name = $user->get_first_name();
    $last_name = $user->get_last_name();
    $checkin_time =  $date;
    $con = connect();
    //if there's no entry for this id, add it
    $query = "INSERT INTO checkintime (id, first_name, last_name, checkin_time) VALUES(?, ?, ?, ?)";
    $statement = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($statement, 'isss', $personid, $first_name, $last_name, $checkin_time);
    mysqli_stmt_execute($statement);
    mysqli_close($con);
    return $query;
}
