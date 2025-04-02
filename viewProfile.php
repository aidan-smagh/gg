<?php
// Make session information accessible, allowing us to associate
// data with the logged-in user.
session_cache_expire(30);
session_start();

$loggedIn = false;
$accessLevel = 0;
$userID = null;
$isAdmin = false;
$isBoardMember = false;
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    header('Location: login.php');
    die();
}
if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
    $accessLevel = $_SESSION['access_level'];
    $isAdmin = $accessLevel >= 2;
    $userID = $_SESSION['_id'];
} else {
    header('Location: login.php');
    die();
}
if ($isAdmin && isset($_GET['id'])) {
    require_once('include/input-validation.php');
    $args = sanitize($_GET);
    $id = strtolower($args['id']);
} else {
    $id = $userID;
}
require_once('database/dbPersons.php');
require_once('database/dbTrainings.php');
$con = connect();

$query = "SELECT dbTrainings.name AS training, dbPersons.id AS volunteer_name
          FROM dbPersons
          JOIN dbpersonstrainings ON dbPersons.id = dbpersonstrainings.id
          JOIN dbTrainings ON dbpersonstrainings.training_name = dbTrainings.name";

$result = mysqli_query($con, $query);
if (isset($_GET['removePic'])) {
    if ($_GET['removePic'] === 'true') {
        remove_profile_picture($id);
    }
}

$user = retrieve_person($id);
$userType = $user->get_type()[0];
if ($userType == 'boardmember') {
    $isBoardMember = true;
}

