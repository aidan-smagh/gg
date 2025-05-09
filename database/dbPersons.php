<?php
/*
 * Copyright 2013 by Jerrick Hoang, Ivy Xing, Sam Roberts, James Cook, 
 * Johnny Coster, Judy Yang, Jackson Moniaga, Oliver Radwan, 
 * Maxwell Palmer, Nolan McNair, Taylor Talmage, and Allen Tucker. 
 * This program is part of RMH Homebase, which is free software.  It comes with 
 * absolutely no warranty. You can redistribute and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software Foundation
 * (see <http://www.gnu.org/licenses/ for more information).
 * 
 */

/**
 * @version March 1, 2012
 * @author Oliver Radwan and Allen Tucker
 */
include_once('dbinfo.php');
include_once(dirname(__FILE__).'/../domain/Person.php');

/*
 * add a person to dbPersons table: if already there, return false
 */

function add_person($person) {
    if (!$person instanceof Person)
        die("Error: add_person type mismatch");
    // connect to db
    $con=connect();
    // prepare sql safe query to see if person already exists
    $query = $con->prepare("SELECT id FROM dbPersons WHERE id = ?");
    $id = $person->get_id();
    $query->bind_param("s", $id);
    // execute query and store its result
    $query->execute();
    $query->store_result();
    // if the query return is empty, the person doesn't exist
    if ($query->num_rows === 0) {
        $insert = $con->prepare("INSERT INTO dbPersons (
            id, start_date, venue, first_name, last_name, address, city, state, zip,
            phone1, phone1type, phone2, phone2type, birthday, email, shirt_size,
            computer, camera, transportation, contact_name, contact_num, relation,
            contact_time, cMethod, position, credithours, howdidyouhear, commitment,
            motivation, specialties, convictions, type, status, availability, schedule,
            hours, notes, password, sundays_start, sundays_end,
            mondays_start, mondays_end, tuesdays_start,
            tuesdays_end, wednesdays_start, wednesdays_end,
            thursdays_start, thursdays_end, fridays_start,
            fridays_end, saturdays_start, saturdays_end,
            profile_pic, force_password_change, gender, prefix, mailing_address,
            mailing_city, mailing_state, mailing_zip, affiliated_org, title_at_affiliated_org
            ) values (?,?,?,?,?,?,?,?,?,?,
                      ?,?,?,?,?,?,?,?,?,?,
                      ?,?,?,?,?,?,?,?,?,?,
                      ?,?,?,?,?,?,?,?,?,?,
                      ?,?,?,?,?,?,?,?,?,?,
                      ?,?,?,?,?,?,?,?,?,?,
                      ?,?
        )");
            // if prepare statement failed, exit and return the error
            if (!$insert) {
                die("Prepare statement failed ". $con->error);
            }
            // collect all of the variables to use in the bind_param
            // id was already collected in previous query
            // broken into blocks of 10 for ease of counting
            $start_date = $person->get_start_date();
            $venue = $person->get_venue();
            $first_name = $person->get_first_name();
            $last_name = $person->get_last_name();
            $address = $person->get_address();
            $city = $person->get_city();
            $state = $person->get_state();
            $zip = $person->get_zip();
            $phone1 = $person->get_phone1();
            $phone1type = $person->get_phone1type();
            
            $phone2 = $person->get_phone2();
            $phone2type = $person->get_phone2type();
            $birthday = $person->get_birthday();
            $email = $person->get_email();
            $shirt_size = $person->get_shirt_size();
            $computer = $person->get_computer();
            $camera = $person->get_camera();
            $transportation = $person->get_transportation();
            $contact_name = $person->get_contact_name();
            $contact_num = $person->get_contact_num();
            
            $relation = $person->get_relation();
            $contact_time = $person->get_contact_time();
            $cMethod = $person->get_cMethod();
            $position = $person->get_position();
            $credithours = $person->get_credithours();
            $howdidyouhear = $person->get_howdidyouhear();
            $commitment = $person->get_commitment();
            $motivation = $person->get_motivation();
            $specialties = $person->get_specialties();
            $convictions = $person->get_convictions();
            
            $type = implode(',', $person->get_type());
            $status = $person->get_status();
            $availability = implode(',', $person->get_availability());
            $schedule = implode(',', $person->get_schedule());
            $hours = implode(',', $person->get_hours());
            $notes = $person->get_notes();
            $password = $person->get_password();
            $sundays_start = $person->get_sunday_availability_start();
            $sundays_end = $person->get_sunday_availability_end();
            $mondays_start = $person->get_monday_availability_start();
            
            $mondays_end = $person->get_monday_availability_end();
            $tuesdays_start = $person->get_tuesday_availability_start();
            $tuesdays_end = $person->get_tuesday_availability_end();
            $wednesdays_start = $person->get_wednesday_availability_start();
            $wednesdays_end = $person->get_wednesday_availability_end();
            $thursdays_start = $person->get_thursday_availability_start();
            $thursdays_end = $person->get_thursday_availability_end();
            $fridays_start = $person->get_friday_availability_start();
            $fridays_end = $person->get_friday_availability_end();
            $saturdays_start = $person->get_saturday_availability_start();
            
            $saturdays_end = $person->get_saturday_availability_end();
            $profile_pic = $person->get_profile_pic();
            $force_password_change = $person->is_password_change_required();
            $gender = $person->get_gender();
            $prefix = $person->get_prefix();
            $mailing_address = $person->get_mailing_address();
            $mailing_city = $person->get_mailing_city();
            $mailing_state = $person->get_mailing_state();
            $mailing_zip = $person->get_mailing_zip();
            $affiliated_org = $person->get_affiliated_org();
            
            $title_at_affiliated_org = $person->get_title_at_affiliated_org();

            // there are 62 fields, #1 - #53 are strings, #54 is int, the rest are strings
            $insert->bind_param("sssssssssssssssssssssssssssssssssssssssssssssssssssssissssssss",
            $id, $start_date, $venue, $first_name, $last_name, $address, $city, $state, $zip, $phone1, 
            $phone1type, $phone2, $phone2type, $birthday, $email, $shirt_size, $computer, $camera, $transportation, $contact_name,
            $contact_num, $relation, $contact_time, $cMethod, $position, $credithours, $howdidyouhear, $commitment, $motivation, $specialties,
            $convictions, $type, $status, $availability, $schedule, $hours, $notes, $password, $sundays_start, $sundays_end,
            $mondays_start, $mondays_end, $tuesdays_start, $tuesdays_end, $wednesdays_start, $wednesdays_end, $thursdays_start, $thursdays_end, $fridays_start, $fridays_end, 
            $saturdays_start, $saturdays_end, $profile_pic, $force_password_change, $gender, $prefix, $mailing_address, $mailing_city, $mailing_state, 
            $mailing_zip, $affiliated_org, $title_at_affiliated_org
        );

        // execute the insert statement, see if it worked, and return error if it didnt
        if (!$insert->execute()) {
            die("Insert execution failed " . $insert->error);
        }

        // success. close the insertion statement and the db connection
        $insert->close();
        $con->close();
        return true;
    }
    //failure. close the query and db connection
    $query->close();
    $con->close();
    return false;
}

