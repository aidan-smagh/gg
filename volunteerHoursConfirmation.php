<?php
// certificate.php
// This page is based on existing VMS pages and uses the same authentication, db, and style sheet.
// It displays a certificate generation form and, when submitted, shows an HTML certificate confirmation.

session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Authenticate the user (same as your VMS pages)
$loggedIn = false;
$accessLevel = 0;
$userID = null;
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}
if (!$loggedIn) {
    header('Location: login.php');
    die();
}
$isAdmin = $accessLevel >= 2;

// Include database functions
require_once('database/dbPersons.php');

if ($isAdmin && isset($_GET['id'])) {
    require_once('include/input-validation.php');
    $args = sanitize($_GET);
    $id = $args['id'];
    $viewingSelf = $id == $userID;
} else {
    $id = $_SESSION['_id'];
    $viewingSelf = true;
}

// Retrieve volunteer information
$volunteer = retrieve_person($id);
$volunteerName = $volunteer ? $volunteer->get_first_name() . ' ' . $volunteer->get_last_name() : 'Volunteer';

// If the certificate form is submitted, generate the certificate confirmation page.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_certificate'])) {
    // Sanitize certificate input
    $startDate = htmlspecialchars($_POST['start_date']);
    $endDate = htmlspecialchars($_POST['end_date']);
    $pronoun = htmlspecialchars($_POST['pronoun']);
    $hours = htmlspecialchars($_POST['hours']);
    
    // Current date for the certificate header
    $currentDate = date('m/d/Y');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php require_once('universal.inc'); ?>
        <meta charset="UTF-8">
        <title>Gwyneth's Gift VMS | Volunteer Hours Confirmation</title>
        <link rel="stylesheet" href="css/hours-report.css">
        <style>
            /* Additional styling for certificate display */
            .certificate { margin: 2rem; padding: 2rem; border: 1px solid #ccc; }
            .certificate .header { text-align: right; }
            .certificate .signature { margin-top: 3rem; }
            .no-print { display: block; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body>
        <?php require_once('header.php'); ?>
        <div class="certificate">
            <div class="header">
                <p>Date: <?php echo $currentDate; ?></p>
            </div>
            <br><br>
            <p>Re: Volunteer Hours Confirmation</p>
            <br>
            <p>To Whom It May Concern,</p>
            <br>
            <p>
                This letter serves as confirmation that <?php echo $volunteerName; ?> has volunteered with Gwyneth’s Gift Foundation from <?php echo $startDate; ?> to <?php echo $endDate; ?>.
                During this period, <?php echo $pronoun; ?> contributed a total of <?php echo $hours; ?> hours.
            </p>
            <br>
            <p>
                We greatly appreciate <?php echo $volunteerName; ?>’s dedication and valuable contributions to our organization.
            </p>
            <br>
            <p>
                If you require any further information, please feel free to contact us.
            </p>
            <br><br>
            <div class="signature">
                <p>Sincerely,</p>
                <p>Tiffany Kay<br>
                Program Manager<br>
                Gwyneth’s Gift Foundation</p>
            </div>
        </div>
        <!-- Button to trigger the browser's print dialog -->
        <button class="no-print" onclick="window.print()">Print Certificate / Save as PDF</button>
        <a class="button cancel no-print" href="viewProfile.php<?php echo ($viewingSelf ? '' : '?id=' . htmlspecialchars($_GET['id'])); ?>">Return to Profile</a>
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
    <title>Gwyneth's Gift VMS | Volunteer Certificate Generator</title>
    <link rel="stylesheet" href="css/hours-report.css">
    <style>
        /* Styling for the certificate form */
        .certificate-form {
            max-width: 500px;
            margin: 2rem auto;
            padding: 1rem;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .certificate-form h2 { text-align: center; }
        .certificate-form label {
            display: block;
            margin-top: 1rem;
        }
        .certificate-form input, 
        .certificate-form select {
            width: 100%;
            padding: 0.5rem;
            box-sizing: border-box;
        }
        .certificate-form button {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php require_once('header.php'); ?>
    <h1>Volunteer Certificate Generator</h1>
    <main class="hours-report">
        <p>Welcome, <?php echo $volunteerName; ?>. Use the form below to generate your volunteer hours confirmation certificate.</p>
        <div class="certificate-form">
            <h2>Generate Certificate</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <p><strong>Volunteer:</strong> <?php echo $volunteerName; ?></p>
                
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
                
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>
                
                <label for="pronoun">Pronoun:</label>
                <select id="pronoun" name="pronoun" required>
                    <option value="he">He</option>
                    <option value="she">She</option>
                    <option value="they">They</option>
                </select>
                
                <label for="hours">Number of Hours:</label>
                <input type="number" id="hours" name="hours" required>
                
                <button type="submit" name="generate_certificate">Generate Certificate</button>
            </form>
        </div>
        <a class="button cancel no-print" href="viewProfile.php<?php echo ($viewingSelf ? '' : '?id=' . htmlspecialchars($_GET['id'])); ?>">Return to Profile</a>
    </main>
</body>
</html>
