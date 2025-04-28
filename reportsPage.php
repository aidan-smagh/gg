<?php
/**
 * @version April 6, 2023
 * @author Alip Yalikun
 */


session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

require_once('include/input-validation.php');
require_once('database/dbPersons.php');
require_once('database/dbEvents.php');
require_once('include/output.php');

$get = sanitize($_GET);
$indivID = @$get['indivID'];
$role = @$get['role'];
$indivStatus = @$get['status'];
$type = $get['report_type'];
$dateFrom = $get['date_from'];
$dateTo = $get['date_to'];
$lastFrom = strtoupper($get['lname_start']);
$lastTo = strtoupper($get['lname_end']);
$eventName = $get['event_name'];
$eventNameWildcard = null;
if ($eventName != null) {
    $eventNameWildcard = '%' . $eventName . '%';
}
@$stats = $get['statusFilter'];
$today = date('Y-m-d');

if ($dateFrom != NULL && $dateTo == NULL)
    $dateTo = $today;
if ($dateFrom == NULL && $dateTo != NULL)
    $dateFrom = date('Y-m-d', strtotime(' - 1 year'));

if ($lastFrom != NULL && $lastTo == NULL)
    $lastTo = 'Z';
if ($lastFrom == NULL && $lastTo != NULL)
    $lastFrom = 'A';

// Is user authorized to view this page?
if ($accessLevel < 2) {
    header('Location: index.php');
    die();
}
function getBetweenDates($startDate, $endDate)
{
    $rangArray = [];

    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate += (86400)) {
        $date = date('Y-m-d', $currentDate);
        $rangArray[] = $date;
    }

    return $rangArray;
}

// csv exports assisted by https://stackoverflow.com/questions/125113/php-code-to-convert-a-mysql-query-to-csv
if (isset($_GET['download'])) {
    $fp = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // query construction
    // TODO this is used twice, extract to function to reduce tech debt
    $con = connect();
    $query = "SELECT *, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
        FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
        JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
        WHERE eventType = 'volunteer_event' ";
    $paramTypes = "";
    $params = array();

    if ($type == "indiv_vol_hours") {
        $query .= "AND dbPersons.id = ? ";
        $paramTypes .= "s";
        $params[] = $indivID;
    } else if ($stats != "All") {
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

    $query .= "GROUP BY ";
    if ($type == "indiv_vol_hours") {
        $query .= "dbEvents.name ";
    } else {
        $query .= "dbPersons.first_name,dbPersons.last_name ";
    }

    switch ($type) {
        case "general_volunteer_report":
            $query .= "ORDER BY dbPersons.last_name, dbPersons.first_name";
            break;
        case "top_perform":
            $query .= "ORDER BY Dur DESC LIMIT 5";
            break;
        case "total_vol_hours":
        case "indiv_vol_hours":
            $query .= "ORDER BY dbEvents.date DESC, dbPersons.last_name, dbPersons.first_name";
            break;
    }

    if ($type == 'meeting_hours') {
        include_once('database/dbCheckIn.php');
        $result = get_board_meeting_attendance($stats, $dateFrom, $dateTo, $eventNameWildcard);
    } else {
        $stmt = $con->prepare($query);
        if ($paramTypes != "") {
            $stmt->bind_param($paramTypes, ...$params);
        }
        $success = $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }

    // header row creation
    $columnNames = [];
        if ($type != "indiv_vol_hours") {
            $columnNames[] = 'First Name';
            $columnNames[] = 'Last Name';
        }
        if ($type == "general_volunteer_report" || $type == 'meeting_hours') {
            $columnNames[] = 'Phone Number';
            $columnNames[] = 'Email Address';
            $columnNames[] = 'Skills';
        } else if ($type == "total_vol_hours" || $type == "indiv_vol_hours") {
            $columnNames[] = 'Event';
            $columnNames[] = 'Event Location';
            $columnNames[] = 'Event Date';
        } else if ($type == 'event_attendance') {
            $columnNames[] = 'Email';
            $columnNames[] = 'Presences';
            $columnNames[] = 'Absences';
        }
    $columnNames[] = 'Hours';

    $nameRange = null;
    if ($lastFrom != null && $lastTo != null && $type != "indiv_vol_hours") {
        $nameRange = range($lastFrom, $lastTo);
    }

    fputcsv($fp, $columnNames);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($nameRange != null && !in_array($row["last_name"][0], $nameRange)) {
            continue;
        }

        $line = [];

        if ($type != "indiv_vol_hours") {
            $line[] = $row['first_name'];
            $line[] = $row['last_name'];
        }
        if ($type == "general_volunteer_report" || $type == 'meeting_hours') {
            $phone = $row['phone1'];
            $mail = $row['email'];
            $line[] = formatPhoneNumber($row['phone1']);
            $line[] = $row['email'];
            $line[] = $row['specialties'];
        } else if ($type == "total_vol_hours" || $type == "indiv_vol_hours") {
            $line[] = $row['name'];
            $line[] = $row['location'];
            $line[] = $row['date'];
        } else if ($type == 'event_attendance') {
            $line[] = $row['email'];
            $attendance = get_attendance($row['email'], $dateFrom, $dateTo, $eventNameWildcard);
            $line[] = $attendance[0];
            $line[] = $attendance[1];
        }
        $line[] = $row['Dur'];

        fputcsv($fp, array_values($line));
    }

    die;
}

