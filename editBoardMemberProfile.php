<?php
    // Description: Board Member Profile edit page
    session_cache_expire(30);
    session_start();
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    if (!isset($_SESSION['_id'])) {
        header('Location: login.php');
        die();
    }

    require_once('include/input-validation.php');
    require_once('domain/Person.php');
    require_once('database/dbPersons.php');

    $editing_self = false;
    // Determing whose profile is being edited 
    if ($_SESSION['access_level'] >= 2 && isset($_GET['id'])) {
        // admin is editing someone else's profile 
        $id = $_GET['id'];
    } else {
        // person is editing their own profile 
        $id = $_SESSION['_id'];
        $editing_self = true;
    }
    
    /* collect the person from the database */
    $person = retrieve_person($id);
    // ensure the person was properly collected and is a board member
    if (!$person || $person->get_type()[0] !== 'boardmember') {
        echo '<main class="signup-form"><p class="error-toast">That user does not exist, or is not a Board Member.</p></main></body></html>';
        die();
    } 

    // Process form submission 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["boardmember-edit-form"])) {
        $args = sanitize($_POST);
        $errors = false;

        // array of all required form fields
        $required = array(
            'first-name', 'last-name', 'birthdate', 'start-date', 'address', 'city', 'state', 'zip',
            'mailing-address', 'mailing-city', 'mailing-state', 'mailing-zip', 'email', 'phone'
        );
        // ensure all required fields were provided
        if (!wereRequiredFieldsSubmitted($args, $required, false)) {
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

        $result = update_board_member_profile($id,
        $first, $last, $prefix, $gender, $dateOfBirth, $shirtSize, $startDate, 
        $address, $city, $state, $zipcode, 
        $mailingAddress, $mailingCity, $mailingState, $mailingZip,
        $affiliatedOrg, $titleAtAffiliatedOrg, 
        $email, $phone, $phoneType, $phone2, $phone2Type, $contactWhen, $contactMethod, 
        $econtactName, $econtactPhone, $econtactRelation
        );
        if ($result) {
            if ($editingSelf) {
                header('Location: viewProfile.php?editSuccess');
            } else {
                header('Location: viewProfile.php?editSuccess&id='. $id);
            }
            die();
        }

    }
?>
<!DOCTYPE html>
<html>
<head>
    <?php require_once('universal.inc'); ?>
    <title>Gwyneth's Gift VMS | Manage Profile</title>
</head>
<body>
    <?php
        require_once('header.php');
        $isAdmin = $_SESSION['access_level'] >= 2;
        require_once('boardMemberProfileEditForm.php');
    ?>
</body>
</html>
