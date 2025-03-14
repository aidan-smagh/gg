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
 */
function add_document($title, $url) {
    $con = connect();
    // Prepare the sql statement, preventing SQL injection
    $stmt = $con->prepare("INSERT INTO dbexternaldocuments (title, url) VALUES (?, ?)");
    //bind the parameters to the statement. ss because both parameters are string types
    $stmt->bind_param("ss", $title, $url);
    $stmt->execute();
    $stmt->close();
    mysqli_close($con);
}
?>