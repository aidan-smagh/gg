<?php


include_once('dbinfo.php');
require_once('dbPersons.php');
include_once(dirname(__FILE__) . '/../domain/Event.php');
//require(dirname(__FILE__) . '/../universal.inc');
//(dirname(__FILE__) . '/../header.php');

function insert_checkintime($id)
{
    $con = connect();
    $eventId = $id;
    $date = date('H:i');
    $user = retrieve_person($_SESSION['_id']);
    $personid = $user->get_email();
    $first_name = $user->get_first_name();
    $last_name = $user->get_last_name();
    $checkin_time = $date;


    // Check to see if user is already checked in
    $alreadyCheckedIn = "SELECT COUNT(*) FROM checkintime WHERE UserId = ? AND EventId = ?";
    $statement = mysqli_prepare($con, $alreadyCheckedIn);
    mysqli_stmt_bind_param($statement, 'si', $personid, $eventId);
    mysqli_stmt_execute($statement);
    mysqli_stmt_bind_result($statement, $count);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);

    if ($count > 0) {
        mysqli_close($con);
        return false;
    }


    //if there's no entry for this id, add it
    $query = "INSERT INTO checkintime (UserId, EventId, first_name, last_name, checkin_time) VALUES (?, ?, ?, ?, ?)";
    $statement = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($statement, 'sisss', $personid, $eventId, $first_name, $last_name, $checkin_time);
    mysqli_stmt_execute($statement);
    mysqli_close($con);
    return mysqli_stmt_affected_rows($statement) > 0;
}

function get_board_meeting_attendance($stats, $dateFrom, $dateTo, $eventNameWildcard) {
    $con = connect();
    $query = "SELECT *, SUM(IF(dbEvents.endTime > checkintime.checkin_time, HOUR(TIMEDIFF(dbEvents.endTime, checkintime.checkin_time)), 0)) as Dur
        FROM dbPersons JOIN checkintime ON dbPersons.id = checkintime.Userid
        JOIN dbEvents ON checkintime.EventId = dbEvents.id
        WHERE eventType = 'board_meeting' ";
    $paramTypes = "";
    $params = array();
    
    if ($stats != "All") {
        $query .= "AND dbPersons.status = ? ";
        $paramTypes .= "s";
        $params[] = $stats;
    }

    if ($dateFrom != NULL && $dateTo != NULL) {
        $query .= "AND date >= ? AND date<= ? ";
        $paramTypes .= "ss";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }

    if ($eventNameWildcard != null) {
        $query .= "AND (name LIKE ? OR abbrevName LIKE ?) ";
        $paramTypes .= "ss";
        $params[] = $eventNameWildcard;
        $params[] = $eventNameWildcard;
    }

    $query .= "GROUP BY dbPersons.first_name,dbPersons.last_name ";
    $query .= "ORDER BY dbPersons.last_name, dbPersons.first_name";

    $stmt = $con->prepare($query);
    if ($paramTypes != "") {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $success = $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    return $result;
}
