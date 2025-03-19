<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $event_id = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <link rel="stylesheet" href="css/messages.css"></link>
        <script src="js/messages.js"></script>
        <title>Gwyneth's Gift VMS | Events</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1>Events</h1>
        <main class="general">
            <h2>Upcoming Events</h2>
            <?php 
                require_once('database/dbEvents.php');
                $messages = retrieve_event($event_id);?>

            <form id="delete-form" action="deleteMultipleNotifications.php" method="post">
                <div class="table-wrapper">
                    <table class="general">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                            <tbody>
                                
                            </tbody>
                        </thead>
                    </table>
                </div>
            </form>
        </main>
    </body>
</html>