/*
 * remove a person from dbPersons table.  If already there, return false
 * NOT CURRENTLY USED ANYWHERE 4/4/2025
 */
function remove_person($id) {
    // connect to db
    $con=connect();
    // prepare sql safe query to see if person exists
    $query = $con->prepare("SELECT 1 FROM dbPersons WHERE id = ?");
    // if prepare statement failed, exit and return the error
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind the id parameter and execute the query
    $query->bind_param("s", $id);
    $query->execute();
    $query->store_result();

    // if the result is empty, person doesn't exist
    if ($query->num_rows === 0) {
        $query->close();
        $con->close();
        return false;
    }
    // query is done so close it
    $query->close();

    // create sql safe person deletion statement
    $stmt = $con->prepare("DELETE FROM dbPersons WHERE id = ?");
    // if prepare statement failed, exit and return the error
    if (!$stmt) {
        die("Prepare statement failed: " . $con->error);
    }

    // bind the id parameter to the deletion statement
    $stmt->bind_param("s", $id);
    // ecxecute deletion and store result
    $result = $stmt->execute();
    if (!$result) {
        die("Deletion execution failed: " . $con->error);
    }
    // done, close everything
    $stmt->close();
    $con->close();
    return $result;
}

/*
 * @return a Person from dbPersons table matching a particular id.
 * if not in table, return false
 */

