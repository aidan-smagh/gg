<?php
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
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
        <title>Gwyneth's Gift VMS | Inbox</title>
        <script>
            // Function to toggle select/deselect all checkboxes
            document.addEventListener("DOMContentLoaded", function() {
                const selectAllCheckbox = document.getElementById("select-all");
                if(selectAllCheckbox){
                    selectAllCheckbox.addEventListener("change", function() {
                        const checkboxes = document.querySelectorAll("input[type='checkbox'][name='message_ids[]']");
                        checkboxes.forEach(function(checkbox) {
                            checkbox.checked = selectAllCheckbox.checked;
                        });
                    });
                }
            });
        </script>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1>Inbox</h1>
        <main class="general">
            <h2>Your Notifications</h2>
            <?php 
                require_once('database/dbMessages.php');
                $messages = get_user_messages($userID);
                if (count($messages) > 0): ?>
                
                <form id="delete-form" action="deleteMultipleNotifications.php" method="post">
                    <button type="submit" class="delete_all">Delete Selected</button>
                    <div class="table-wrapper">
                        <table class="general">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all" style="display:inline-block;>
                                        <label for="select-all"> Select All</label>
                                    </th>
                                    <th style="width:1px">From</th>
                                    <th>Title</th>
                                    <th style="width:1px">Received</th>
                                </tr>
                            </thead>
                            <tbody class="standout">
                                <?php 
                                    require_once('database/dbPersons.php');
                                    require_once('include/output.php');
                                    foreach ($messages as $message) {
                                        $messageID = $message['id'];
                                        echo "
                                            <tr class='message' data-message-id='$messageID'>
                                                <td class='checkbox'>
                                                    <input type='checkbox' name='message_ids[]' value='$messageID'>
                                                </td>
                                                <td>{$message['senderID']}</td>
                                                <td><a href='viewNotification.php?id=".$messageID."'>{$message['title']}</a></td>
                                                <td>{$message['time']}</td>
                                            </tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </form>

            <?php else: ?>
                <p class="no-messages standout">You currently have no unread messages.</p>
            <?php endif ?>
            <a class="button cancel" href="index.php">Return to Dashboard</a>
        </main>
    </body>
</html>
