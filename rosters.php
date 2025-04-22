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
$trainings = get_all_trainings();
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
                <label for="training">Training</label><br>
                <select name="training" id="training">
                    <option value="">Select Training</option>
                    <?php foreach ($trainings as $training): ?>{
                        <option value="<?php echo htmlspecialchars($training['name']); ?>">
                            <?php echo htmlspecialchars($training['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            
            <br></br>
            <input type="submit" name="training" value="training">
            </form>
        </main>
</body>
</html>