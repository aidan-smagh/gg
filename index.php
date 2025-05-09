<?php
    session_cache_expire(30);
    session_start();

    date_default_timezone_set("America/New_York");
    
    if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
        if (isset($_SESSION['change-password'])) {
            header('Location: changePassword.php');
        } else {
            header('Location: login.php');
        }
        die();
    }
        
    include_once('database/dbPersons.php');
    include_once('domain/Person.php');
    // Get date?
    if (isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }
    $notRoot = $person->get_id() != 'vmsroot';
    //see if person is board member (for redirection to proper form for editing profile)
    $isBoardMember = false;
    $isSuperAdmin = false;
    $userType = $person->get_type()[0];
    if ($userType == "boardmember") {
        $isBoardMember = true;
    }
    if ($userType == "superadmin") {
        $isSuperAdmin = true;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <?php require('universal.inc'); ?>
        <title>Gwyneth's Gift VMS | Dashboard</title>
    </head>
    <body>
        <?php require('header.php'); ?>
        <h1>Welcome</h1>
        <main class='dashboard'>
            <?php if (isset($_GET['pcSuccess'])): ?>
                <div class="happy-toast">Password changed successfully!</div>
            <?php elseif (isset($_GET['registerSuccess'])): ?>
                <div class="happy-toast">Volunteer registered successfully!</div>
            <?php elseif (isset($_GET['eventError'])): ?>
                <div class="error-toast">The event could not be accessed.</div>
            <?php endif ?>
            <p>Welcome back, <?php echo $person->get_first_name() ?>!</p>
            <p>Today is <?php echo date('l, F j, Y'); ?>.</p>
            <div id="dashboard">
                <?php
                    require_once('database/dbMessages.php');
                    $unreadMessageCount = get_user_unread_count($person->get_id());
                    $inboxIcon = 'inbox.svg';
                    if ($unreadMessageCount) {
                        $inboxIcon = 'inbox-unread.svg';
                    }
                ?>
                <div class="dashboard-item" data-link="inbox.php">
                    <img src="images/<?php echo $inboxIcon ?>">
                    <span>Notifications<?php 
                        if ($unreadMessageCount > 0) {
                            echo ' (' . $unreadMessageCount . ')';
                        }
                    ?></span>
                </div>
                <div class="dashboard-item" data-link="calendar.php">
                    <img src="images/view-calendar.svg">
                    <span>View Calendar</span>
                </div>
                <?php if ($_SESSION['access_level'] >= 2): ?>
                    <div class="dashboard-item" data-link="addEvent.php">
                        <img src="images/new-event.svg">
                        <span>Create Event</span>
                    </div>
                <?php endif ?>
                <div class="dashboard-item" data-link="eventSearch.php">
                    <img src="images/search.svg">
                    <span>Find Event</span>
                </div>
                <?php if ($_SESSION['access_level'] >= 2): ?>
                    <div class="dashboard-item" data-link="personSearch.php">
                        <img src="images/person-search.svg">
                        <span>Find Person</span>
                    </div>
                    <div class="dashboard-item" data-link="register.php">
                        <img src="images/add-person.svg">
                        <span>Register Volunteer</span>
                    </div>
                    <div class="dashboard-item" data-link="report.php">
                        <img src="images/create-report.svg">
                        <span>Create Report</span>
                    </div>
                <?php endif ?>
                <?php if ($notRoot) : ?>
                    <div class="dashboard-item" data-link="viewProfile.php">
                        <img src="images/view-profile.svg">
                        <span>View Profile</span>
                    </div>

                    <?php if ($isBoardMember): ?>
                        <div class="dashboard-item" data-link="editBoardMemberProfile.php">
                            <img src="images/manage-account.svg">
                            <span>Edit Profile</span>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-item" data-link="editProfile.php">
                            <img src="images/manage-account.svg">
                            <span>Edit Profile</span>
                        </div>
                    <?php endif ?>
                <?php endif ?>
                <?php if ($notRoot) : ?>
                    <div class="dashboard-item" data-link="volunteerReport.php">
                        <img src="images/volunteer-history.svg">
                        <span>View My Hours</span>
                    </div>
                <?php endif ?>
                <?php if ($_SESSION['access_level'] >= 2): ?>
                    <div class="dashboard-item" data-link="forums.php">
                        <img src="images/forum.svg">
                        <span>Forums</span>
                    </div>
                <?php endif ?>
                <?php if ($_SESSION['access_level'] >= 2): ?>
                    <div class="dashboard-item" data-link="viewEvents.php">
                        <img src="images/card-checklist.svg">
                        <span>View events</span>
                    </div>
                <?php endif ?>

                <?php if ($isSuperAdmin || $isBoardMember): ?>
                    <div class="dashboard-item" data-link="externalDocuments.php">
                        <img src="images/external-docs.svg">
                        <span style="text-align: center;">External Documents</span>
                    </div>
                <?php endif ?>
                <?php if ($isSuperAdmin || $isBoardMember): ?>
                    <div class="dashboard-item" data-link="addNewTraining.php">
                        <img src="images/person-raised-hand.svg">
                        <span style="text-align: center;">Add Training</span>
                    </div>
                <?php endif ?>
                <div class="dashboard-item" data-link="volunteerHoursConfirmation.php">
                    <img src="images/volunteer-hours.svg">
                    <span style="text-align: center;">Volunteer Hours Certificate</span>
                </div>
                <?php if ($isSuperAdmin || $isBoardMember): ?>
                    <div class="dashboard-item" data-link="createPersonnelRosters.php">
                        <img src="images/person-lines-fill.svg">
                        <span style="text-align: center;">Create Personnel Rosters</span>
                    </div>
                <?php endif ?>
                <div class="dashboard-item" data-link="changePassword.php">
                    <img src="images/change-password.svg">
                    <span>Change Password</span>
                </div>
                <div class="dashboard-item" data-link="logout.php">
                    <img src="images/logout.svg">
                    <span>Log out</span>
                </div>
            </div>
        </main>
    </body>
</html>