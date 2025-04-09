<?php
    // Author: Lauren Knight
    // Description: Registration page for new volunteers
    session_cache_expire(30);
    session_start();
    
    require_once('include/input-validation.php');

    $loggedIn = false;
    if (isset($_SESSION['change-password'])) {
        header('Location: changePassword.php');
        die();
    }
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Gwyneth's Gift VMS | Register <?php if ($loggedIn) echo ' New Volunteer' ?></title>
</head>
<body>
    <?php
        require_once('header.php');
        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        require_once('database/dbMessages.php');
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // make every submitted field SQL-safe except for password
            $ignoreList = array('password');
            $args = sanitize($_POST, $ignoreList);

            $required = array(
                'first-name', 'last-name', 'birthdate',
                'address', 'city', 'state', 'zip', 
                'email', 'phone',
                'start-date', 'password',
            );
            $errors = false;
            if (!wereRequiredFieldsSubmitted($args, $required)) {
                $errors = true;
            }

            /* Personal Information Section Data */
            $first = $args['first-name'];
            $last = $args['last-name'];
            $prefix = $args['prefix'];
            $gender = $args['gender'];
            $dateOfBirth = validateDate($args['birthdate']);
            if (!$dateOfBirth) {
                $errors = true;
                echo 'bad dob';
            }
            $shirtSize = $args['shirt-size'];
            $startDate = validateDate($args['start-date']);
            if (!$startDate) {
                $errors = true;
                echo 'bad start date';
            }

            /* Home Address Section Data */ 
            $address = $args['address'];
            $city = $args['city'];
            $state = $args['state'];
            if (!valueConstrainedTo($state, array('AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA',
                    'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
                    'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM',
                    'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX',
                    'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY'))) {
                $errors = true;
            }
            $zipcode = $args['zip'];
            if (!validateZipcode($zipcode)) {
                $errors = true;
                echo 'bad zip';
            }

            /* Mailing Address Section Data 
               The mailing address fields are required. However, javascript allows the use to select "same-as-home".
               If the use selects the same as home checkbox, the entry boxes for mailing address fields will be disabled.
               The form will won't submit anything for those fields, so that logic must happen here
            */
            if (isset($args['mailing-address'])) {
                // User entered separate value for mailing address
                $mailingAddress = $args['mailing-address'];
            }
            else {
                //User selected Same as home, so use home address values
                $mailingAddress = $address;
            }
            if (isset($args['mailing-city'])) {
                //user entered separate value for mailing city
                $mailingCity = $args['mailing-city'];
            }
            else {
                //user selected same as home
                $mailingCity = $city;
            }
            if (isset($args['mailing-state'])) {
                //user entered separate value for mailing state
                $mailingState = $args['mailing-state'];
            }
            else {
                //user selected same as home
                $mailingState = $state;
            }
            if (!valueConstrainedTo($mailingState, array('AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA',
                    'HI', 'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
                    'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ', 'NM',
                    'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX',
                    'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY'))) {
                $errors = true;
            }
            if (isset($args['mailing-zip'])) {
                //user entered separate value for mailing zip
                $mailingZip = $args['mailing-zip'];
            }
            else {
                //user selected same as home
                $mailingZip = $zipcode;
            }
            if (!validateZipcode($mailingZip)) {
                $errors = true;
                echo 'bad zip';
            }

            /* Affiliation With Outside Organization Section Data */
            $affiliatedOrg = $args['affiliated-org'];
            $titleAtAffiliatedOrg = $args['affiliated-title'];

            /* Contact Information Section Data */
            //Processing primary phone
            $phone = validateAndFilterPhoneNumber($args['phone']);
            if (!$phone) {
                $errors = true;
                echo 'bad phone';
            }
            $phoneType = '';
            if (!isset($args['phone-type']) || $args['phone-type'] == '') {
                $args['phone-type'] = 'cellphone';
            }
            $phoneType = $args['phone-type'];
            if (!valueConstrainedTo($phoneType, array('cellphone', 'home', 'work',''))) {
                $errors = true;
                echo 'bad phone type';
            }
            //processing secondary phone, which is not a required field in the form
            $phone2 = '';
            // Only proceed if phone2 was provided in the form
            if (!empty($args['phone2'])) {
                $phone2 = validateAndFilterPhoneNumber($args['phone2']);
                if (!$phone2) {
                    $errors = true;
                    echo 'bad phone2';
                }
            }
            $phone2Type = '';
            if (isset($args['phone2-type']) && !empty($args['phone2-type'])) {
                $phone2Type = $args['phone2-type'];
                if (!valueConstrainedTo($phone2Type, array('cellphone2', 'home2', 'work2',''))) {
                    $errors = true;
                    echo 'bad phone2 type';
                }
            }
            $contactWhen = $args['contact-when'];
            $contactMethod = ''; // Initialize $contactMethod
            if (!isset($args['contact-method']) || $args['contact-method'] == '') {
                $args['contact-method'] = 'text';
            }
            $contactMethod = $args['contact-method'];
            if (!valueConstrainedTo($contactMethod, array('phone', 'text', 'email',''))) {
                $errors = true;
                echo 'bad contact method';
            }

            /* Emergency Contact Information Section Data */
            // Collect econtact name if provided, otherwise set it to the empty string
            if (isset($args['econtact-name']) && !empty($args['econtact-name'])) {
                $econtactName = $args['econtact-name'];
            }
            else {
                $econtactName = '';
            }
            // Collect and validate econtact phone if provided, otherwise set it to the empty string
            if (isset($args['econtact-phone']) && !empty($args['econtact-phone'])) {
                $econtactPhone = validateAndFilterPhoneNumber($args['econtact-phone']);
                if (!$econtactPhone) {
                    $errors = true;
                    echo 'bad econtact phone';
                }
            }
            else {
                $econtactPhone = '';
            }
            // Collect econtact relation if provided, otherwise set it to the empty string
            if (isset($args['econtact-relation']) && !empty($args['econtact-relation'])) {
                $econtactRelation = $args['econtact-relation'];
            }
            else {
                $econtactRelation = '';
            }

            /* Login Credentials Section Data */
            // May want to enforce password requirements at this step
            $email = strtolower($args['email']);
            $email = validateEmail($email);
            if (!$email) {
                $errors = true;
                echo 'bad email';
            }
            $password = password_hash($args['password'], PASSWORD_BCRYPT);

            if ($errors) {
                echo '<p>Your form submission contained unexpected input.</p>';
                die();
            }
            /* The new board member form (boardMemberRegistrationForm.php) does not collect every possible data field
                relating to a person. Entries not collected by the form are entered either as null (if the database allows null)
                or they are entered as '' (if the database does not allow null) 
                
                Board Members are created as volunteers. They must be later changed to boardmember by superAdmin 
            */
            $newperson = new Person(
                $first, $last, 'portland', 
                $address, $city, $state, $zipcode, "", 
                $phone, $phoneType, $phone2, $phone2Type,
                $email, $shirtSize, false, false, true, 
                $econtactName, $econtactPhone, $econtactRelation, 
                $contactWhen, 'volunteer', 'Active', $contactMethod, 
                null, null, null, null, '', null, '', '', '', 
                $dateOfBirth, $startDate, null, null, $password,
                null, null, null, null,
                null, null, null, null,
                null, null, null, null,
                null, null, 0, $gender, $prefix,
                $mailingAddress, $mailingCity, $mailingState, $mailingZip,
                $affiliatedOrg, $titleAtAffiliatedOrg
            );
            $result = add_person($newperson);
            if (!$result) {
                echo '<p>That e-mail address is already in use.</p>';
            } else {
                /* Send a message to the superadmin to notify them of the new registration 
                THIS MUST BE CHANGED, IT IS SENDING TO A TEST PROFILE FOR TESTING.
                THE RECIPIENTID SHOULD BE:
                    veronica@gwynethsgift.org 
                */
                $senderId = 'vmsroot@gmail.com';
                $recipientId = 'fake@fake.com';
                $title = "New Board Member Registration: " . $first . ' ' . $last . "!";
                $message = $first . " " . $last . " has registered as a new board member. " . 
                            "Please go to their profile and change their role status "
                            . " from volunteer to boardmember to approve their registration.";
                send_message($senderId, $recipientId, $title, $message);
                if ($loggedIn) {
                    echo '<script>document.location = "index.php?registerSuccess=board";</script>';
                } else {
                    echo '<script>document.location = "login.php?registerSuccess=board";</script>';
                }
                //insert logic to forward registration verification to administrator
            }
        } else {
            require_once('boardMemberRegistrationForm.php'); 
        }
    ?>
</body>
<script src="js/autofillSameAddress.js"></script>
</html>
