<?php
// for connection to database
include_once('database/dbinfo.php');

/**
 * Collect all external documents
 * @return array of documents (id, title, url)
 */
function get_all_external_documents() {
    $con = connect();
    $query = "SELECT * FROM dbexternaldocuments ORDER BY id ASC";
    // Execute the query and store it in the table $result
    $result = mysqli_query($con, $query);
    // array to hold the documents for return
    $documents = [];

    // add each row of data, stored in the $result table, to the documents array
    // a row in $result consists of id, title, and url
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    // close database connection and return the documents array
    mysqli_close($con);
    return $documents;
}

/**
 * Insert a new external document
 * Retun values:
 * - "duplicateTitle" if a document with the same title already exists
 * - "success" if the document was added successfully
 * - "error" if there was a different unknown error that caused failure
 */
function add_document($title, $url) {
    $con = connect();
    /*First check if there is a document already stored with the new title */
    //base query 
    $query = $con->prepare("SELECT COUNT(*) FROM dbexternaldocuments WHERE title = ?");
    // bind the title parameter to the query. s because is is a string type
    $query->bind_param("s", $title);
    $query->execute();
    /* store the result of the query in $count
       $count will store the number of entries in the db with the same title */
    $query->store_result();
    $query->bind_result($count);
    $query->fetch();
    $query->close();

    if ($count > 0) {
        /*There is already a document with the same title */
        mysqli_close($con);
        return "duplicateTitle";
    }

    // Prepare the sql statement, preventing SQL injection
    $stmt = $con->prepare("INSERT INTO dbexternaldocuments (title, url) VALUES (?, ?)");
    //bind the parameters to the statement. ss because both parameters are string types
    $stmt->bind_param("ss", $title, $url);
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

function delete_document($title) {
    $con = connect();
    /* prepare the deletion statement */
    $stmt = $con->prepare("DELETE FROM dbexternaldocuments WHERE title = ?");
    $stmt->bind_param("s", $title);

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