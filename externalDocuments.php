<?php

session_cache_expire(30);
session_start();

// Ensure user is logged in. If not, redirect to login page
if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}
// Ensure use has appropriate access level. If not, redirect to index page
// 0 = not logged in, 1 = standard user, 2 = admin and boardmember, 3 super admin
if ($_SESSION['access_level'] < 2) {
    header('Location: index.php');
    die();
}
/* Only superadmin can add new external docs and edit exitsing ones */
$accessLevel = $_SESSION['access_level'];
$isSuperAdmin = $accessLevel >= 3;

/* Get all external docs stored in the database (function call to dbExternalDocuments.php) */
$documents = get_all_external_documents();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require('universal.inc'); ?>
    <title>Organizational Documents</title>
</head>
<body>
    <h1>External Documents</h1>
</body>
</html>