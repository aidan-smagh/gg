<?php

session_cache_expire(30);
session_start();
require_once('database/dbExternalDocuments.php');
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

/* Get all external docs stored in the database (function call to dbExternalDocuments.php) */
$documents = get_all_external_documents();

/* Process add/delete document form */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"])) {//&& $_POST["form_type"] == "add_document") {
    /* Processing the add document form */
    if ($_POST["form_type"] == "add_document") {
        // clear leading/tailing whitespace from the title and url
        $title = trim($_POST["title"]);
        $url = trim($_POST["url"]);

        /* add the new document to the database (function call to dbExternalDocuments.php)
        First ensuring the fields were filled out properly */
        if (!empty($title) && !empty($url)) {
            $insertResult = add_document($title, $url);
            if ($insertResult === "duplicateTitle") {
                header("location: externalDocuments.php?documentAdded=duplicate");
                exit;
            }
            else if ($insertResult === "error") {
                // redirect user to external docs page, with error parameter
                header("Location: externalDocuments.php?documentAdded=error");
                exit;
            }
            else {
                header("Location: externalDocuments.php?documentAdded=success");
                exit;
            }
        }
    }
    /* Processing the delete document form */
    else if ($_POST["form_type"] == "delete_document") {
        /* Collect the title to be deleted from the form */
        $titleToDelete = $_POST["title_to_delete"];

        if (!empty($titleToDelete)) {
            /* function call to dbExternalDocuments.php to delete the document */
            $deletionResult = delete_document($titleToDelete);
            if ($deletionResult === "success") {
                header("Location: externalDocuments.php?documentDeleted=success");
                exit;
            }
            else {
                header("Location: externalDocuments.php?documentDeleted=error");
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
    <title>Organizational Documents</title>
</head>
<body>
    <main class="general">
    <?php require('header.php'); ?>
    <h1 style="margin-bottom: 2rem;">Organizational Documents</h1>
        <!-- success/error messages for adding documents -->
        <?php if (isset($_GET['documentAdded'])): ?>
        <?php $insertionStatus = $_GET['documentAdded']; ?>
        <div class="happy-toast">
            <?php if ($insertionStatus === 'success'): ?>
                Document added successfully!
            <?php elseif ($insertionStatus === 'error'): ?>
                Error adding document. Please try again.
            <?php elseif ($insertionStatus === 'duplicate'): ?>
                A document with that title already exists. Please choose a different title.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- success/error messages for deleting documents -->
    <?php if (isset($_GET['documentDeleted'])): ?>
        <?php $deletionStatus = $_GET['documentDeleted']; ?>
        <div class="happy-toast">
            <?php if ($deletionStatus === "success"): ?>
                Document deleted successfully!
            <?php elseif ($deletionStatus === "error"): ?>
                Error deleting document. Please try again.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Display all external documents -->
    <div style="border: 4px solid black; padding: 1rem; margin-bottom: 5rem; border-radius: 3px;"> 
        <?php if (count($documents) > 0): ?>
            <div class="table-wrapper" style="margin-top: 1rem; margin-bottom: 1rem;">
                <table class="general">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>URL</th>
                        </tr>
                    </thead>
                    <tbody class="standout">
                        <?php foreach ($documents as $document): ?>
                            <tr class="message">
                                <td><?php echo htmlspecialchars($document['title']); ?></td>
                                <td><a href="<?php echo htmlspecialchars($document['url']); ?>" target="_blank">
                                    <?php 
                                        /* Need to ensure that the url isn't too long to display.
                                        If it is, display the first 50 characters, followed by "..." */
                                        $fullURL = htmlspecialchars($document['url']);
                                        if (strlen($fullURL) > 50) {
                                            $shortURL = substr($fullURL, 0, 50) . '...';
                                            echo $shortURL;
                                        }
                                        else {
                                            echo $fullURL;
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
            <p class="no-messages standout">No external documents are available.</p>
        <?php endif; ?>
    </div>
    
    <!-- Add document form, for superadmin only -->
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
                Add New Document
            </h2>

            <form method="POST" action="externalDocuments.php">
                <input type="hidden" name="form_type" value="add_document">

                <label for="title">New Document Title:</label>
                <input type="text" id="title" name="title" required>

                <label for="url">New Document URL:</label>
                <input type="url" id="url" name="url" required style="width: 100%; margin-bottom: 2rem;">

                <button type="submit">Submit New Document</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Delete document form. For superadmin only -->
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
                    Delete Existing Document
            </h2>
            <form method="POST" action ="externalDocuments.php">
                <input type="hidden" name="form_type" value="delete_document">

                <label for="title_to_delete">Select Document to Delete:</label>
                <select id="title_to_delete" name="title_to_delete" required>
                    <option value="">Select a document</option>
                    <?php foreach ($documents as $document): ?>
                        <option value="<?php echo htmlspecialchars($document['title']); ?>">
                            <?php echo htmlspecialchars($document['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Submit Document Deletion</button>
            </form>
        </div>
    <?php endif;?>
</body>
</html>