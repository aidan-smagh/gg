<?php

session_cache_expire(30);
session_start();

// Ensure user is logged in
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    header('Location: login.php');
    die();
}
require_once('include/input-validation.php');
require_once('./database/dbCheckIn.php');
$args = sanitize($_GET);
if (isset($args["id"])) {
    $id = $args["id"];
} else {
    header('Location: calendar.php');
    die();
}

include_once('database/dbEvents.php');

// We need to check for a bad ID here before we query the db
// otherwise we may be vulnerable to SQL injection(!)
$event_info = fetch_event_by_id($id);
if ($event_info == NULL || ($event_info['eventType'] == 'board_meeting' && $_SESSION['access_level'] < 2)) {
    // TODO: Need to create error page for no event found
    // header('Location: calendar.php');

    // Lauren: changing this to a more specific error message for testing
    //echo 'bad event ID';
    header('Location: index.php?eventError=1');
    die();
}

include_once('database/dbPersons.php');
include_once('database/dbCheckIn.php');
$access_level = $_SESSION['access_level'];
$user = retrieve_person($_SESSION['_id']);
$active = $user->get_status() == 'Active';


ini_set("display_errors", 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $args = sanitize($_POST);
    $get = sanitize($_GET);
    if (isset($_POST['attach-post-media-submit'])) {
        if ($access_level < 2) {
            echo 'forbidden';
            die();
        }
        $required = [
            'url',
            'description',
            'format',
            'id'
        ];
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo "dude, args missing";
            die();
        }
        $type = 'post';
        $format = $args['format'];
        $url = $args['url'];
        if ($format == 'video') {
            $url = convertYouTubeURLToEmbedLink($url);
            if (!$url) {
                echo "bad video link";
                die();
            }
        } else if (!validateURL($url)) {
            echo "bad url";
            die();
        }
        $eid = $args['id'];
        $description = $args['description'];
        if (!valueConstrainedTo($format, ['link', 'video', 'picture'])) {
            echo "dude, bad format";
            die();
        }
        attach_post_event_media($eid, $url, $format, $description);
        header('Location: event.php?id=' . $id . '&attachSuccess');
        die();
    }
    if (isset($_POST['attach-training-media-submit'])) {
        if ($access_level < 2) {
            echo 'forbidden';
            die();
        }
        $required = [
            'url',
            'description',
            'format',
            'id'
        ];
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo "dude, args missing";
            die();
        }
        $type = 'post';
        $format = $args['format'];
        $url = $args['url'];
        if ($format == 'video') {
            $url = convertYouTubeURLToEmbedLink($url);
            if (!$url) {
                echo "bad video link";
                die();
            }
        } else if (!validateURL($url)) {
            echo "bad url";
            die();
        }
        $eid = $args['id'];
        $description = $args['description'];
        if (!valueConstrainedTo($format, ['link', 'video', 'picture'])) {
            echo "dude, bad format";
            die();
        }
        attach_event_training_media($eid, $url, $format, $description);
        header('Location: event.php?id=' . $id . '&attachSuccess');
        die();
    }
    if (isset($_POST['submitTime'])) {
        $hi = insert_checkintime($id);
        $status = $hi ? 'success' : 'fail';
        header('Location: event.php?id=' . $args['id'] . '&checkIn=' . $status);
        die();
    }
} else {
    if (isset($args["request_type"])) {
        //if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $request_type = $args['request_type'];
        if (!valueConstrainedTo(
            $request_type,
            array('add self', 'add another', 'remove')
        )) {
            echo "Bad request";
            die();
        }
        $eventID = $args["id"];


        // Check if Get request from user is from an organization member
        // (volunteer, admin/super admin)
        if ($request_type == 'add self' && $access_level >= 1) {
            if (!$active) {
                echo 'forbidden';
                die();
            }
            $volunteerID = $_SESSION['_id'];
            $person = retrieve_person($volunteerID);
            $name = $person->get_first_name() . ' ' . $person->get_last_name();
            $name = htmlspecialchars_decode($name);
            update_event_volunteer_list($eventID, $volunteerID);
            require_once('database/dbMessages.php');
            require_once('include/output.php');
            $event = fetch_event_by_id($eventID);

            $eventName = htmlspecialchars_decode($event['name']);
            $eventDate = date('l, F j, Y', strtotime($event['date']));
            $eventStart = time24hto12h($event['startTime']);
            $eventEnd = time24hto12h($event['endTime']);
            $eventType = $event['eventType'];
            if ($eventType == 'board_meeting') {
                $notificationSubject = "$name signed up for a board meeting.";
                $notificationText = "Hello,\r\n\r\n$name signed up for the [$eventName](event: $eventID) board meeting from $eventStart to $eventEnd on $eventDate.";
            } else {
                $notificationSubject = "$name signed up for an event!";
                $notificationText = "Exciting news!\r\n\r\n$name signed up for the [$eventName](event: $eventID) event from $eventStart to $eventEnd on $eventDate.";
            }
            system_message_all_admins($notificationSubject, $notificationText);
            // Check if GET request from user is from an admin/super admin
            // (Only admins and super admins can add another user)
        } else if ($request_type == 'add another' && $access_level > 1) {
            $volunteerID = strtolower($args['selected_id']);
            if ($volunteerID == 'vmsroot') {
                echo 'invalid user id';
                die();
            }
            update_event_volunteer_list($eventID, $volunteerID);
            require_once('database/dbMessages.php');
            require_once('include/output.php');
            $event = fetch_event_by_id($eventID);
            $eventName = htmlspecialchars_decode($event['name']);
            $eventDate = date('l, F j, Y', strtotime($event['date']));
            $eventStart = time24hto12h($event['startTime']);
            $eventEnd = time24hto12h($event['endTime']);
            $eventType = $event['eventType'];
            if ($eventType == 'board_meeting') {
                $notificationSubject = "You were assigned to a board meeting.";
                $notificationText = "Hello,\r\n\r\nYou were assigned to the [$eventName](event: $eventID) board meeting from $eventStart to $eventEnd on $eventDate.";
            } else {
                $notificationSubject = 'You were assigned to an event!';
                $notificationText = "Hello,\r\n\r\nYou were assigned to the [$eventName](event: $eventID) event from $eventStart to $eventEnd on $eventDate.";
            }
            send_system_message($volunteerID, $notificationSubject, $notificationText);
        } else if ($request_type == 'remove' && $access_level > 1) {
            $volunteerID = $args['selected_removal_id'];
            remove_volunteer_from_event($eventID, $volunteerID);
        } else {
            header('Location: event.php?id=' . $eventID);
            die();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php
    require_once('universal.inc');
    $eventDescriptor = $event_info['eventType'] == 'board_meeting' ? 'Board Meeting' : 'Event';
    ?>
    <title>Gwyneth's Gift VMS | View <?php echo $eventDescriptor ?>: <?php echo $event_info['name'] ?></title>
    <link rel="stylesheet" href="css/event.css" type="text/css" />
    <?php if ($access_level >= 2) : ?>
        <script src="js/event.js"></script>
    <?php endif ?>
</head>

<body>
    <?php if ($access_level >= 2) : ?>
        <div id="delete-confirmation-wrapper" class="hidden">
            <div id="delete-confirmation">
                <p>Are you sure you want to delete this event?</p>
                <p>This action cannot be undone.</p>

                <form method="post" action="deleteEvent.php">
                    <input type="submit" value="Delete Event">
                    <input type="hidden" name="id" value="<?= $id ?>">
                </form>
                <button id="delete-cancel">Cancel</button>
            </div>
        </div>
    <?php endif ?>
    <?php require_once('header.php') ?>

    <!-- Message display block -->
    <?php if (isset($_GET['hoursUpdated'])): ?>
        <div class="happy-toast">
            Hours updated
        </div>
    <?php endif; ?>

    <h1>View <?php echo $eventDescriptor ?></h1>
    <main class="event-info">
        <?php if (isset($_GET['createSuccess'])): ?>
            <div class="happy-toast">Event created successfully!</div>
        <?php endif ?>
        <?php if (isset($_GET['attachSuccess'])): ?>
            <div class="happy-toast">Media attached successfully!</div>
        <?php endif ?>
        <?php if (isset($_GET['removeSuccess'])): ?>
            <div class="happy-toast">Media removed successfully!</div>
        <?php endif ?>
        <?php if (isset($_GET['editSuccess'])): ?>
            <div class="happy-toast">Event details updated successfully!</div>
        <?php endif ?>
        <?php if (isset($_GET['checkIn'])) {
            if ($_GET['checkIn'] === 'success') {
                echo '<div class="happy-toast">You\'ve checked into the meeting!</div>';
            } elseif ($_GET['checkIn'] === 'fail') {
                echo '<div class="error-toast">You\'ve already checked in to this meeting.</div>';
            }
        } ?>
        <?php

        require_once('include/output.php');
        $event_name = $event_info['name'];
        $event_type = $event_info['eventType'];
        $event_date = date('l, F j, Y', strtotime($event_info['date']));
        $event_startTime = time24hto12h($event_info['startTime']);
        $event_endTime = time24hto12h($event_info['endTime']);
        $event_location = $event_info['location'];
        $event_description = $event_info['description'];
        $event_in_past = strcmp(date('Y-m-d'), $event_info['date']) > 0;
        require_once('include/time.php');
        $event_duration = calculateHourDuration($event_info['startTime'], $event_info['endTime']);
        $event_duration = floatPrecision($event_duration, 2);

        $currentDate = new DateTime();
        $justDate = $currentDate->format('Y-m-d');
        $datePassed = false;

        if ($justDate > $event_info['date']) {
            $datePassed = true;
        }

        //var_dump($justDate);
        //var_dump($event_info['date']);

        if ($event_duration == floor($event_duration)) {
            $event_duration = intval($event_duration);
        }
        echo '<h2 class="centered">' . $event_name . '</h2>';
        ?>
        <div id="table-wrapper">
            <table class="centered">
                <tbody>
                    <tr>
                        <td class="label">Date </td>
                        <td><?php echo $event_date ?></td>
                    </tr>
                    <tr>
                        <td class="label">Time </td>
                        <td><?php echo $event_startTime . ' - ' . $event_endTime ?></td>
                    </tr>
                    <tr>
                        <td class="label">Duration</td>
                        <td><?php echo $event_duration . ' hours' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Location </td>
                        <td><?php echo $event_location ?></td>
                    </tr>
                    <tr>
                        <td class="label">Description </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td id="description-cell" colspan="2"><?php echo $event_description ?></td>
                    </tr>
                    <?php
                    $canHaveMedia = $event_type != 'board_meeting';
                    ?>
                    <?php if ($canHaveMedia): ?>
                        <tr>
                            <td class="label">Training Materials </td>
                            <td></td>
                        </tr>
                        <!-- <td colspan="2" class="inactive">None at this time</td> -->
                        <?php
                        $medias = get_event_training_media($id);
                        foreach ($medias as $media) {
                            echo '<tr class="media"><td colspan="2">';
                            if ($media['format'] == 'link') {
                                echo '<a href="' . $media['url'] . '">' . $media['description'] . '</a>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                            } else if ($media['format'] == 'picture') {
                                echo '<span>' . $media['description'] . '</span>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                                echo '<br><a href="' . $media['url'] . '"><img style="max-width: 30vw" src="' . $media['url'] . '" alt="' . $media['description'] . '"></a>';
                            } else {
                                echo '<span>' . $media['description'] . '</span>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                                echo '<br><iframe width="560" height="315" src="' . $media['url'] . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
                            }
                            echo '</td></tr>';
                        }
                        if (count($medias) == 0) {
                            echo '<td colspan="2" class="inactive">None at this time</td>';
                        }
                        ?>
                        <?php if ($access_level >= 2): ?>
                            <tr>
                                <td colspan="2">
                                    <form class="media-form hidden" method="post" id="attach-training-media-form">
                                        <label>Attach Event Training Media</label>
                                        <label for="url">URL</label>
                                        <input type="text" id="url" name="url" placeholder="Paste link to media" required>
                                        <p class="error hidden" id="url-error">Please enter a valid URL.</p>
                                        <label for="description">Description</label>
                                        <input type="text" id="description" name="description" placeholder="Enter a description" required>
                                        <label for="format">Format</label>
                                        <select id="format" name="format">
                                            <option value="link">Link</option>
                                            <option value="video">YouTube video (embeds video)</option>
                                            <option value="picture">Picture (embeds picture)</option>
                                        </select>
                                        <input type="hidden" name="id" value="<?php echo $id ?>">
                                        <input type="submit" name="attach-training-media-submit" value="Attach">
                                    </form>
                                    <a id="attach-training-media">Attach Event Training Media</a>
                                </td>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <td class="label">Post-Event Media </td>
                            <td></td>
                        </tr>
                        <?php
                        $medias = get_post_event_media($id);
                        foreach ($medias as $media) {
                            echo '<tr class="media"><td colspan="2">';
                            if ($media['format'] == 'link') {
                                echo '<a href="' . $media['url'] . '">' . $media['description'] . '</a>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                            } else if ($media['format'] == 'picture') {
                                echo '<span>' . $media['description'] . '</span>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                                echo '<br><a href="' . $media['url'] . '"><img style="max-width: 30vw" src="' . $media['url'] . '" alt="' . $media['description'] . '"></a>';
                            } else {
                                echo '<span>' . $media['description'] . '</span>';
                                if ($access_level >= 2) {
                                    echo ' <a href="detachMedia.php?eid=' . $id . '&mid=' . $media['id'] . '">Remove</a>';
                                }
                                echo '<br><iframe width="560" height="315" src="' . $media['url'] . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
                            }
                            echo '</td></tr>';
                        }
                        if (count($medias) == 0) {
                            echo '<td colspan="2" class="inactive">None at this time</td>';
                        }
                        ?>
                        <?php if ($access_level >= 2): ?>
                            <tr>
                                <td colspan="2">
                                    <form class="media-form hidden" method="post" id="attach-post-media-form">
                                        <label>Attach Post-Event Media</label>
                                        <label for="post-url">URL</label>
                                        <input type="text" id="post-url" name="url" placeholder="Paste link to media" required>
                                        <p class="error hidden" id="post-url-error">Please enter a valid URL.</p>
                                        <label for="post-description">Description</label>
                                        <input type="text" id="post-description" name="description" placeholder="Enter a description" required>
                                        <label for="post-format">Format</label>
                                        <select id="post-format" name="format">
                                            <option value="link">Link</option>
                                            <option value="video">YouTube video (embeds video)</option>
                                            <option value="picture">Picture (embeds picture)</option>
                                        </select>
                                        <input type="hidden" name="id" value="<?php echo $id ?>">
                                        <input type="submit" name="attach-post-media-submit" value="Attach">
                                    </form>
                                    <a id="attach-post-media">Attach Post-Event Media</a>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endif ?>
                    <?php
                    if ($access_level >= 2) {
                        echo '
                                <tr>
                                    <td colspan="2">
                                        <a href="editEvent.php?id=' . $id . '" class="button">Edit Event Details</a>
                                    </td>
                                </tr>
                            ';
                    }
                    /*
                    if ($access_level >= 2 && $event_info['eventType'] == 'board_meeting') {
                        echo '
                    <form method = "post">
                        <tr>
                            <td colspan="2">
                            <input type="hidden" name="id" value="' . $id . '">
                                <button type="submit" value = "submitTime" name="submitTime"> Check In </button>
                            </td>
                        </tr>
                        
                    </form>
                    ';
                    }
*/

                    ?>
                </tbody>
            </table>
        </div>
        <!-- Consolidated Event Volunteers Section -->
        <h2 class="centered">Event Volunteers</h2>
        <div class="standout">
            <?php
            $event_persons = getvolunteers_byevent($id);
            $capacity = intval($event_info['capacity']);
            $num_persons = count($event_persons);
            $remaining_slots = $capacity - $num_persons;
            $user_id = $_SESSION['_id'];
            $already_assigned = false;

            ?>

            <?php if ($event_in_past): ?>
                <!-- Past Event: Display a checkbox form for confirming hours -->
                <form method="POST" action="confirmhours.php" id="confirmHoursForm">
                    <ul class="centered">
                        <li class="centered">
                            <input type="checkbox" id="select_all" onclick="toggleSelectAll(this)">
                            <label for="select_all"><strong>Select All</strong></label>
                        </li>
                        <?php foreach ($event_persons as $person): ?>
                            <li class="centered">
                                <input type="checkbox" name="volunteers[]" id="vol_<?php echo $person->get_id(); ?>" value="<?php echo $person->get_id(); ?>">
                                <label for="vol_<?php echo $person->get_id(); ?>">
                                    <?php echo htmlspecialchars($person->get_first_name() . ' ' . $person->get_last_name());?>
                                </label>
                            </li>

                            <?php if ($person->get_id() == $user_id) {
                                $already_assigned = true;
                            } ?>
                        <?php endforeach; ?>
                        <?php for ($x = 0; $x < $remaining_slots; $x++): ?>
                            <li class="centered empty-slot">-Empty Slot-</li>
                        <?php endfor; ?>
                    </ul>
                    <input type="hidden" name="event_id" value="<?php echo $id; ?>">
                    <input type="submit" name="confirm_hours_submit" value="Confirm Hours">
                </form>
                <script>
                    function toggleSelectAll(source) {
                        var checkboxes = document.querySelectorAll('#confirmHoursForm input[type="checkbox"][name="volunteers[]"]');
                        for (var i = 0; i < checkboxes.length; i++) {
                            checkboxes[i].checked = source.checked;
                        }
                    }
                </script>

            <?php else: ?>
                <!-- Future/Current Event: Display the standard volunteer list -->
                <ul class="centered">
                    <?php
                    if ($remaining_slots) {
                        echo '<li class="centered">' . $remaining_slots . ' / ' . $capacity . ' Slots Remaining</li>';
                    } else {
                        echo '<li class="centered">This event is fully booked!</li>';
                    }
                    foreach ($event_persons as $person) {
                        if ($person->get_id() == $user_id) {
                            $already_assigned = true;
                        }

                        $hasNotes = false;
                        if ($person->get_notes() != null && $person->get_notes() != '') {
                            $hasNotes = true;
                        }
                        $fullName = htmlspecialchars($person->get_first_name() . ' ' . $person->get_last_name());
                        $volunteerId = htmlspecialchars($person->get_id());
                        // admin wants a tag to show if a person has personal profile notes. 
                        // If the person has notes, concatenate "NOTES " after fullName. Otherwise, concatenate ''.
                        if ($access_level > 1) {
                            echo '<li class="centered remove-person">' .
                            // Display the name as a clickable link, redirecting to view profile page
                            '<form class="remove-person" method="GET" action="viewProfile.php">' .
                            '<input type="hidden" name="id" value="' . $volunteerId . '">' .
                            '<input class="stripped" type="submit" value="' . $fullName . ($hasNotes ? ' NOTES' : '') . '" />' .
                            '</form>' .

                            // display "Remove" as a clickable form to remove the volunteer from the event
                            '<form class="remove-person" method="GET">' .
                                '<input type="hidden" name="request_type" value="remove" />' .
                                '<input type="hidden" name="id" value="' . $id . '">' .
                                '<input type="hidden" name="selected_removal_id" value="' . $person->get_id() . '" />' .
                                '<input class="stripped" type="submit" value="Remove" />' .
                                '</form></li>';
                        } else {
                            echo '<li class="centered">' . $fullName . '</li>';
                        }
                    }
                    for ($x = 0; $x < $remaining_slots; $x++) {
                        echo '<li class="centered empty-slot">-Empty Slot-</li>';
                    }
                    ?>
                </ul>
                <?php
                if ($remaining_slots > 0 && $user_id != 'vmsroot') {
                    if (!$already_assigned) {
                        if ($active) {
                            echo '<form method="GET">' .
                                '<input type="hidden" name="request_type" value="add self">' .
                                '<input type="hidden" name="id" value="' . $id . '">' .
                                '<input type="submit" value="Sign Up">' .
                                '</form>';
                        } else {
                            echo '<div class="centered">As an inactive volunteer, you are ineligible to sign up for events.</div>';
                        }
                    } else {
                        echo '<div class="centered">You are signed up for this event!</div>';
                        if ($access_level >= 2 && $event_info['eventType'] == 'board_meeting') {
                            echo '
                        <form method = "post">
                            <tr>
                                <td colspan="2">
                                <input type="hidden" name="id" value="' . $id . '">
                                    <button type="submit" value = "submitTime" name="submitTime" style="margin-top: 10px;"> Check In </button>
                                </td>
                            </tr>
                            
                        </form>
                        ';
                        }
                    }
                } else if ($already_assigned) {
                    echo '<div class="centered">You are signed up for this event!</div>';
                    if ($access_level >= 2 && $event_info['eventType'] == 'board_meeting') {
                        echo '
                    <form method = "post">
                        <tr>
                            <td colspan="2">
                            <input type="hidden" name="id" value="' . $id . '">
                                <button type="submit" value = "submitTime" name="submitTime"style="margin-top: 10px;"> Check In </button>
                            </td>
                        </tr>
                        
                    </form>
                    ';
                    }
                }

                if ($access_level >= 2 && $num_persons > 0) {
                    echo '<br/><a href="roster.php?id=' . $id . '" class="button">View Event Roster</a>';
                }
                ?>
            <?php endif; ?>
        </div>

        <?php
        if ($remaining_slots > 0) {
            if ($access_level >= 2) {
                if ($event_in_past) {
                    echo '<div id="assign-volunteer" class="standout"><label>Assign Volunteer</label><p>This event is archived. Volunteers cannot be assigned.</p></div>';
                } else {
                    $all_volunteers = get_unassigned_available_volunteers($id);
                    if ($all_volunteers) {
                        echo '<form method="GET" id="assign-volunteer" class="standout">';
                        echo '<input type=hidden name="request_type" value="add another">';
                        echo '<input type="hidden" name="id" value="' . $id . '">';
                        echo '<label for="volunteer-select">Assign Volunteer:</label>';
                        echo '<div class="pair"><select name="selected_id" id="volunter-select" required>';
                        if ($all_volunteers) {
                            for ($x = 0; $x < count($all_volunteers); $x++) {
                                echo '<option value="' . $all_volunteers[$x]->get_id() . '">' . $all_volunteers[$x]->get_last_name() . ', ' . $all_volunteers[$x]->get_first_name() . '</option>';
                            }
                        }
                        echo '</select>';
                        echo '<input type="submit" value="Assign" /></div>';
                        echo '</form>';
                    } else {
                        echo '<div id="assign-volunteer" class="standout"><label>Assign Volunteer</label><p>There are currently no volunteers available to assign to this event.</p></div>';
                    }
                }
            }
        }
        ?>

        <?php if ($access_level >= 2) : ?>
            <!-- <form method="post" action="deleteEvent.php">
                <input type="submit" value="Delete Event">
                <input type="hidden" name="id" value="<?= $id ?>">
            </form> -->
            <button onclick="showDeleteConfirmation()">Delete Event</button>
        <?php endif ?>

        <a href="calendar.php?month=<?php echo substr($event_info['date'], 0, 7) ?>" class="button cancel" style="margin-top: -.5rem">Return to Calendar</a>
    </main>
</body>

</html>