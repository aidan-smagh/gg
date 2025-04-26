<?php


include_once('dbinfo.php');
require_once('dbPersons.php');
include_once(dirname(__FILE__) . '/../domain/Event.php');


function insert_checkintime($id)
{
    $con = connect();
    $eventId = $id;
    $date = date('H:i');
    $user = retrieve_person($_SESSION['_id']);
    $personid = $user->get_email();
    $first_name = $user->get_first_name();
    $last_name = $user->get_last_name();
    $checkin_time =  $date;

    //if there's no entry for this id, add it
    $query = "INSERT INTO checkintime (UserId, EventId, first_name, last_name, checkin_time) VALUES (?, ?, ?, ?, ?)";
    $statement = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($statement, 'sisss', $personid, $eventId, $first_name, $last_name, $checkin_time);
    mysqli_stmt_execute($statement);
    mysqli_close($con);
    return $query;
}
