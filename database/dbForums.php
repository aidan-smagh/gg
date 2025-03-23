<?php
// for connection to database
include_once('database/dbinfo.php');

/**
 * Collect all forums posts
 * @return array of documents (id, title, url)
 */
function get_all_posts() {
    $con = connect();
    $query = "SELECT * FROM dbforums ORDER BY timePosted DESC";
    // Execute the query and store it in the table $result
    $result = mysqli_query($con, $query);
    // array to hold the posts for return
    $potsts = [];

    // add each row of data, stored in the $result table, to the posts array
    // a row in $result consists of id, title, and url
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    // close database connection and return the posts array
    mysqli_close($con);
    return $posts;
}

/**
 * Create a new forum post
 * Retun values:
 * - "success" if the post was added successfully
 * - "error" if there was a different unknown error that caused failure
 */
function add_post($title, $person, $url) {
    $con = connect();

    // Prepare the sql statement, preventing SQL injection
    $stmt = $con->prepare("INSERT INTO dbforums (title, poster, url) VALUES (?, ?, ?)");
    //bind the parameters to the statement. ss because both parameters are string types
    $stmt->bind_param("sss", $title, $person, $url);
    /* Attempt to execute the insertion, catching any errors */
    $success = $stmt->execute();
    $stmt->close();
    mysqli_close($con);

    /* return the result of the insertion */
    if ($success) {
        return "success";
    } else {
        return "error";
    }
}

function delete_post($id) {
    $con = connect();
    /* prepare the deletion statement */
    $stmt = $con->prepare("DELETE FROM dbforum WHERE id = ?");
    $stmt->bind_param("i", $id);

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