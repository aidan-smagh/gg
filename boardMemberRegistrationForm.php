<?php
$times = [
    '12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM',
    '6:00 AM', '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM',
    '12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM',
    '6:00 PM', '7:00 PM', '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM',
    '11:59 PM'
];
$values = [
    "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", 
    "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", 
    "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", 
    "18:00", "19:00", "20:00", "21:00", "22:00", "23:00",
    "23:59"
];

function buildSelect($name, $disabled=false, $selected=null) {
    global $times;
    global $values;
    if ($disabled) {
        $select = '
            <select id="' . $name . '" name="' . $name . '" disabled>';
    } else {
        $select = '
            <select id="' . $name . '" name="' . $name . '">';
    }
    if (!$selected) {
        $select .= '<option disabled selected value>Select a time</option>';
    }
    $n = count($times);
    for ($i = 0; $i < $n; $i++) {
        $value = $values[$i];
        if ($selected == $value) {
            $select .= '
                <option value="' . $values[$i] . '" selected>' . $times[$i] . '</option>';
        } else {
            $select .= '
                <option value="' . $values[$i] . '">' . $times[$i] . '</option>';
        }
    }
    $select .= '</select>';
    return $select;
}

/* 
Board member form information
    Personal information section, add prefix 
      * First name
      * Last name
        Prefix (Mr., Mrs., Dr. etc.)
        Gender
      * DOB
        Shirt size (dropdown box)
    Home address
        Street address (home)
        City (home)
        State (home)
        Zip code (home)
    Mailing address
        Street address (mailing)
        City (mailing)
        State (mailing)
        Zip code (mailing)
    New Section: Affiliation
        Organization affiliated with
        Title at affiliated organization
    Contact information section (remains the same, but add another entry for phone 2)
      * E-mail
      * Phone number 1
        Phone 1 type (cell home work)
        Phone number 2
        Phone 2 type (cell home work)
        Best time to reach you
        Preferred contact method (phone call, text, email)
    Emergency contact section (remains the same)
        Contact name
        Contact phone number
        Contact relation to you
    Volunteer Information Section (only retains start date)
      * I will be available to volunteer starting
    Login Credentials section remains the same


Board member fields in form:
    first name
    last name
    prefix (Mr., Mrs. Dr. etc.)
    steeet address
    city
    state

*/
?>

