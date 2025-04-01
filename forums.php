<?php

session_cache_expire(30);
session_start();
require_once('database/dbForums.php');

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

$accessLevel = $_SESSION['access_level'];
$isSuperAdmin = $accessLevel >= 3;

/* Get all posts stored in the database (function call to dbForums.php) */
$posts = get_all_posts();
/* Process add/delete post form */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_type"])) {
    /* Processing the add post form */
    if ($_POST["form_type"] == "add_post") {
        // clear leading/tailing whitespace from the title and url
        $title = trim($_POST["title"]);
        $url = trim($_POST["url"]);

        /* add the new post to the database (function call to dbForums.php)
        First ensuring the fields were filled out properly */
        if (!empty($title) && !empty($url)) {
            $insertResult = add_post($title, $_SESSION['_id'], $url);
            if ($insertResult === "error") {
                // redirect user to forums page, with error parameter
                header("Location: forums.php?postAdded=error");
                exit;
            }
            else {
                header("Location: forums.php?postAdded=success");
                exit;
            }
        }
    }
    /* Processing the delete post form */
    else if ($_POST["form_type"] == "delete_post") {
        /* Collect the id to be deleted from the form */
        $idToDelete = $_POST["id_to_delete"];

        if (!empty($idToDelete)) {
            /* function call to dbForums.php to delete the post */
            $deletionResult = delete_post($idToDelete);
            if ($deletionResult === "success") {
                header("Location: forums.php?postDeleted=success");
                exit;
            }
            else {
                header("Location: forums.php?postDeleted=error");
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
    <title>Board Member Forums</title>
</head>
<body>
    <main class="general">
    <?php require('header.php'); ?>
    <h1 style="margin-bottom: 2rem;">Board Member Forums</h1>
        <!-- success/error messages for adding posts -->
        <?php if (isset($_GET['postAdded'])): ?>
        <?php $insertionStatus = $_GET['postAdded']; ?>
        <div class="happy-toast">
            <?php if ($insertionStatus === 'success'): ?>
                Post created successfully!
            <?php elseif ($insertionStatus === 'error'): ?>
                Error creating post. Please try again.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- success/error messages for deleting posts -->
    <?php if (isset($_GET['postDeleted'])): ?>
        <?php $deletionStatus = $_GET['postDeleted']; ?>
        <div class="happy-toast">
            <?php if ($deletionStatus === "success"): ?>
                Post deleted successfully!
            <?php elseif ($deletionStatus === "error"): ?>
                Error deleting post. Please try again.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Display all posts -->
    <div style="border: 4px solid black; padding: 1rem; margin-bottom: 5rem; border-radius: 3px;"> 
        <?php if (count($posts) > 0): ?>
            <div class="table-wrapper" style="margin-top: 1rem; margin-bottom: 1rem;">
                <table class="general">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Author</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody class="standout">
                        <?php require_once('database/dbPersons.php'); ?>
                        <?php foreach ($posts as $post): ?>
                            <tr class="message">
                                <td><a href="<?php echo htmlspecialchars($post['url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a></td>
                                <td>
                                    <?php 
                                        $person = retrieve_person($post['poster']);
                                        if ($person) {
                                            echo htmlspecialchars($person->get_first_name() . ' ' . $person->get_last_name());
                                        } else {
                                            echo 'Unknown';
                                        }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($post['timePosted']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-messages standout">No forum posts are available.</p>
        <?php endif; ?>
    </div>
    
    <!-- Add post form -->
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
            Create Post
        </h2>

        <form method="POST" action="forums.php">
            <input type="hidden" name="form_type" value="add_post">

            <label for="title">Post Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="url">Post URL:</label>
            <input type="url" id="url" name="url" required style="width: 100%; margin-bottom: 2rem;">

            <button type="submit">Submit Post</button>
        </form>
    </div>

    <!-- Delete post form. Non-superadmins can only delete their own posts. -->
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
                Delete Post
        </h2>
        <form method="POST" action ="forums.php">
            <input type="hidden" name="form_type" value="delete_post">

            <label for="id_to_delete">Select Post to Delete:</label>
            <select id="id_to_delete" name="id_to_delete" required>
                <option value="">Select a post</option>
                <?php
                    if ($isSuperAdmin) {
                        $deleteablePosts = $posts;
                    } else {
                        $deleteablePosts = get_all_posts_by($_SESSION['_id']);
                    }
                ?>
                <?php foreach ($deleteablePosts as $post): ?>
                    <option value="<?php echo htmlspecialchars($post['id']); ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Delete Post</button>
        </form>
    </div>
</body>
</html>