?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once('universal.inc') ?>
    <title>Gwyneth's Gift VMS | Report Result</title>
    <style>
        table {
            margin-top: 1rem;
            margin-left: auto;
            margin-right: auto;
            border-collapse: collapse;
            width: 80%;
        }

        td {
            border: 1px solid #333333;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: var(--main-color);
            color: var(--button-font-color);
            border: 1px solid #333333;
            text-align: left;
            padding: 8px;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f0f0f0;
            /* color:var(--button-font-color); */

        }

        @media print {
            tr:nth-child(even) {
                background-color: white;
            }

            button,
            header {
                display: none;
            }

            :root {
                font-size: 10pt;
            }

            label {
                color: black;
            }

            table {
                width: 100%;
            }

            a {
                color: black;
            }
        }

        .theB {
            width: auto;
            font-size: 15px;
        }

        .center_a {
            margin-top: 0;
            margin-bottom: 3rem;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .8rem;
        }

        .center_b {
            margin-top: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .8rem;
        }

        #back-to-top-btn {
            bottom: 20px;
        }

        .back-to-top:visited {
            color: white;
            /* sets the color of the link when visited */
        }

        .back-to-top {
            color: white;
            /* sets the color of the link when visited */
        }

        .intro {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            padding: 0 0 0 0;
        }

        @media only screen and (min-width: 1024px) {
            .intro {
                width: 80%;
            }

            main.report {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        footer {
            margin-bottom: 2rem;
        }
    </style>

</head>

<body>
    <?php require_once('header.php') ?>
    <h1>Report Result</h1>
    <main class="report">
        <div class="intro">
            <div>
                <label>Report Type:</label>
                <span>
                    <?php echo '&nbsp&nbsp&nbsp';
                    if ($type == "top_perform") {
                        echo "Top Performers";
                    } elseif ($type == "general_volunteer_report") {
                        echo "General Volunteer Report";
                    } elseif ($type == "total_vol_hours") {
                        echo "Total Volunteer Hours";
                    } elseif ($type == "indiv_vol_hours") {
                        echo "Individual Volunteer Hours";
                    } elseif ($type == "meeting_hours") {
                        echo "Board Meeting Attendance";
                    } elseif ($type == "event_attendance") {
                        echo "Volunteer Event Attendance";
                    }
                    ?>
                </span>
            </div>
            <div>

                <?php if ($type == "indiv_vol_hours"): ?>
                    <label>Name: </label>
                    <?php echo '&nbsp&nbsp&nbsp';
                    $con = connect();
                    $query = "SELECT dbPersons.first_name, dbPersons.last_name FROM dbPersons WHERE id= '$indivID' ";
                    $result = mysqli_query($con, $query);
                    $theName = mysqli_fetch_assoc($result);
                    echo $theName['first_name'], " ", $theName['last_name'] ?>
                <?php else: ?>
                    <label>Last Name Range:</label>
                    <span>
                        <?php echo '&nbsp&nbsp&nbsp';
                        if ($lastFrom == NULL && $lastTo == NULL): ?>
                            <?php echo "All last names"; ?>
                        <?php else: ?>
                            <?php echo $lastFrom, " to ", $lastTo; ?>
                        <?php endif ?>
                    <?php endif ?>
                </span>
            </div>


            <div>
                <label>Date Range:</label>
                <span>
                    <?php echo '&nbsp&nbsp&nbsp';
                    // if date from is provided but not date to, assume admin wants all dates from gi    ven date to current
                    if (isset($dateFrom) && !isset($dateTo)) {
                        echo $dateFrom, " to Current";
                        // if date from is not provided but date to is, assume admin wants all dates prio    r to the date given
                    } elseif (!isset($dateFrom) && isset($dateTo)) {
                        echo "Every date through ", $dateTo;
                        // if date from and date to is not provided assume admin wants all dates
                    } elseif ($dateFrom == NULL && $dateTo == NULL) {
                        echo "All dates";
                    } else {
                        echo $dateFrom, " to ", $dateTo;
                    }
                    ?>
                </span>
            </div>

            <div>
                <label>Volunteer Status:</label>
                <span>
                    <?php echo '&nbsp&nbsp&nbsp';
                    if ($type == 'indiv_vol_hours')
                        echo $indivStatus;
                    else
                        echo $stats;
                    ?>
                </span>
            </div>
            <?php if ($type == "indiv_vol_hours"): ?>
                <div>
                    <label>Role:</label>
                    <span>
                        <?php echo '&nbsp&nbsp&nbsp';
                        $con = connect();
                        $query = "SELECT dbPersons.type FROM dbPersons WHERE id= '$indivID' ";
                        $result = mysqli_query($con, $query);
                        $theName = mysqli_fetch_assoc($result);
                        if ($role == 'volunteer')
                            $role = 'Volunteer';
                        elseif ($role == 'boardmember')
                            $role = 'Board Member';
                        elseif ($role == 'admin')
                            $role = 'Admin';
                        elseif ($role == 'superadmin')
                            $role = 'SuperAdmin';
                        echo $role ?>
                </div>
            <?php endif ?>
            <div>
                <label>Event name:</label>
                <span>
                    <?php echo '&nbsp&nbsp&nbsp';
                    if ($eventName != null) {
                        echo $eventName;
                    } else {
                        echo "(any)";
                    }
                    ?>
                </span>
            </div>
    </main>

    <div class="center_a">
        <a href="report.php">
            <button class="theB">New Report</button>
        </a>
        <a href="index.php">
            <button class="theB">Home Page</button>
        </a>
        <?php
        $exportLink = '"reportsPage.php?' . $_SERVER['QUERY_STRING'];
        if (!isset($_GET['download'])) {
            $exportLink .=  '&download';
        }
        $exportLink .= '"';
        ?>
        <a href=<?php echo $exportLink ?>>
            <button class="theB">Export to CSV</button>
        </a>
    </div>
    <div class="table-wrapper">
        <?php /* */

        $columns = 0;

        echo "
            <table>
                <tr>
        ";
        if ($type != "indiv_vol_hours") {
            echo "
                <th>First Name</th>
                <th>Last Name</th>
            ";
            $columns += 2;
        }
        if ($type == "general_volunteer_report" || $type == 'meeting_hours') {
            echo "
                <th>Phone Number</th>
                <th>Email Address</th>
                <th>Skills</th>
            ";
            $columns += 3;
        } else if ($type == "total_vol_hours" || $type == "indiv_vol_hours") {
            echo "
                <th>Event</th>
                <th>Event Location</th>
                <th>Event Date</th>
            ";
            $columns += 3;
        } else if ($type == 'event_attendance') {
            echo "
                <th>Email</th>
                <th>Presences</th>
                <th>Absences</th>
            ";
            $columns += 3;
        }
        echo "
                <th>Hours</th>
            </tr>
            <tbody>
        ";
        $columns += 1;

        // query construction nightmare. trust me, it's way better than what used to be here.
        $con = connect();
        
        $query = "SELECT *, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
            FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
            JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
            WHERE eventType = 'volunteer_event' AND noShow = 0 ";
        $paramTypes = "";
        $params = array();

        if ($type == "indiv_vol_hours") {
            $query .= "AND dbPersons.id = ? ";
            $paramTypes .= "s";
            $params[] = $indivID;
        } else if ($stats != "All") {
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

        $query .= "GROUP BY ";
        if ($type == "indiv_vol_hours") {
            $query .= "dbEvents.name ";
        } else {
            $query .= "dbPersons.first_name,dbPersons.last_name ";
        }

        switch ($type) {
            case "general_volunteer_report":
                $query .= "ORDER BY dbPersons.last_name, dbPersons.first_name";
                break;
            case "top_perform":
                $query .= "ORDER BY Dur DESC LIMIT 5";
                break;
            case "total_vol_hours":
            case "indiv_vol_hours":
                $query .= "ORDER BY dbEvents.date DESC, dbPersons.last_name, dbPersons.first_name";
                break;
        }

        if ($type == 'meeting_hours') {
            include_once('database/dbCheckIn.php');
            $result = get_board_meeting_attendance($stats, $dateFrom, $dateTo, $eventNameWildcard);
        } else {
            $stmt = $con->prepare($query);
            if ($paramTypes != "") {
                $stmt->bind_param($paramTypes, ...$params);
            }
            $success = $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        }    

        try {
            $sum = 0;

            $nameRange = null;
            if ($lastFrom != null && $lastTo != null && $type != "indiv_vol_hours") {
                $nameRange = range($lastFrom, $lastTo);
            }

            include_once('database/dbEvents.php');
            while ($row = mysqli_fetch_assoc($result)) {
                if ($nameRange != null && !in_array($row["last_name"][0], $nameRange)) {
                    continue;
                }

                echo "
                    <tr>
                ";
                if ($type != "indiv_vol_hours") {
                    echo "
                        <td>" . $row['first_name'] . "</td>
                        <td>" . $row['last_name'] . "</td>
                    ";
                }
                if ($type == "general_volunteer_report" || $type == 'meeting_hours') {
                    $phone = $row['phone1'];
                    $mail = $row['email'];
                    echo "
                        <td><a href='tel:$phone'>" . formatPhoneNumber($row['phone1']) . "</a></td>
                        <td><a href='mailto:$mail'>" . $row['email'] . "</a></td>
                        <td>" . $row['specialties'] . "</td>
                    ";
                } else if ($type == "total_vol_hours" || $type == "indiv_vol_hours") {
                    echo "
                        <td>" . $row['name'] . "</td>
                        <td>" . $row['location'] . "</td>
                        <td>" . $row['date'] . "</td>
                    ";
                } else if ($type == 'event_attendance') {
                    $mail = $row['email'];
                    $attendance = get_attendance($mail, $dateFrom, $dateTo, $eventNameWildcard);
                    $presences = $attendance[0];
                    $absences = $attendance[1];
                    echo "
                        <td><a href='mailto:$mail'>" . $row['email'] . "</a></td>
                        <td>" . $presences . "</td>
                        <td>" . $absences . "</td>
                    ";
                }
                echo "
                        <td>" . $row['Dur'] . "</td>
                    </tr>
                ";

                $sum += $row["Dur"];
            }

            echo "
                <tr>
            ";
            for ($i = 2; $i < $columns; $i++) {
                echo "<td style='border: none;' bgcolor='white'></td>";
            }
            echo "
                    <td bgcolor='white'><label>Total Hours:</label></td>
                    <td bgcolor='white'><label>" . $sum . "</label></td>
                </tr>
            ";
        } catch (TypeError $e) {
            // Code to handle the exception or error goes here
            echo "No Results found!";
        }
        ?>

            </tbody>
        </table>
    </div>
    <div class="center_b">
        <a href="report.php">
            <button class="theB">New Report</button>
        </a>
        <a href="index.php">
            <button class="theB">Home Page</button>
        </a>
        <?php
        $exportLink = '"reportsPage.php?' . $_SERVER['QUERY_STRING'];
        if (!isset($_GET['download'])) {
            $exportLink .=  '&download';
        }
        $exportLink .= '"';
        ?>
        <a href=<?php echo $exportLink ?>>
            <button class="theB">Export to CSV</button>
        </a>
    </div>
    </main>
    <footer>
        <div class="center_b">
            <button class="theB" id="back-to-top-btn"><a href="#" class="back-to-top">Back to top</a></button>
        </div>
    </footer>
</body>

</html>