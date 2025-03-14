<?php

session_cache_expire(30);
session_start();
require_once('database/dbExternalDocuments.php');

// Ensure user is logged in. If not, redirect to login page
if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}
// Ensure use has appropriate access level. If not, redirect to index page
// 0 = not logged in, 1 = standard user, 2 = admin and boardmember, 3 super admin
if ($_SESSION['access_level'] < 2) {
    header('Location: index.php');
    die();
}
/* Only superadmin can add, delete, and edit external docs  */
$accessLevel = $_SESSION['access_level'];
$isSuperAdmin = $accessLevel >= 3;

/* Get all external docs stored in the database (function call to dbExternalDocuments.php) */
$documents = get_all_external_documents();

/* Process add document form */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"]) && $_POST["form_type"] == "add_document") {
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
            header("Location: externalDocuments.php?documentAdded=error");
            exit;
        }
        else {
            header("Location: externalDocuments.php?documentAdded=success");
            exit;
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
    <?php require('header.php'); ?>

    <main class="general">
    <h1 style="margin-bottom: 0.1rem;">Organizational Documents</h1>
    <!-- success/error messages -->
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

    <!-- Display all external documents -->

    <?php if (count($documents) > 0): ?>
        <div class="table-wrapper" style="margin-bottom: 5rem;">
            <table class="general">
                <thead>
                    <tr>
                        <th style="background-color: var(--accent-color) !important; color: var(--page-background-color) !important;">Title</th>
                        <th style="background-color: var(--accent-color) !important; color: var(--page-background-color) !important;">URL</th>
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

    <!-- Add document form, for superadmin only -->
     <?php if ($isSuperAdmin): ?>
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

            <label for="title">Document Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="url">Document URL:</label>
            <input type="url" id="url" name="url" required style="width: 100%; margin-bottom: 2rem;">

            <button type="submit" style="margin-bottom: 5rem;"> Add Document</button>
        </form>
    <?php endif; ?>

    <!-- Delete document form. For superadmin only -->
     <?php if ($isSuperAdmin): ?>
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

            <button type="submit">Delete Document</button>
        </form>
    <?php endif;?>
</body>
</html>