<?php

session_cache_expire(30);
session_start();
require_once('database/dbTrainings.php');
require_once('database/dbPersons.php');

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

/* Get all trainings in the database (function call to dbTrainings.php) */
$trainings = get_all_trainings_with_description();

/* Process add/delete training */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"])) {//&& $_POST["form_type"] == "add_training") {
    /* Processing the add training */
    if ($_POST["form_type"] == "add_training") {
        // clear leading/tailing whitespace from the name and description
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);

        /* add the new training to the database (function call to dbTrainings.php)
        First ensuring the fields were filled out properly */
        if (!empty($name) && !empty($description)) {
            $insertResult = add_training_to_db($name, $description);
            if ($insertResult === "duplicateName") {
                header("location: addNewTraining.php?trainingAdded=duplicate");
                exit;
            }
            else if ($insertResult === "error") {
                // redirect user to training page, with error parameter
                header("Location: addNewTraining.php?trainingAdded=error");
                exit;
            }
            else {
                header("Location: addNewTraining.php?trainingAdded=success");
                exit;
            }
        }
    }
    /* Processing the delete training form */
    else if ($_POST["form_type"] == "delete_training") {
        /* Collect the name to be deleted from the form */
        $nameToDelete = $_POST["name_to_delete"];

        if (!empty($nameToDelete)) {
            /* function call to dbtrainings.php to delete the training */
            $deletionResult = delete_training($nameToDelete);
            if ($deletionResult === "success") {
                header("Location: addNewTraining.php?trainingDeleted=success");
                exit;
            }
            else {
                header("Location: addNewTraining.php?trainingDeleted=error");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require('universal.inc'); ?>
    <link rel="stylesheet" href="css/messages.css">
    <title>Trainings</title>
</head>
<body>
    <main class="general">
    <?php require('header.php'); ?>
    <h1 style="margin-bottom: 2rem;">Trainings</h1>
        <!-- success/error messages for adding trainings -->
        <?php if (isset($_GET['trainingAdded'])): ?>
        <?php $insertionStatus = $_GET['trainingAdded']; ?>
        <div class="happy-toast">
            <?php if ($insertionStatus === 'success'): ?>
                Training added successfully!
            <?php elseif ($insertionStatus === 'error'): ?>
                Error adding training. Please try again.
            <?php elseif ($insertionStatus === 'duplicate'): ?>
                A training with that name already exists. Please choose a different name.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- success/error messages for deleting trainings -->
    <?php if (isset($_GET['trainingDeleted'])): ?>
        <?php $deletionStatus = $_GET['trainingDeleted']; ?>
        <div class="happy-toast">
            <?php if ($deletionStatus === "success"): ?>
                Training deleted successfully!
            <?php elseif ($deletionStatus === "error"): ?>
                Error deleting training. Please try again.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Display all trainings -->
    <div style="border: 4px solid black; padding: 1rem; margin-bottom: 5rem; border-radius: 3px;"> 
        <?php if (count($trainings) > 0): ?>
            <div class="table-wrapper" style="margin-top: 1rem; margin-bottom: 1rem;">
                <table class="general">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody class="standout">
                        <?php foreach ($trainings as $training): ?>
                            <tr class="message">
                                <td><?php echo htmlspecialchars($training['name']); ?></td>
                                
                                <td><<?php echo htmlspecialchars($training['description']); ?></td>
                                    <?php 
                                        /* Need to ensure that the description isn't too long to display.
                                        If it is, display the first 50 characters, followed by "..." */
                                        $fulldescription = htmlspecialchars($training['description']);
                                        if (strlen($fulldescription) > 50) {
                                            $shortdescription = substr($fulldescription, 0, 50) . '...';
                                            echo $shortdescription;
                                        }
                                        else {
                                            echo $fulldescription;
                                        }
                                    ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-messages standout">No trainings are available.</p>
        <?php endif; ?>
    </div>
    
    <!-- Add training form, for superadmin only -->
    <?php if ($isSuperAdmin): ?>
        <div style="border: 4px solid black; padding: 1rem; margin-bottom: 5rem; border-radius: 3px;"> 
            <h2 style="
                font-size: 1.5rem;
                font-weight: 500;
                margin-bottom: 2rem;
                background-color: var(--main-color);
                color: var(--page-background-color);
                width: 100%;
                text-align: center;
                padding: 1rem;">
                Add New Training
            </h2>

            <form method="POST" action="addNewTraining.php">
                <input type="hidden" name="form_type" value="add_training">

                <label for="name">New Training Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="description">New Training Description :</label>
                <input type="description" id="description" name="description" required style="width: 100%; margin-bottom: 2rem;">

                <button type="submit">Submit New Training</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Delete training form. For superadmin only -->
    <?php if ($isSuperAdmin): ?>
        <div style="border: 4px solid black; padding: 1rem; margin-bottom: 5rem; border-radius: 3px;"> 
            <h2 style="
                    font-size: 1.5rem;
                    font-weight: 500;
                    margin-bottom: 2rem;
                    background-color: var(--main-color);
                    color: var(--page-background-color);
                    width: 100%;
                    text-align: center;
                    padding: 1rem;">
                    Delete Existing Training
            </h2>
            <form method="POST" action ="addNewTraining.php">
                <input type="hidden" name="form_type" value="delete_training">

                <label for="name_to_delete">Select Training to Delete:</label>
                <select id="name_to_delete" name="name_to_delete" required>
                    <option value="">Select a training</option>
                    <?php foreach ($trainings as $training): ?>
                        <option value="<?php echo htmlspecialchars($training['name']); ?>">
                            <?php echo htmlspecialchars($training['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Submit Training Deletion</button>
            </form>
        </div>
    <?php endif;?>
</body>
</html>