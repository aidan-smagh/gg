<?php
// volunteerHoursConfirmation.php

session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
if (!$loggedIn) {
    header('Location: login.php');
    die();
}
$isAdmin = $accessLevel >= 2;

require_once('database/dbPersons.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lookup_certificate'])) {
    $firstName = trim(htmlspecialchars($_POST['first_name']));
    $lastName = trim(htmlspecialchars($_POST['last_name']));
    $birthday = trim(htmlspecialchars($_POST['birthday']));
    $endDate = trim(htmlspecialchars($_POST['end_date']));

    $con = connect();
    $query = "SELECT * FROM dbPersons 
              WHERE first_name = '" . mysqli_real_escape_string($con, $firstName) . "'
              AND last_name = '" . mysqli_real_escape_string($con, $lastName) . "'
              AND birthday = '" . mysqli_real_escape_string($con, $birthday) . "'";
    $result = mysqli_query($con, $query);

    if (!$result || mysqli_num_rows($result) != 1) {
        mysqli_close($con);
        echo "<p style='color:red;'>Volunteer not found. Please check your first name, last name, and birthday.</p>";
        echo "<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>Reload Page</a>";
        exit();
    }

    $result_row = mysqli_fetch_assoc($result);
    $volunteer = make_a_person($result_row);

    //Pull start date and total hours directly from dbPersons fields
    $volunteerName = $volunteer->get_first_name() . ' ' . $volunteer->get_last_name();
    $startDate = $volunteer->get_start_date();
    $volunteerID = $volunteer->get_id();
    $totalHours = get_hours_volunteered_by($volunteerID);
    $currentDate = date('m/d/Y');

    mysqli_close($con);

    // Pronoun
    $gender = strtolower($volunteer->get_gender());
    if ($gender === "female") {
        $pronoun = "She";
    } elseif ($gender === "male") {
        $pronoun = "He";
    } else {
        $pronoun = "They";
    }
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <meta charset="UTF-8">
    <title>Gwyneth's Gift VMS | Volunteer Hours Confirmation</title>
    <link rel="stylesheet" href="css/hours-report.css">
    <style>
        .certificate { margin: 2rem; padding: 2rem; border: 1px solid #ccc; font-family: Arial, sans-serif; line-height: 1.5; }
        .certificate .header { text-align: right; }
        .certificate .header img { max-width: 200px; margin-bottom: 2rem; }
        .certificate .signature img { max-width: 150px; display: block; margin-bottom: 0.5rem; }
        .no-print { display: block; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <?php require_once('header.php'); ?>
    <div class="certificate">
        <div class="header">
            <img src="images/logo.png" alt="Gwyneth’s Gift Logo">
            <p>Date: <?php echo $currentDate; ?></p>
        </div>
        <br><br>
        <p><strong>Re: Volunteer Hours Confirmation</strong></p>
        <br>
        <p>To Whom It May Concern,</p>
        <br>
        <p>
            This letter serves as confirmation that <?php echo $volunteerName; ?> has volunteered with Gwyneth’s Gift Foundation from <?php echo $startDate; ?> to <?php echo $endDate; ?>.
            During this period, <?php echo $pronoun; ?> contributed a total of <?php echo $totalHours; ?> hours.
        </p>
        <br>
        <p>We greatly appreciate <?php echo $volunteerName; ?>’s dedication and valuable contributions to our organization.</p>
        <br>
        <p>If you require any further information, please feel free to contact us.</p>
        <br><br>
        <div class="signature">
            <img src="images/signature.png" alt="Tiffany Kay Signature">
            <p>Sincerely,</p>
            <p>Tiffany Kay<br>Program Manager<br>Gwyneth’s Gift Foundation</p>
        </div>
    </div>
    <button class="no-print" onclick="window.print()">Print Certificate / Save as PDF</button>
    <a class="button cancel no-print" href="viewProfile.php">Return to Profile</a>
</body>
</html>
<?php
exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <meta charset="UTF-8">
    <title>Gwyneth's Gift VMS | Volunteer Certificate</title>
    <link rel="stylesheet" href="css/hours-report.css">
    <style>
        .lookup-form { display: flex; flex-direction: column; gap: 0.5rem; padding: 1rem; margin: 2rem auto; max-width: 35rem; border: 1px solid #ccc; background-color: #f9f9f9; font-family: Arial, sans-serif; }
        .lookup-form label { margin-top: 1rem; }
        .lookup-form input { padding: 0.5rem; width: 100%; box-sizing: border-box; }
        .lookup-form button { margin-top: 1rem; padding: 0.5rem 1rem; width: 100%; }
        main.report { display: flex; flex-direction: column; align-items: center; }
    </style>
</head>
<body>
    <?php require_once('header.php'); ?>
    <h1>Volunteer Certificate</h1>
    <main class="report">
        <p>Please enter your details to generate your volunteer certificate.</p>
        <div class="lookup-form">
            <h2>Generate Certificate</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="birthday">Birthday:</label>
                <input type="date" id="birthday" name="birthday" required>

                <label for="end_date">Certificate End Date:</label>
                <input type="date" id="end_date" name="end_date" required>

                <button type="submit" name="lookup_certificate">Generate Certificate</button>
            </form>
        </div>
        <a class="button cancel no-print" href="viewProfile.php">Return to Profile</a>
    </main>
</body>
</html>