$viewingOwnProfile = $id == $userID;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['url'])) {
        if (!update_profile_pic($id, $_POST['url'])) {
            header('Location: viewProfile.php?id=' . $id . '&picsuccess=False');
        } else {
            header('Location: viewProfile.php?id=' . $id . '&picsuccess=True');
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php require_once('universal.inc') ?>
    <!-- <link rel="stylesheet" href="css/editprofile.css" type="text/css" /> -->
    <title>Gwyneth's Gift VMS | View User</title>
</head>

<body>
    <?php
    require_once('header.php');
    require_once('include/output.php');
    ?>
    <h1>View Profile</h1>
    <main class="general">
        <?php
        if (isset($_GET['picsuccess'])) {
            $picsuccess = $_GET['picsuccess'];
            if ($picsuccess === 'True') {
                echo '<div class="happy-toast">Profile Picture Updated Successfully!</div>';
            } else if ($picsuccess === 'False') {
                echo '<div class="error-toast">There was an error updating the Profile Picture!</div>';
            }
        }
        ?>
        <?php if ($id == 'vmsroot'): ?>
            <div class="error-toast">The root user does not have a profile.</div>
    </main>
</body>

</html>
<?php die() ?>
<?php elseif (!$user): ?>
    <div class="error-toast">User does not exist!</div>
    </main>
    </body>

    </html>
    <?php die() ?>
<?php endif ?>
<?php if (isset($_GET['editSuccess'])): ?>
    <div class="happy-toast">Profile updated successfully!</div>
<?php endif ?>
<?php if (isset($_GET['rscSuccess'])): ?>
    <div class="happy-toast">User's role and/or status updated successfully!</div>
<?php endif ?>
<?php if ($viewingOwnProfile): ?>
    <h2>Your Profile</h2>
<?php else: ?>
    <?php if ($user->get_prefix() != null): ?>
        <h2>Viewing <?php echo $user->get_prefix() . ' ' . $user->get_first_name() . ' ' . $user->get_last_name() ?></h2>
    <?php else: ?>
    <h2>Viewing <?php echo $user->get_first_name() . ' ' . $user->get_last_name() ?></h2>
    <?php endif ?>
<?php endif ?>

<fieldset>
    <legend>General Information</legend>

    <label>Username</label>
    <p><?php echo $user->get_id() ?></p>

    <label>Profile Picture</label>
    <img class="profile-pic" src="<?php
                                    $profile_pic = $user->get_profile_pic();
                                    if ($profile_pic) {
                                        echo $profile_pic;
                                    } else {
                                        echo 'images/default-profile-picture.svg';
                                    }
                                    ?>" width="140" height="140">

    <form class="media-form hidden" method="post" id="edit-profile-picture-form">
        <label>Edit Photo</label>
        <label for="url">URL</label>
        <input type="text" id="url" name="url" placeholder="Paste link to media" required>
        <p class="error hidden" id="url-error">Please enter a valid URL.</p>
        <input type="hidden" name="id" value="<?php echo $id ?>">
        <input type="submit" name="edit-profile-picture-submit" value="Attach">
    </form>

    <a id="edit-profile-picture" class="link-like">Edit Photo</a>
    <?php
    echo '<a href="viewProfile.php?id=' . $id . '&removePic=true" style="color:inherit">Remove Photo</a>'
    ?>

    <label>Gender</label>
    <p><?php echo $user->get_gender(); ?></p>

    <label>Date of Birth</label>
    <p><?php echo date('d/m/Y', strtotime($user->get_birthday())) ?></p>

    <label>Start Date</label>
    <p><?php echo $user->get_start_date(); ?> </p>

    <label>Address</label>
    <p><?php echo $user->get_address() . ', ' . $user->get_city() . ', ' . $user->get_state() . ' ' . $user->get_zip() ?></p>


    <!-- Check if user has a registered mailing address. If they do, display it -->
     <?php if ($user->get_mailing_address() != null): ?>
        <label>Mailing Address</label>
        <p><?php echo $user->get_mailing_address() . ', ' . $user->get_mailing_city() . ', ' . $user->get_mailing_state() . ' ' . $user->get_mailing_zip() ?></p>
    <?php endif ?>
    
    <label>Role</label>
    <p><?php echo ucfirst($user->get_type()[0]) ?></p>

    <label>Status</label>
    <p><?php
        $status = ucfirst($user->get_status());
        $reason = $user->get_notes();
        if ($status == "Inactive" && $reason) {
            echo "Inactive (" . $reason . ")";
        } else {
            echo $status;
        }
        ?></p>
        <?php /* T-shirt size is displayed in volunteer information section, but that section only
             displays on volunteer profiles. It is added here for every profile that is not a volunteer */
        if ($userType != 'volunteer'): ?>    
            <label>T-Shirt Size</label>
            <p>
                <?php
                $sizes = [
                    null => '',
                    '' => '',
                    'S' => 'Small',
                    'M' => 'Medium',
                    'L' => 'Large',
                    'XL' => 'Extra Large',
                    'XXL' => '2X Large',
                ];
                $size = $sizes[$user->get_shirt_size()];
                echo $size;
                ?>
            </p>      
        <?php endif ?>

    <?php if ($id != $userID && $accessLevel >= 2): ?>
        <?php if ($accessLevel >= 3): ?>
            <a href="modifyUserRole.php?id=<?php echo $id ?>" class="button">Change Role/Status</a>
        <?php else: ?>
            <a href="modifyUserRole.php?id=<?php echo $id ?>" class="button">Change Status</a>
        <?php endif ?>
    <?php endif ?>
</fieldset>

<!-- Other Organizational Affiliation section is only for board members -->
<?php if ($isBoardMember): ?>
    <fieldset>
        <legend>Other Organizational Affiliation</legend>
        <label>Organization Affiliated With</label>
        <p><?php echo $user->get_affiliated_org() ?></p>
        <label>Title at Affiliated Organization</label>
        <p><?php echo $user->get_title_at_affiliated_org() ?></p>
    </fieldset>
<?php endif ?>

<fieldset>
    <legend>Contact Information</legend>
    <label>E-mail</label>
    <p><a href="mailto:<?php echo $user->get_email() ?>"><?php echo $user->get_email() ?></a></p>

    <label>Phone Number</label>
    <p><a href="tel:<?php echo $user->get_phone1() ?>"><?php echo formatPhoneNumber($user->get_phone1()) ?></a> (<?php echo ucfirst($user->get_phone1type()) ?>)</p>
    
    <!-- Display secondary phone number if provided -->
    <?php if ($user->get_phone2() != null): 
        $phone2type = $user->get_phone2type();
        /* db stores secondary phone types with a 2 prepended. Remove that ending 2 for display */
        if (substr($phone2type, -1) == '2') {
            $phone2Type = substr($phone2type, 0, -1); // remove last character
        }
        ?>
            <label>Secondary Phone Number</label>
            <p><a href="tel:<?php echo $user->get_phone2() ?>"><?php echo formatPhoneNumber($user->get_phone2()) ?></a> (<?php echo ucfirst($phone2Type) ?>)</p>
    <?php endif ?>
    
    <label>Preferred Contact Method</label>
    <p><?php echo ucfirst($user->get_cMethod()) ?></p>
    
    <label>Best Time to Contact</label>
    <p><?php echo ucfirst($user->get_contact_time()) ?></p>
</fieldset>

<fieldset>
    <legend>Emergency Contact</legend>
    <label>Name</label>
    <p><?php echo $user->get_contact_name() ?></p>
    <label>Relation</label>
    <p><?php echo $user->get_relation() ?></p>
    <label>Phone Number</label>
    <p><a href="tel:<?php echo $user->get_contact_num() ?>"><?php echo formatPhoneNumber($user->get_contact_num()) ?></a></p>
</fieldset>

<!-- Only display the following info if the person who owns the profile is a volunteer -->
<?php if ($userType == 'volunteer'): ?>
    <fieldset>
        <legend>Volunteer Information</legend>
        <label>Availability</label>
        <?php if ($user->get_sunday_availability_start()): ?>
            <label>Sundays</label>
            <p><?php echo time24hTo12h($user->get_sunday_availability_start()) . ' - ' . time24hTo12h($user->get_sunday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_monday_availability_start()): ?>
            <label>Mondays</label>
            <p><?php echo time24hTo12h($user->get_monday_availability_start()) . ' - ' . time24hTo12h($user->get_monday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_tuesday_availability_start()): ?>
            <label>Tuedays</label>
            <p><?php echo time24hTo12h($user->get_tuesday_availability_start()) . ' - ' . time24hTo12h($user->get_tuesday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_wednesday_availability_start()): ?>
            <label>Wednesdays</label>
            <p><?php echo time24hTo12h($user->get_wednesday_availability_start()) . ' - ' . time24hTo12h($user->get_wednesday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_thursday_availability_start()): ?>
            <label>Thursdays</label>
            <p><?php echo time24hTo12h($user->get_thursday_availability_start()) . ' - ' . time24hTo12h($user->get_thursday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_friday_availability_start()): ?>
            <label>Fridays</label>
            <p><?php echo time24hTo12h($user->get_friday_availability_start()) . ' - ' . time24hTo12h($user->get_friday_availability_end()) ?></p>
        <?php endif ?>
        <?php if ($user->get_saturday_availability_start()): ?>
            <label>Saturdays</label>
            <p><?php echo time24hTo12h($user->get_saturday_availability_start()) . ' - ' . time24hTo12h($user->get_saturday_availability_end()) ?></p>
        <?php endif ?>
        <!--<label>Skills</label>
        <p><?php echo str_replace("\r\n", '<br>', $user->get_specialties()) ?></p>
        -->
        <label>Additional Information</label>
        <p><?php if ($user->get_computer()) echo 'Owns a computer';
            else echo 'Does NOT own a computer'; ?></p>
        <p><?php if ($user->get_camera()) echo 'Owns a camera';
            else echo 'Does NOT own a camera'; ?></p>
        <p><?php if ($user->get_transportation()) echo 'Has access to transportation';
            else echo 'Does NOT have access to transportation'; ?></p>
        <label>T-Shirt Size</label>
        <p>
            <?php
            $sizes = [
                null => '',
                '' => '',
                'S' => 'Small',
                'M' => 'Medium',
                'L' => 'Large',
                'XL' => 'Extra Large',
                'XXL' => '2X Large',
            ];
            $size = $sizes[$user->get_shirt_size()];
            echo $size;
            ?>
        </p>
    </fieldset>
    <?php endif ?>
<fieldset>
    <legend>Miscellaneous</legend>
    <label>Skills</label>
    <p><?php echo str_replace("\r\n", '<br>', $user->get_specialties()) ?></p>
    <label>Training</label>
    <?php 
        $training = get_trainings_for($id);
    ?>
    <p><?php foreach($training as $trainings) {
        echo $trainings . "<br>";
    } ?></p>

</fieldset>
<?php if (($accessLevel == 2 && $user->get_access_level() == 1) || $accessLevel >= 3): ?>    
    <fieldset>
        <legend>Notes</legend>
        <label>Notes</label>
        <p>
            <?php echo $user->get_notes()?>
        </p>
    </fieldset>
<?php endif ?>

<a class="button" href="editVolunteerNotes.php<?php if ($id != $userID) echo '?id=' . $id ?>">Edit Notes About A Volunteer</a>
<a class="button" href="addTraining.php<?php if ($id != $userID) echo '?id=' . $id ?>">Add Completed Training</a>
    
<?php if (!$isBoardMember): ?>
    <a class="button" href="editProfile.php<?php if ($id != $userID) echo '?id=' . $id ?>">Edit Profile</a>
<?php else: ?>
    <a class="button" href="editBoardMemberProfile.php<?php if ($id != $userID) echo '?id=' . $id ?>">Edit Profile</a>
<?php endif ?>

<?php if ($id != $userID): ?>
    <?php if (($accessLevel == 2 && $user->get_access_level() == 1) || $accessLevel >= 3): ?>
        <a class="button" href="resetPassword.php?id=<?php echo htmlspecialchars($_GET['id']) ?>">Reset Password</a>
    <?php endif ?>
    <a class="button" href="volunteerReport.php?id=<?php echo htmlspecialchars($_GET['id']) ?>">View Volunteer Hours</a>
    <a class="button cancel" href="personSearch.php">Return to User Search</a>
<?php else: ?>
    <a class="button" href="changePassword.php">Change Password</a>
    <a class="button" href="volunteerReport.php">View Volunteer Hours</a>
    <a class="button cancel" href="index.php">Return to Dashboard</a>
<?php endif ?>
</main>
</body>

</html>