<h1>New Board Member Registration</h1>
<main class="signup-form">
    <form class="signup-form" method="post">
        <h2>Registration Form</h2>
        <p>Please fill out each section of the following form if you would like to register as a Board Member with Gwyneth's Gift</p>
        <p>An asterisk (<label><em>*</em></label>) indicates a required field.</p>
        <fieldset>
            <legend>Personal Information</legend>
            <p>The following information will help us identify you within our system.</p>
            <label for="first-name"><em>* </em>First Name</label>
            <input type="text" id="first-name" name="first-name" required placeholder="Enter your first name">

            <label for="last-name"><em>* </em>Last Name</label>
            <input type="text" id="last-name" name="last-name" required placeholder="Enter your last name">

            <label for="prefix"><em> </em>Prefix</label>
            <input type="text" id="prefix" name="prefix" placeholder="Mr, Mrs, Dr, etc...">

            <label for="gender"><em> </em>Gender</label>
            <select id="gender" name="gender" >
                <option value="">Choose an option</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="birthdate"><em>* </em>Date of Birth</label>
            <input type="date" id="birthdate" name="birthdate" required placeholder="Choose your birthday" max="<?php echo date('Y-m-d'); ?>">

            <label for="shirt-size"><em> </em>T-shirt Size</label>
            <select id="shirt-size" name="shirt-size" >
                <option value="S">Small</option>
                <option value="M">Medium</option>
                <option value="L">Large</option>
                <option value="XL">Extra Large</option>
                <option value="XXL">2X Large</option>
            </select>

            <label for="start-date"><em>* </em>I will be available to join Gwyneth's Gift starting</label>
            <input type="date" id="start-date" name="start-date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
        </fieldset>

        <fieldset>
            <legend>Home Address</legend>

            <label for="address"><em>* </em>Street Address</label>
            <input type="text" id="address" name="address" required placeholder="Enter your home street address">

            <label for="city"><em>* </em>City</label>
            <input type="text" id="city" name="city" required placeholder="Enter your home city">

            <label for="state"><em>* </em>State</label>
            
            <select id="state" name="state" required>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District Of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA" selected>Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select>

            <label for="zip"><em>* </em>Zip Code</label>
            <input type="text" id="zip" name="zip" pattern="[0-9]{5}" title="5-digit zip code" required placeholder="Enter your 5-digit home zip code">
        
        </fieldset>
        
        <fieldset>
            <legend>Mailing Address</legend>
            <label for="address"><em>* </em>Street Address</label>
            <input type="text" id="mailingAddress" name="mailingAddress" required placeholder="Enter your mailing street address">

            <label for="city"><em>* </em>City</label>
            <input type="text" id="mailingCity" name="mailingCity" required placeholder="Enter your mailing city">

            <label for="state"><em>* </em>State</label>
            <select id="mailingState" name="mailingState" required>
                <option value="AL">Alabama</option>
                <option value="AK">Alaska</option>
                <option value="AZ">Arizona</option>
                <option value="AR">Arkansas</option>
                <option value="CA">California</option>
                <option value="CO">Colorado</option>
                <option value="CT">Connecticut</option>
                <option value="DE">Delaware</option>
                <option value="DC">District Of Columbia</option>
                <option value="FL">Florida</option>
                <option value="GA">Georgia</option>
                <option value="HI">Hawaii</option>
                <option value="ID">Idaho</option>
                <option value="IL">Illinois</option>
                <option value="IN">Indiana</option>
                <option value="IA">Iowa</option>
                <option value="KS">Kansas</option>
                <option value="KY">Kentucky</option>
                <option value="LA">Louisiana</option>
                <option value="ME">Maine</option>
                <option value="MD">Maryland</option>
                <option value="MA">Massachusetts</option>
                <option value="MI">Michigan</option>
                <option value="MN">Minnesota</option>
                <option value="MS">Mississippi</option>
                <option value="MO">Missouri</option>
                <option value="MT">Montana</option>
                <option value="NE">Nebraska</option>
                <option value="NV">Nevada</option>
                <option value="NH">New Hampshire</option>
                <option value="NJ">New Jersey</option>
                <option value="NM">New Mexico</option>
                <option value="NY">New York</option>
                <option value="NC">North Carolina</option>
                <option value="ND">North Dakota</option>
                <option value="OH">Ohio</option>
                <option value="OK">Oklahoma</option>
                <option value="OR">Oregon</option>
                <option value="PA">Pennsylvania</option>
                <option value="RI">Rhode Island</option>
                <option value="SC">South Carolina</option>
                <option value="SD">South Dakota</option>
                <option value="TN">Tennessee</option>
                <option value="TX">Texas</option>
                <option value="UT">Utah</option>
                <option value="VT">Vermont</option>
                <option value="VA" selected>Virginia</option>
                <option value="WA">Washington</option>
                <option value="WV">West Virginia</option>
                <option value="WI">Wisconsin</option>
                <option value="WY">Wyoming</option>
            </select>

            <label for="zip"><em>* </em>Zip Code</label>
            <input type="text" id="mailingZip" name="mailingZip" pattern="[0-9]{5}" title="5-digit mailing zip code" required placeholder="Enter your 5-digit mailing zip code">
        
        </fieldset>

        <fieldset>
            <legend>Affiliation With Outside Organization</legend>
            <p>The following information relates to an outside organization you may be affiliated with.</p>
            <label for="affiliation"><em> </em>Organization Affiliated With</label>
            <input type="text" id="affiliationOrg" name="affiliationOrg" placeholder="Enter the organization name">

            <label for="affiliationTitle"><em> </em>Title at Affiliated Organization</label>
            <input type="text" id="affiliationTitle" name="affiliationTitle" placeholder="Enter your title at your affiliated organization">

        </fieldset>

        <fieldset>
            <legend>Contact Information</legend>
            <p>The following information will help us determine the best way to contact you regarding event coordination.</p>
            
            <label for="email"><em>* </em>E-mail</label>
            <p>This will also serve as your username when logging in.</p>
            <input type="email" id="email" name="email" required placeholder="Enter your e-mail address">

            <label for="phone"><em>* </em>Primary Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required placeholder="Ex. (555) 555-5555">

            <label><em> </em>Primary Phone Type</label>
            <div class="radio-group">
                <input type="radio" id="phone-type-cellphone" name="phone-type" value="cellphone" ><label for="phone-type-cellphone">Cell</label>
                <input type="radio" id="phone-type-home" name="phone-type" value="home" ><label for="phone-type-home">Home</label>
                <input type="radio" id="phone-type-work" name="phone-type" value="work" ><label for="phone-type-work">Work</label>
            </div>

            <label for="phone2"><em> </em>Secondary Phone Number</label>
            <input type="tel" id="phone2" name="phone2" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" placeholder="Ex. (555) 555-5555">

            <label><em> </em>Secondary Phone Type</label>
            <div class="radio-group">
                <input type="radio" id="phone2-type-cellphone" name="phone2-type" value="cellphone2"><label for="phone2-type-cellphone">Cell</label>
                <input type="radio" id="phone2-type-home" name="phone2-type" value="home2"><label for="phone2-type-home">Home</label>
                <input type="radio" id="phone2-type-work" name="phone2-type" value="work2"><label for="phone2-type-work">Work</label>
            </div>

            <label for="contact-when"><em> </em>Best Time to Reach You</label>
            <input type="text" id="contact-when" name="contact-when"  placeholder="Ex. Evenings, Days">

            <label><em> </em>Preferred Contact Method</label>
            <div class="radio-group">
                <input type="radio" id="method-phone" name="contact-method" value="phone" ><label for="method-phone">Phone call</label>
                <input type="radio" id="method-text" name="contact-method" value="text" ><label for="method-text">Text</label>
                <input type="radio" id="method-email" name="contact-method" value="email" ><label for="method-email">E-mail</label>
            </div>
        </fieldset>
        <fieldset>
            <legend>Emergency Contact</legend>
            <p>Please provide us with someone to contact on your behalf in case of an emergency.</p>
            <label for="econtact-name" ><em> </em>Contact Name</label>
            <input type="text" id="econtact-name" name="econtact-name"  placeholder="Enter emergency contact name">

            <label for="econtact-phone"><em> </em>Contact Phone Number</label>
            <input type="tel" id="econtact-phone" name="econtact-phone" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}"  placeholder="Enter emergency contact phone number. Ex. (555) 555-5555">

            <label for="econtact-name"><em> </em>Contact Relation to You</label>
            <input type="text" id="econtact-relation" name="econtact-relation"  placeholder="Ex. Spouse, Mother, Father, Sister, Brother, Friend">
        </fieldset>

        <fieldset>
            <legend>Login Credentials</legend>
            <p>You will use the following information to log in to the VMS.</p>

            <label for="email-relisting">E-mail Address</label>
            <span id="email-dupe" class="pseudo-input">Enter your e-mail address above</span>

            <label for="password"><em>* </em>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required>

            <label for="password-reenter"><em>* </em>Re-enter Password</label>
            <input type="password" id="password-reenter" name="password-reenter" placeholder="Re-enter password" required>
            <p id="password-match-error" class="error hidden">Passwords do not match!</p>
        </fieldset>
        <p>By pressing Submit below, you are requesting to join Gwyneth's Gift as a Board Member.</p>
        <p>Your request will be forwarded to the administrator for review.</p>
        <input type="submit" name="registration-form" value="Submit">
    </form>
    <?php if ($loggedIn): ?>
        <a class="button cancel" href="index.php" style="margin-top: .5rem">Cancel</a>
    <?php endif ?>
</main>