<?php
    //Admin can edit notes about a volunteer    
    //You move to this form by clicking the "Edit Notes About A Volunteer Button" when viewing the volunteer profile
    session_cache_expire(30);
    session_start();
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['_id'])) {
        header('Location: login.php');
        die();
    }


    require_once('domain/Person.php');
    require_once('database/dbPersons.php');
    require_once('include/output.php');
    require_once('include/input-validation.php');
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["modify_access"]) && isset($_POST["id"])) {
        $id = $_POST['id'];
        header("Location: /gwyneth/modifyUserRole.php?id=$id");
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["profile-edit-form"])) {
        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        // make every submitted field SQL-safe except for password
        $ignoreList = array('password');
        $args = sanitize($_POST, $ignoreList);
    }
    if ($_SESSION['access_level'] >= 2 && isset($_POST['id'])) {
        $id = $_POST['id'];
        $editingSelf = $id == $_SESSION['_id'];
        $id = $args['id'];
        // Check to see if user is a lower-level manager here
    } else {
        $id = $_SESSION['_id'];
    }

    $person = retrieve_person($id);
    if (!$person) {
        echo '<main class="signup-form"><p class="error-toast">That user does not exist.</p></main></body></html>';
        die();
    }
    var_dump($person);
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
                <input type="text" id="notes" name="notes" value="<?php echo hsc($person->get_notes()); ?>">
            </fieldset>
        </main>
</body>
</html>