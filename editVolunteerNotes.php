<?php
    //Admin can edit notes about a volunteer    
    //You move to this form by clicking the "Edit Notes About A Volunteer Button" when viewing the volunteer profile
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    $isAdmin = false;

    ini_set("display_errors", 1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['_id'])) {
        header('Location: login.php');
        die();
    }

    require_once('domain/Person.php');
    require_once('include/output.php');

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    header('Location: login.php');
    die();
}
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $isAdmin = $accessLevel >= 2;
    $userID = $_SESSION['_id'];
} else {
    header('Location: login.php');
    die();
}
if ($isAdmin && isset($_GET['id'])) {
    require_once('include/input-validation.php');
    $args = sanitize($_GET);
    $id = strtolower($args['id']);
} else {
    $id = $userID;
}
require_once('database/dbPersons.php');
if (isset($_GET['removePic'])) {
    if ($_GET['removePic'] === 'true') {
        remove_profile_picture($id);
    }
}
    $person = retrieve_person($id);
    if (!$person) {
        echo '<main class="signup-form"><p class="error-toast">That user does not exist.</p></main></body></html>';
        die();
    }
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirm"])) {
    $args = sanitize($_POST);
    $newNotes = $args['notes'];

    if (update_notes($id, $newNotes)) {
        header("Location: viewProfile.php?id=$id&editSuccess=True");
        die();
    } else {
        echo '<p class="error-toast">Error updating notes. Please try again.</p>';
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Gwyneth's Gift VMS | Edit Notes</title>
</head>
<body>
    <?php
        require_once('header.php');
        require_once('include/output.php');
        $isAdmin = $_SESSION['access_level'] >= 2;
    ?>
    <h1>Volunteer Notes</h1>
        <main class="signup-form">
            <h2>Edit Volunteer Notes</h2>
            
            <fieldset>
                <legend>Notes</legend>
                <!--<label for="notes">Notes</label> -->
            </fieldset>
            <form method="post">
                <fieldset>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="text" id="notes" name="notes" value="<?php echo hsc($person->get_notes()); ?>">
                </fieldset>
                <br></br>
                <input type="submit" name="confirm" value="Confirm Changes">
            </form>
            <!--<a class="button" href="viewProfile.php">Confirm Changes</a> -->
        </main>
</body>
</html>