<?php

session_cache_expire(30);
session_start();
require_once('database/dbPersons.php');
require_once('database/dbTrainings.php');

// collect id if logged in, redirect to home page if not
if (isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}
else {
    header('Location: login.php');
    die();
}

$isBoardMember = false;
$isSuperAdmin = false;
$userType = $person->get_type()[0];
if ($userType == "boardmember") {
    $isBoardMember = true;
}
if ($userType == "superadmin") {
    $isSuperAdmin = true;
}

// only superadmin and board members can access this page
if (!$isBoardMember && !$isSuperAdmin) {
    header('Location: index.php');
    die();
}
// all trainings in the database
$trainings = get_all_trainings();
// array to store roster as it is built
$roster = [];

// process training form submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trainings'])) {
    $chosenTrainings = $_POST['trainings'];
    $userIds = get_persons_with_specific_training($chosenTrainings);
    foreach ($userIds as $userId) {
        $person = retrieve_person($userId);
        if ($person) {
            $roster[] = $person;
        }
    }
    // create csv file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="roster.csv"');
    $output = fopen('php://output', 'w');
    // write title to the file
    $title = 'Personnel who have completed ' . implode(', ', $chosenTrainings);
    fwrite($output, $title . "\n\n");
    // add each person's email to the file
    foreach ($roster as $person) {
        fwrite($output, $person->get_email() . ',' . "\n");
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require('universal.inc'); ?>
    <link rel="stylesheet" href="css/messages.css">
    <title>Gwyneth's Gift VMS | Roster Creation</title>
</head>
<body>
    <?php
        require_once('header.php');
        require_once('include/output.php');
        $isAdmin = $_SESSION['access_level'] >= 2;
    ?>
    <h1>Roster Creation</h1>
        <main class="signup-form">
            <h2>Roster Criteria</h2>
            
            <form method="post">
            <fieldset>
                
            <!-- training selection -->
            <legend>Select Training</legend>
            <?php foreach ($trainings as $training): ?>
                <div>
                    <input type="checkbox" name="trainings[]" id="<?php echo htmlspecialchars($training['name']); ?>"
                        value="<?php echo htmlspecialchars($training['name']); ?>">
                    <label for="<?php echo htmlspecialchars($training['name']); ?>">
                        <?php echo htmlspecialchars($training['name']); ?>
                    </label>
                </div>
            <?php endforeach; ?>

            
            
            </fieldset>

            <br>
            <input type="submit" name="create_roster" value="Create Roster">
            </form>
        </main>
</body>
</html>