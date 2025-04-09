<?php
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
/*$event_id = null;*/
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $userID = $_SESSION['_id'];
}

include 'database/dbEvents.php';
$events = getUpcomingEvents();
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc') ?>
    <link rel="stylesheet" href="css/messages.css">
    <script src="js/messages.js"></script>
    <title>Gwyneth's Gift VMS | Upcoming Events</title>
    <style>
        a.event-link {
            text-decoration: none;
            color: inherit;
        }

        a.event-link .message-body {
            transition: background-color 0.3s, transform 0.2s;
        }

        a.event-link:hover .message-body {
            background-color: #f9f4ec;
            cursor: pointer;
            transform: scale(1.01);
        }
    </style>
</head>
<body>
    <?php require_once('header.php') ?>
    <h1>Upcoming Events</h1>

    <main class="message">
        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <?php
                    $formattedDate = date("l, F j", strtotime($event['date']));
                    $eventUrl = 'event.php?id=' . urlencode($event['id']);
                ?>
                <a href="<?php echo $eventUrl; ?>" class="event-link">
                    <div class="message-body">
                        <div class="sender-time-line" style="flex-direction: column;">
                            <strong><?php echo htmlspecialchars($event['name']); ?></strong>
                            <span><?php echo $formattedDate; ?></span>
                        </div>
                        <div><?php echo($event['startTime']);?> - <?php echo($event['endTime']);?></div>
                        <div><em>Location: <?php echo htmlspecialchars($event['location']); ?></em></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-messages">No upcoming events found.</p>
        <?php endif; ?>
    </main>
</body>
</html>