function retrieve_person($id) {
    //connect to db
    $con=connect();
    //sql safe query to retrieve a person by id
    $query = $con->prepare("SELECT * FROM dbPersons WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }

    // bind parameters, execute query, store result
    $query->bind_param("s", $id);
    $query->execute();
    $result = $query->get_result();

    // if the result doesn't return 1 person's data, return false
    if ($result->num_rows !== 1) {
        $query->close();
        $con->close();
        return false;
    }

    // fetch the result row (person's data) 
    $result_row = $result->fetch_assoc();
    //convert the data to a person object
    $person = make_a_person($result_row);

    //close everything and return
    $query->close();
    $con->close();
    return $person;
}

// function to retrieve a person by their name
function retrieve_persons_by_name ($name) {
	$persons = array();
	if (!isset($name) || $name == "" || $name == null) return $persons;
	$con=connect();

    // separate name into first and last
    $separated_name = explode(" ", $name);
    $first_name = $separated_name[0];
    $last_name = $separated_name[1];

    // prepare sql safe query to retrieve persons by first and last name
    $query = $con->prepare("SELECT * FROM dbPersons WHERE first_name = ? AND last_name = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $first_name, $last_name);
    // execute query and store result
    $query->execute();
    $result = $query->get_result();

    // collect each matching result and convert to Person object
    while ($result_row = $result->fetch_assoc()) {
        //create the person
        $thePerson =make_a_person($result_row);
        //append the person to the $persons array
        $persons[] = $thePerson;
    }
}
/* allows users to change their own password */
function change_password($id, $newPass) {
    $con=connect();
    // prepare sql safe update query to change password
    $query = $con->prepare("UPDATE dbPersons SET password = ?, force_password_change='0' WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $newPass, $id);
    //execute query and store result
    $result = $query->execute();

    //close everything and return
    $query->close();
    $con->close();
    return $result;
}

/* Function allowing admins to reset a user's password.
   Gives a temporary password to the admin, who must give it to the user.
   When the user logins in with the generated temp password, they are forced to change it. */
function reset_password($id, $newPass) {
    //62698030
    $con=connect();
    //prepare sql safe update query
    $query = $con->prepare("UPDATE dbPersons SET password = ?, force_password_change='1' WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $newPass, $id);
    // execute query and store result
    $result = $query->execute();
    //close everything and return
    $query->close();
    $con->close();
    return $result;
}

function update_hours($id, $new_hours) {
    $con=connect();
    $query = $con->prepare('UPDATE dbPersons SET hours = ? WHERE id = ?');
    $query->bind_param("is", $new_hours, $id);
    $result = $query->execute();
    $query->close();
    mysqli_close($con);
    return $result;
}

/* NOT CURRENTLY USED ANYWHERE 4/4/2025 */
function update_birthday($id, $new_birthday) {
	$con=connect();
    // prepare sql safe update query
    $query = $con->prepare("UPDATE dbPersons SET birthday = ? WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $new_birthday, $id);
    // execute query and store result
    $result = $query->execute();
    //close everything and return
    $query->close();
    $con->close();
    return $result;
}

/*
 * Updates the profile picture link of the corresponding
 * id.
*/
function update_profile_pic($id, $link) {
    $con = connect();
    // prepare sql safe update query
    $query = $con->prepare("UPDATE dbPersons SET profile_pic = ? WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $link, $id);
    // execute query and store result
    $result = $query->execute();
    //close everything and return
    $query->close();
    $con->close();
    return $result;
}

/*
 * Returns the age of the person by subtracting the 
 * person's birthday from the current date
*/
function get_age($birthday) {

  $today = date("Ymd");
  // If month-day is before the person's birthday,
  // subtract 1 from current year - birth year
  $age = date_diff(date_create($birthday), date_create($today))->format('%y');

  return $age;
}
/* NOT CURRENTLY USED ANYWHERE 4/4/2025 */
function update_start_date($id, $new_start_date) {
	$con=connect();
    //prepare sql safe update query
    $query = $con->prepare("UPDATE dbPersons SET start_date = ? WHERE id = ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to prepared query
    $query->bind_param("ss", $new_start_date, $id);
    // execute query and store result
    $result = $query->execute();
    // close everything and return result
    $query->close();
    $con->close();
    return $result;
}

/*
 * @return all rows from dbPersons table ordered by last name
 * if none there, return false
 * 
 * WAITING TO MAKE SQL SAFE UNTIL SOMONE TELLS ME THEYRE DONE WITH IT
 */

function getall_dbPersons($name_from, $name_to, $venue) {
    $con=connect();
    $query = "SELECT * FROM dbPersons";
    $query.= " WHERE venue = '" .$venue. "'"; 
    $query.= " AND last_name BETWEEN '" .$name_from. "' AND '" .$name_to. "'"; 
    $query.= " ORDER BY last_name,first_name";
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return false;
    }
    $result = mysqli_query($con,$query);
    $thePersons = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $thePerson = make_a_person($result_row);
        $thePersons[] = $thePerson;
    }

    return $thePersons;
}

/*
  @return all rows from dbPersons
  NOT CURRENTLY USED ANYWHERE 4/4/2025
  NOT SQL SAFE!!!!!!!!!!!
*/
function getall_volunteers() {
    $con=connect();
    $query = 'SELECT * FROM dbPersons WHERE id != "vmsroot"';
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return false;
    }
    $result = mysqli_query($con,$query);
    $thePersons = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $thePerson = make_a_person($result_row);
        $thePersons[] = $thePerson;
    }

    return $thePersons;
}

/* NOT CURRENTLY USED ANYWHERE 4/4/2025
   NOT SQL SAFE!!!!!!!!!!!!!!!!! */
function getall_volunteer_names() {
	$con=connect();
    $type = "volunteer";
	$query = "SELECT first_name, last_name FROM dbPersons WHERE type LIKE '%" . $type . "%' ";
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return false;
    }
    $result = mysqli_query($con,$query);
    $names = array();
    while ($result_row = mysqli_fetch_assoc($result)) {
        $names[] = $result_row['first_name'].' '.$result_row['last_name'];
    }
    mysqli_close($con);
    return $names;   	
}

function make_a_person($result_row) {
	/*
	 ($f, $l, $v, $a, $c, $s, $z, $p1, $p1t, $p2, $p2t, $e, $ts, $comp, $cam, $tran, $cn, $cpn, $rel,
			$ct, $t, $st, $cntm, $pos, $credithours, $comm, $mot, $spe,
			$convictions, $av, $sch, $hrs, $bd, $sd, $hdyh, $notes, $pass)
	 */
    $thePerson = new Person(
                    $result_row['first_name'],
                    $result_row['last_name'],
                    $result_row['venue'],
                    $result_row['address'],
                    $result_row['city'],
                    $result_row['state'],
                    $result_row['zip'],
                    @$result_row['profile_pic'],
                    $result_row['phone1'],
                    $result_row['phone1type'],
                    $result_row['phone2'],
                    $result_row['phone2type'],
                    $result_row['email'],
                    $result_row['shirt_size'],
                    $result_row['computer'],
                    $result_row['camera'],
                    $result_row['transportation'],
                    $result_row['contact_name'],
                    $result_row['contact_num'],
                    $result_row['relation'],
                    $result_row['contact_time'],
                    $result_row['type'],
                    $result_row['status'],
                    $result_row['cMethod'],  
                    $result_row['position'],
                    $result_row['hours'],
                    $result_row['commitment'],
                    $result_row['motivation'],
                    $result_row['specialties'],
                    $result_row['convictions'],
                    $result_row['availability'],
                    $result_row['schedule'],
                    $result_row['hours'],
                    $result_row['birthday'],
                    $result_row['start_date'],
                    $result_row['howdidyouhear'],
                    $result_row['notes'],
                    $result_row['password'],
                    $result_row['sundays_start'],
                    $result_row['sundays_end'],
                    $result_row['mondays_start'],
                    $result_row['mondays_end'],
                    $result_row['tuesdays_start'],
                    $result_row['tuesdays_end'],
                    $result_row['wednesdays_start'],
                    $result_row['wednesdays_end'],
                    $result_row['thursdays_start'],
                    $result_row['thursdays_end'],
                    $result_row['fridays_start'],
                    $result_row['fridays_end'],
                    $result_row['saturdays_start'],
                    $result_row['saturdays_end'],
                    $result_row['force_password_change'],
                    $result_row['gender'],
                    $result_row['prefix'],
                    $result_row['mailing_address'],
                    $result_row['mailing_city'],
                    $result_row['mailing_state'],
                    $result_row['mailing_zip'],
                    $result_row['affiliated_org'],
                    $result_row['title_at_affiliated_org']
                );   
    return $thePerson;
}
/* NOT CURRENTLY USED ANYWHERE 4/4/2025
   NOT SQL SAFE!!!!!!!!!!!! */
function getall_names($status, $type, $venue) {
    $con=connect();
    $result = mysqli_query($con,"SELECT id,first_name,last_name,type FROM dbPersons " .
            "WHERE venue='".$venue."' AND status = '" . $status . "' AND TYPE LIKE '%" . $type . "%' ORDER BY last_name,first_name");
    mysqli_close($con);
    return $result;
}

/*
 * @return all active people of type $t or subs from dbPersons table ordered by last name
 * NOT CURRENTLY USED ANYWHERE 4/4/2025
 * NOT SQL SAFE!!!!!!!!!!!!
 */

function getall_type($t) {
    $con=connect();
    $query = "SELECT * FROM dbPersons WHERE (type LIKE '%" . $t . "%' OR type LIKE '%sub%') AND status = 'active'  ORDER BY last_name,first_name";
    $result = mysqli_query($con,$query);
    if ($result == null || mysqli_num_rows($result) == 0) {
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return $result;
}

/*
 *   get all active volunteers and subs of $type who are available for the given $frequency,$week,$day,and $shift
 *   Not currently used anywhere 4/4/2025
 *   NOT SQL SAFE!!!!!!!!!!!!!
 */

function getall_available($type, $day, $shift, $venue) {
    $con=connect();
    $query = "SELECT * FROM dbPersons WHERE (type LIKE '%" . $type . "%' OR type LIKE '%sub%')" .
            " AND availability LIKE '%" . $day .":". $shift .
            "%' AND status = 'active' AND venue = '" . $venue . "' ORDER BY last_name,first_name";
    $result = mysqli_query($con,$query);
    mysqli_close($con);
    return $result;
}

function getvolunteers_byevent($id){
	$con = connect();
    // make sql safe query
    //POSSIBLE BUG: Should the WHERE clause here be a ON clause?
    $query = $con->prepare("SELECT * FROM dbEventVolunteers JOIN dbPersons WHERE eventId = ? 
                            AND dbEventVolunteers.userId = dbPersons.id");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameter to prepared query
    $query->bind_param("s", $id);
    //execute query and store result, which should be rows of data
    $query->execute();
    $result = $query->get_result();
    // array to store the persons whose data are stored in $result_rows
    $persons = array();
    // collect each matching result and convert to Person object
    while ($result_rows = $result->fetch_assoc()) {
        $person = make_a_person($result_rows);
        $persons[] = $person;
    }
    // close the query and connection
    $query->close();
    $con->close();
    return $persons;
}


/*  retrieve only those persons that match the criteria given in the arguments
    Not currently used anywhere 4/4/2025

    NOT SQL SAFE!!!!!!!!!!!!!
*/
function getonlythose_dbPersons($type, $status, $name, $day, $shift, $venue) {
   $con=connect();
   $query = "SELECT * FROM dbPersons WHERE type LIKE '%" . $type . "%'" .
           " AND status LIKE '%" . $status . "%'" .
           " AND (first_name LIKE '%" . $name . "%' OR last_name LIKE '%" . $name . "%')" .
           " AND availability LIKE '%" . $day . "%'" . 
           " AND availability LIKE '%" . $shift . "%'" . 
           " AND venue = '" . $venue . "'" . 
           " ORDER BY last_name,first_name";
   $result = mysqli_query($con,$query);
   $thePersons = array();
   while ($result_row = mysqli_fetch_assoc($result)) {
       $thePerson = make_a_person($result_row);
       $thePersons[] = $thePerson;
   }
   mysqli_close($con);
   return $thePersons;
}

function phone_edit($phone) {
    if ($phone!="")
		return substr($phone, 0, 3) . "-" . substr($phone, 3, 3) . "-" . substr($phone, 6);
	else return "";
}
/* NOT SQL SAFE !!!!!!!!!!!!!!! */
function get_people_for_export($attr, $first_name, $last_name, $type, $status, $start_date, $city, $zip, $phone, $email) {
	$first_name = "'".$first_name."'";
	$last_name = "'".$last_name."'";
	$status = "'".$status."'";
	$start_date = "'".$start_date."'";
	$city = "'".$city."'";
	$zip = "'".$zip."'";
	$phone = "'".$phone."'";
	$email = "'".$email."'";
	$select_all_query = "'.'";
	if ($start_date == $select_all_query) $start_date = $start_date." or start_date=''";
	if ($email == $select_all_query) $email = $email." or email=''";
    
	$type_query = "";
    if (!isset($type) || count($type) == 0) $type_query = "'.'";
    else {
    	$type_query = implode("|", $type);
    	$type_query = "'.*($type_query).*'";
    }
    
    error_log("query for start date is ". $start_date);
    error_log("query for type is ". $type_query);
    
   	$con=connect();
    $query = "SELECT ". $attr ." FROM dbPersons WHERE 
    			first_name REGEXP ". $first_name . 
    			" and last_name REGEXP ". $last_name . 
    			" and (type REGEXP ". $type_query .")". 
    			" and status REGEXP ". $status . 
    			" and (start_date REGEXP ". $start_date . ")" .
    			" and city REGEXP ". $city .
    			" and zip REGEXP ". $zip .
    			" and (phone1 REGEXP ". $phone ." or phone2 REGEXP ". $phone . " )" .
    			" and (email REGEXP ". $email .") ORDER BY last_name, first_name";
	error_log("Querying database for exporting");
	error_log("query = " .$query);
    $result = mysqli_query($con,$query);
    return $result;

}

/*  return an array of "last_name:first_name:birth_date", and sorted by month and day
    Not currently used anywhere 4/4/2025

    NOT SQL SAFE!!!!!!!!!!!!!
*/
function get_birthdays($name_from, $name_to, $venue) {
	$con=connect();
   	$query = "SELECT * FROM dbPersons WHERE availability LIKE '%" . $venue . "%'" . 
   	$query = " AND last_name BETWEEN '" .$name_from. "' AND '" .$name_to. "'";
    $query.= " ORDER BY birthday";
	$result = mysqli_query($con,$query);
	$thePersons = array();
	while ($result_row = mysqli_fetch_assoc($result)) {
    	$thePerson = make_a_person($result_row);
        $thePersons[] = $thePerson;
	}
   	mysqli_close($con);
   	return $thePersons;
}

/* return an array of "last_name;first_name;hours", which is "last_name;first_name;date:start_time-end_time:venue:totalhours"
   and sorted alphabetically

   Not currently used anywhere 4/4/2025
   NOT SQL SAFE!!!!!!!!!!!!!
*/
function get_logged_hours($from, $to, $name_from, $name_to, $venue) {
	$con=connect();
   	$query = "SELECT first_name,last_name,hours,venue FROM dbPersons "; 
   	$query.= " WHERE venue = '" .$venue. "'";
   	$query.= " AND last_name BETWEEN '" .$name_from. "' AND '" .$name_to. "'";
   	$query.= " ORDER BY last_name,first_name";
	$result = mysqli_query($con,$query);
	$thePersons = array();
	while ($result_row = mysqli_fetch_assoc($result)) {
		if ($result_row['hours']!="") {
			$shifts = explode(',',$result_row['hours']);
			$goodshifts = array();
			foreach ($shifts as $shift) 
			    if (($from == "" || substr($shift,0,8) >= $from) && ($to =="" || substr($shift,0,8) <= $to))
			    	$goodshifts[] = $shift;
			if (count($goodshifts)>0) {
				$newshifts = implode(",",$goodshifts);
				array_push($thePersons,$result_row['last_name'].";".$result_row['first_name'].";".$newshifts);
			}   // we've just selected those shifts that follow within a date range for the given venue
		}
	}
   	mysqli_close($con);
   	return $thePersons;
}
/*  Function to update a person (not a board member) profile
*/
function update_person_profile(
    $id,
    $first, $last, $dateOfBirth, $address, $city, $state, $zipcode,
    $email, $phone, $phoneType, $contactWhen, $contactMethod, 
    $econtactName, $econtactPhone, $econtactRelation,
    $skills, $hasComputer, $hasCamera, $hasTransportation, $shirtSize,
    $sundaysStart, $sundaysEnd, $mondaysStart, $mondaysEnd,
    $tuesdaysStart, $tuesdaysEnd, $wednesdaysStart, $wednesdaysEnd,
    $thursdaysStart, $thursdaysEnd, $fridaysStart, $fridaysEnd,
    $saturdaysStart, $saturdaysEnd, $gender
) {
    $con = connect();
    // prepare sql safe update query
    // 36 parameters given
    $query = $con->prepare("UPDATE dbPersons SET
        first_name= ?, last_name= ?, birthday= ?, address= ?, city= ?, state = ?, zip= ?,
        email= ?, phone1= ?, phone1type= ?, contact_time= ?, cMethod= ?,
        contact_name= ?, contact_num= ?, relation= ?,
        specialties= ?, computer= ?, camera= ?, transportation= ?, shirt_size= ?,
        sundays_start= ?, sundays_end= ?, mondays_start= ?, mondays_end= ?,
        tuesdays_start= ?, tuesdays_end= ?, wednesdays_start= ?, wednesdays_end= ?,
        thursdays_start= ?, thursdays_end= ?, fridays_start= ?, fridays_end= ?,
        saturdays_start= ?, saturdays_end= ?, gender= ?
        WHERE id= ?");
    if (!$query) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind parameters to the update statement
    $query->bind_param("ssssssssssssssssssssssssssssssssssss", 
        $first, $last, $dateOfBirth, $address, $city, $state, $zipcode,
        $email, $phone, $phoneType, $contactWhen, $contactMethod,
        $econtactName, $econtactPhone, $econtactRelation,
        $skills, $hasComputer, $hasCamera, $hasTransportation, $shirtSize,
        $sundaysStart, $sundaysEnd, 
        $mondaysStart, $mondaysEnd,
        $tuesdaysStart, $tuesdaysEnd,
        $wednesdaysStart, $wednesdaysEnd,
        $thursdaysStart, $thursdaysEnd,
        $fridaysStart, $fridaysEnd,
        $saturdaysStart, $saturdaysEnd,
        $gender,
        $id
    );
    // execute the update statement and store the result
    $result = $query->execute();
    // close everything and return result
    $query->close();
    $con->close();
    return $result;
}

/* function to update an existing board member profile */
function update_board_member_profile(
    $id,
    $first, $last, $prefix, $gender, $dateOfBirth, $shirtSize, $startDate, 
    $address, $city, $state, $zipcode, 
    $mailingAddress, $mailingCity, $mailingState, $mailingZip,
    $affiliatedOrg, $titleAtAffiliatedOrg, 
    $email, $phone, $phoneType, $phone2, $phone2Type, $contactWhen, $contactMethod, 
    $econtactName, $econtactPhone, $econtactRelation
) {
    // build sql safe update query
    $query = "update dbPersons set 
        first_name= ?, last_name= ?, prefix= ?, gender= ?, birthday= ?, shirt_size= ?, start_date= ?,
        address= ?, city= ?, state= ?, zip= ?, 
        mailing_address= ?, mailing_city= ?, mailing_state= ?, mailing_zip= ?,
        affiliated_org= ?, title_at_affiliated_org= ?, 
        email= ?, phone1= ?, phone1type= ?, phone2= ?, phone2type= ?, contact_time= ?, cMethod= ?,
        contact_name= ?, contact_num= ?, relation= ?
        where id= ?";

    // connect to db
    $con = connect();
    // prepare the query to fill with parameters
    $stmt = mysqli_prepare($con, $query);
    // if $stmt is false, prepare statement failed, so return any errors
    if (!$stmt) {
        die("Prepare statement failed: " . mysqli_error($con));
    }
    // bind all parameters to the update statement
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssssss", 
        $first, $last, $prefix, $gender, $dateOfBirth, $shirtSize, $startDate,
        $address, $city, $state, $zipcode,
        $mailingAddress, $mailingCity, $mailingState, $mailingZip,
        $affiliatedOrg, $titleAtAffiliatedOrg,
        $email, $phone, $phoneType, $phone2, $phone2Type, 
        $contactWhen, $contactMethod,
        $econtactName, $econtactPhone, $econtactRelation,
        $id
    );
    // execute the update statement and store the result
    $result = mysqli_stmt_execute($stmt);
    // close the statement and the db connection
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    return $result;
}


/**
 * Searches the database and returns an array of all volunteers
 * that are eligible to attend the given event that have not yet
 * signed up/been assigned to the event.
 * 
 * Eligibility criteria: availability falls within event start/end time
 * and start date falls before or on the volunteer's start date.
 */
function get_unassigned_available_volunteers($eventID) {
    $con = connect();
    // prepare sql safe query to check if event exists
    $eventQuery = $con->prepare("SELECT * FROM dbEvents WHERE id = ?");
    if (!$eventQuery) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind eventId parameter
    $eventQuery->bind_param("i", $eventID);
    // execute query and store result
    $eventQuery->execute();
    $result = $eventQuery->get_result();
    // if there are no events with provided id, return null
    if (!$result) {
        $eventQuery->close();
        $con->close();
        return null;
    }
    // fetch the event row data
    $event = $result->fetch_assoc();

    // collect the event's time and data data for comparison with person availability
    $event_start = $event['startTime'];
    // correction made below. was $event_end = $event['startTime'}]
    $event_end = $event['endTime'];
    $date = $event['date'];
    $dateInt = strtotime($date);
    $dayofweek = strtolower(date('l', $dateInt));
    $dayname_start = $dayofweek . 's_start';
    $dayname_end = $dayofweek . 's_end';

    // build sql safe query for volunteers who are available
    // only injection risk is from $eventID
    $personQuery = $con->prepare("SELECT * FROM dbPersons WHERE
        $dayname_start<='$event_start' and $dayname_end>='$event_end'
        AND start_date<='$date'
        AND id != 'vmsroot' 
        AND status='Active'
        AND id not in (select userID from dbEventVolunteers where eventID= ?)
        ORDER BY last_name, first_name"
    );
    if (!$personQuery) {
        die("Prepare statement failed: " . $con->error);
    }
    // bind the eventID parameter to the prepared query
    $personQuery->bind_param("i", $eventID);
    // execute the query and store the result
    $personQuery->execute();
    $result = $personQuery->get_result();
    // if no volunteers available, close everything and return null
    if (!$result || $result->num_rows === 0) {
        $eventQuery->close();
        $personQuery->close();
        $con->close();
        return null;
    }

    // array to store the available volunteers
    $availablePersons = array();
    // loop through the $result persons data and create person objects
    while ($result_row = $result->fetch_assoc()) {
        // create a person object from the result row
        $person = make_a_person($result_row);
        // append the person object to the array
        $availablePersons[] = $person;
    }

    // close everything and return the availablePersons array
    $eventQuery->close();
    $personQuery->close(); 
    $con->close();
    return $availablePersons;
}

/*
    Function to find users based on any comnination of provided parameters
*/
function find_users($name, $id, $phone, $zip, $type, $status) {
    $con = connect();
    // array to store various where clauses based on provided parameters
    $whereClauses = [];
    // array to store provided parameters
    $params = [];
    // string to store the param types, for parameter binding
    $paramTypes = "";
    
    // if $name is not empty, add it to the where clauses, params, and types
    if ($name) {
        // if there is a space in the name, split it into first and last names
        if (strpos($name, ' ') !== false) {
            $fullname = explode(' ', $name, 2);
            $first = $fullname[0];
            $last = $fullname[1];
            // now we know we're searching by first AND last name
            $whereClauses[] = "(first_name LIKE ? AND last_name LIKE ?)";
            // add the parameters for binding
            $params[] = "%$first%";
            $params[] = "%$last%";
            // first and last are both strings
            $paramTypes .= "ss";
        }
        // otherwise, only 1 name was provided. Unknown if it is first or last
        else {
            $whereClauses[] = "(first_name LIKE ? OR last_name LIKE ?)";
            // $name will be inserted into both ? placeholders
            $params[] = "%$name%";
            $params[] = "%$name%";
            // both are strings
            $paramTypes .= "ss";
        }   
    }
    
    // if $id isnt empty or null, do the same
    if ($id) {
        $whereClauses[] = "id LIKE ?";
        $params[] = "%$id%";
        // person ids are strings
        $paramTypes .= "s"; 
    }
    
    // if phone not empty or null, do the same
    if ($phone) {
        $whereClauses[] = "phone1 LIKE ?";
        $params[] = "%$phone%";
        // phone numbers are strings
        $paramTypes .= "s"; 
    }
    
    // same for zip code
    if ($zip) {
        $whereClauses[] = "zip LIKE ?";
        $params[] = "%$zip%";
        // zip codes are strings
        $paramTypes .= "s";
    }

    // same for type
    if ($type) {
        $whereClauses[] = "type = ?";
        $params[] = $type;
        // type is a string
        $paramTypes .= "s";
    }

    // same for status
    if ($status) {
        $whereClauses[] = "status = ?";
        $params[] = $status;
        // status is a string
        $paramTypes .= "s";
    }

    // if $whereClauses is empty, no data was provided to search by, so close and return an empty array
    if (empty($whereClauses)) {
        $con->close();
        return [];
    }

    // build the full where clause
    $whereClause = "WHERE " . implode(" AND ", $whereClauses);
    // build the full query
    $query = "SELECT * FROM dbPersons $whereClause ORDER BY last_name, first_name";
    // prepare the query for parameter binding
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $con->error);
    }

    // bind the parameters. Since the params are stored in an array, they have to be unpacked
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    
    // execute the query and store the result, which will be rows of person data
    $stmt->execute();
    $result = $stmt->get_result();

    // loop through all the person data returned and turn it into person objects
    $persons = [];
    while ($result_row = $result->fetch_assoc()) {
        if ($result_row['id'] !== 'vmsroot')
        $persons[] = make_a_person($result_row);
    }
    
    // close everything and return the persons array
    $stmt->close();
    $con->close();
    return $persons;
}

function find_user_names($name) {
        $where = 'where ';
        if (!($name)) {
            return [];
        }
        $first = true;
        if ($name) {
            if (strpos($name, ' ')) {
                $name = explode(' ', $name, 2);
                $first = $name[0];
                $last = $name[1];
                $where .= "first_name like '%$first%' and last_name like '%$last%'";
            } else {
                $where .= "(first_name like '%$name%' or last_name like '%$name%')";
            }
            $first = false;
        }
	$query = "select * from dbPersons $where order by last_name, first_name";
        // echo $query;
        $connection = connect();
        $result = mysqli_query($connection, $query);
        if (!$result) {
            mysqli_close($connection);
            return [];
	}
        $raw = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $persons = [];
        foreach ($raw as $row) {
            if ($row['id'] == 'vmsroot') {
                continue;
            }
            $persons []= make_a_person($row);
        }
        mysqli_close($connection);
        return $persons;
    }

    function update_type($id, $role) {
        $con=connect();
        $query = 'UPDATE dbPersons SET type = "' . $role . '" WHERE id = "' . $id . '"';
        $result = mysqli_query($con,$query);
        mysqli_close($con);
        return $result;
    }
    
    function update_status($id, $new_status){
        $con=connect();
        $query = 'UPDATE dbPersons SET status = "' . $new_status . '" WHERE id = "' . $id . '"';
        $result = mysqli_query($con,$query);
        mysqli_close($con);
        return $result;
    }
    function update_notes($id, $new_notes){
        $con=connect();
        $query = 'UPDATE dbPersons SET notes = "' . $new_notes . '" WHERE id = "' . $id . '"';
        $result = mysqli_query($con,$query);
        mysqli_close($con);
        return $result;
    }
    
    function get_dbtype($id) {
        $con=connect();
        $query = "SELECT type FROM dbPersons";
        $query.= " WHERE id = '" .$id. "'"; 
        $result = mysqli_query($con,$query);
        mysqli_close($con);
        return $result;
    }
    date_default_timezone_set("America/New_York");

    function get_events_attended_by($personID) {
        $today = date("Y-m-d");
        $query = "select * from dbEventVolunteers, dbEvents
                  where userID='$personID' and eventID=id
                  and date<='$today'
                  order by date asc";
        $connection = connect();
        $result = mysqli_query($connection, $query);
        if ($result) {
            require_once('include/time.php');
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_close($connection);
            foreach ($rows as &$row) {
                $row['duration'] = calculateHourDuration($row['startTime'], $row['endTime']);
            }
            unset($row); // suggested for security
            return $rows;
        } else {
            mysqli_close($connection);
            return [];
        }
    }

    function get_events_attended_by_and_date($personID,$fromDate,$toDate) {
        $today = date("Y-m-d");
        $query = "select * from dbEventVolunteers, dbEvents
                  where userID='$personID' and eventID=id
                  and date<='$toDate' and date >= '$fromDate'
                  order by date desc";
        $connection = connect();
        $result = mysqli_query($connection, $query);
        if ($result) {
            require_once('include/time.php');
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_close($connection);
            foreach ($rows as &$row) {
                $row['duration'] = calculateHourDuration($row['startTime'], $row['endTime']);
            }
            unset($row); // suggested for security
            return $rows;
        } else {
            mysqli_close($connection);
            return [];
        }
    }

    function get_events_attended_by_desc($personID) {
        $today = date("Y-m-d");
        $query = "select * from dbEventVolunteers, dbEvents
                  where userID='$personID' and eventID=id
                  and date<='$today'
                  order by date desc";
        $connection = connect();
        $result = mysqli_query($connection, $query);
        if ($result) {
            require_once('include/time.php');
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_close($connection);
            foreach ($rows as &$row) {
                $row['duration'] = calculateHourDuration($row['startTime'], $row['endTime']);
            }
            unset($row); // suggested for security
            return $rows;
        } else {
            mysqli_close($connection);
            return [];
        }
    }

    function get_hours_volunteered_by($personID) {
        $events = get_events_attended_by($personID);
        $hours = 0;
        foreach ($events as $event) {
            $duration = $event['duration'];
            $type = $event['eventType'];
            if ($duration > 0 && $type != 'board_meeting') {
                $hours += $duration;
            }
        }
        return $hours;
    }

    function get_hours_volunteered_by_and_date($personID,$fromDate,$toDate) {
        $events = get_events_attended_by_and_date($personID,$fromDate,$toDate);
        $hours = 0;
        foreach ($events as $event) {
            $duration = $event['duration'];
            $type = $event['eventType'];
            if ($duration > 0 && $type != 'board_meeting') {
                $hours += $duration;
            }
        }
        return $hours;
    }

    // deprecated and marked for execution by firing squad
    function get_tot_vol_hours($type,$stats,$dateFrom,$dateTo,$lastFrom,$lastTo){
        $con = connect();
        $type1 = "volunteer";
        //$stats = "Active";
        if (($type=="general_volunteer_report" || $type == "total_vol_hours") && ($dateFrom == NULL && $dateTo == NULL && $lastFrom == NULL && $lastTo == NULL)) {
            if ($stats == 'Active' || $stats == 'Inactive') {
                $query = $query = "SELECT * FROM dbPersons WHERE type='$type1' AND status='$stats'";
            } else {
                $query = $query = "SELECT * FROM dbPersons WHERE type='$type1'";
            }
            $result = mysqli_query($con,$query);
            $totHours = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $hours = get_hours_volunteered_by($row['id']);
                $totHours[] = $hours;
            }
            $sum = 0;
            foreach ($totHours as $hrs) {
                $sum += $hrs;
            }
            return $sum;
        }
        elseif (($type=="general_volunteer_report" || $type == "total_vol_hours") && ($dateFrom && $dateTo && $lastFrom && $lastTo)) {
            $today = date("Y-m-d");
            if ($stats == 'Active' || $stats == 'Inactive') {
                $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    WHERE date >= '$dateFrom' AND date<='$dateTo' AND dbPersons.status='$stats' GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            } else {
                $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    WHERE date >= '$dateFrom' AND date<='$dateTo'
                    GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            }
            $result = mysqli_query($con,$query);
            try {
                // Code that might throw an exception or error goes here
                $dd = getBetweenDates($dateFrom, $dateTo);
                $nameRange = range($lastFrom, $lastTo);
                $bothRange = array_merge($dd, $nameRange);
                $dateRange = @fetch_events_in_date_range_as_array($dateFrom, $dateTo)[0];
                $totHours = array();
                while($row = mysqli_fetch_assoc($result)){
                    foreach ($bothRange as $both) {
                        if(in_array($both, $dateRange) && in_array($row['last_name'][0], $nameRange)){
                            $hours = $row['Dur'];   
                            $totHours[] = $hours;
                        }
                    }
                }
                $sum = 0;
                foreach($totHours as $hrs){
                    $sum += $hrs;
                }
            } catch (TypeError $e) {
                // Code to handle the exception or error goes here
                echo "No Results found!"; 
            }
            return $sum; 
        } elseif (($type == "general_volunteer_report" ||$type == "total_vol_hours") && ($dateFrom && $dateTo && $lastFrom == NULL  && $lastTo == NULL)) {
            if ($stats == 'Active' || $stats == 'Inactive') {
                $query = $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    WHERE date >= '$dateFrom' AND date<='$dateTo' AND dbPersons.status='$stats' GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            } else {
                $query = $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    WHERE date >= '$dateFrom' AND date<='$dateTo'
                    GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            }
            $result = mysqli_query($con,$query);
            try {
                // Code that might throw an exception or error goes here
                $dd = getBetweenDates($dateFrom, $dateTo);
                $dateRange = @fetch_events_in_date_range_as_array($dateFrom, $dateTo)[0];
                $totHours = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($dd as $date) {
                        if (in_array($date, $dateRange)) {
                            $hours = $row['Dur'];   
                            $totHours[] = $hours;
                        }
                    }
                }
                $sum = 0;
                foreach($totHours as $hrs){
                    $sum += $hrs;
                }
            } catch (TypeError $e) {
                // Code to handle the exception or error goes here
                echo "No Results found!"; 
            }
            return $sum;
        } elseif(($type == "general_volunteer_report" ||$type == "total_vol_hours") && ($dateFrom == NULL && $dateTo ==NULL && $lastFrom && $lastTo)) {
	        if ($stats == 'Active' || $stats == 'Inactive') {
		        $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    WHERE dbPersons.status='$stats'
                    GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            } else {
		        $query = "SELECT dbPersons.id,dbPersons.first_name,dbPersons.last_name, SUM(HOUR(TIMEDIFF(dbEvents.endTime, dbEvents.startTime))) as Dur
                    FROM dbPersons JOIN dbEventVolunteers ON dbPersons.id = dbEventVolunteers.userID
                    JOIN dbEvents ON dbEventVolunteers.eventID = dbEvents.id
                    GROUP BY dbPersons.first_name,dbPersons.last_name
                    ORDER BY Dur";
            }
            //$query = "SELECT * FROM dbPersons WHERE dbPersons.status='$stats'";
            $result = mysqli_query($con,$query);
            $nameRange = range($lastFrom,$lastTo);
            $totHours = array();
            while ($row = mysqli_fetch_assoc($result)) {
                foreach ($nameRange as $a) {
                    if ($row['last_name'][0] == $a) {
                        $hours = get_hours_volunteered_by($row['id']);   
                        $totHours[] = $hours;
                    }
                }
            }
            $sum = 0;
            foreach($totHours as $hrs){
                $sum += $hrs;
            }
            return $sum;
        }
    }

    function remove_profile_picture($id) {
        $con=connect();
        $query = 'UPDATE dbPersons SET profile_pic="" WHERE id="'.$id.'"';
        $result = mysqli_query($con,$query);
        mysqli_close($con);
        return True;
    }

    function get_name_from_id($id) {
        if ($id == 'vmsroot') {
            return 'System';
        }
        $query = "select first_name, last_name from dbPersons
            where id='$id'";
        $connection = connect();
        $result = mysqli_query($connection, $query);
        if (!$result) {
            return null;
        }

        $row = mysqli_fetch_assoc($result);
        mysqli_close($connection);
        return $row['first_name'] . ' ' . $row['last_name'];
    }
