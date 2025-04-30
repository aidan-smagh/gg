<?php
//for connection to database
include_once('database/dbinfo.php');

function get_all_trainings() {
    $con = connect();
    $query = "SELECT name FROM dbTrainings ORDER BY name ASC";
    //execute the query and store it in $result
    $result = mysqli_query($con, $query);
    // array to hold the trainings for return
    $trainings = [];

    // add each row of data, stored in the $result table, to the trainingss array
    // a row in $result consists of id, title, and url
    while ($row = mysqli_fetch_assoc($result)) {
        $trainings[] = $row;
    }
    // close database connection and return the trainings array
    mysqli_close($con);
    return $trainings;
}
function get_all_trainings_with_description() {
    $con = connect();
    $query = "SELECT name, description FROM dbTrainings ORDER BY name ASC";
    //execute the query and store it in $result
    $result = mysqli_query($con, $query);
    // array to hold the trainings for return
    $trainings = [];

    // add each row of data, stored in the $result table, to the trainingss array
    // a row in $result consists of id, title, and url
    while ($row = mysqli_fetch_assoc($result)) {
        $trainings[] = $row;
    }
    // close database connection and return the trainings array
    mysqli_close($con);
    return $trainings;
}
function update_trainings($id, $updatedTraining) {
    $con = connect();

    //check if the combination is already in the database
    $checkquery = 'SELECT COUNT(*) FROM dbpersonstrainings WHERE id = ? AND training_name = ?';
    $stmt = mysqli_prepare($con, $checkquery);
    mysqli_stmt_bind_param($stmt, "ss", $id, $updatedTraining);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($count > 0) {
        mysqli_close($con);
        return false;
    }
    $query = 'INSERT INTO dbpersonstrainings (id, training_name) VALUES (?, ?)';
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $id, $updatedTraining);
    
    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $result;
}
function get_trainings_for($id) {
    $con = connect();
    $query = 'SELECT training_name FROM dbpersonstrainings WHERE id = ?';
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $trainingName);

    $trainings = [];
    while (mysqli_stmt_fetch($stmt)) {
        $trainings[] = stripslashes($trainingName);

    }
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return $trainings;
}
function add_training_to_db($name, $desc) {
    $con = connect();
    // was INSERT INTO dbtrainings
    $query = 'INSERT INTO dbTrainings (name, description) VALUES (?, ?)';
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $name, $desc);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    return "success";
}

function delete_training($name) {
    $con = connect();
    /* prepare the deletion statement */
    $stmt = $con->prepare("DELETE FROM dbTrainings WHERE name = ?");
    $stmt->bind_param("s", $name);

    /* Attempt to execute the deletion, storing the bool result in $success */
    $success = $stmt->execute();
    $stmt->close();
    mysqli_close($con);

    /* For error handling, return the success status */
    if ($success) {
        return "success";
    }
    else {
        return "error";
    }
}
?>