function get_persons_with_specific_training($trainingNames) {
    // return empty array if $trainingNames is empty
    if (empty($trainingNames)) {
        return [];
    }
    $con = connect();
    // count number of training names provided
    $numTrainings = count($trainingNames);
    // create a string of ? placeholders for the query, separated by commas
    $placeholders = implode(',', array_fill(0, $numTrainings, '?'));
    // prepare query statement searching for all persons who have completed all provided trainings
    $query = "SELECT id FROM dbpersonstrainings WHERE training_name IN ($placeholders)
            GROUP BY id
            HAVING COUNT(DISTINCT training_name) = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $con->error);
    }
    // each training name is a string, the number of trainings provided is an int.
    // create a string of types (s) plus one int (i) for param binding
    $paramTypes = "";
    for ($i = 0; $i < $numTrainings; $i++) {
        $paramTypes .= 's';
    }
    $paramTypes .= 'i';

    //combine training names + count into an array
    $paramValues = array_merge($trainingNames, [$numTrainings]);
    // bind the parameters to the prepared statement
    $stmt->bind_param($paramTypes, ...$paramValues);

    // execute the query and store the result
    $stmt->execute();
    $result = $stmt->get_result();

    //store all collected user ids into an array
    $personIds = [];
    while ($row = $result->fetch_assoc()) {
        $personIds[] = $row['id'];
    }

    // close and return
    $stmt->close();
    $con->close();
    return $personIds;
}