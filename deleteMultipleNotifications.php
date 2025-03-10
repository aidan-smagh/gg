<?php
session_start();
require_once('database/dbMessages.php');

if (!isset($_SESSION['_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_ids'])) {
    $messageIDs = $_POST['message_ids'];
    
    foreach ($messageIDs as $id) {
        delete_message(intval($id));
    }
    header("Location: inbox.php?deleteSuccess");
    exit();
} else {
    header("Location: inbox.php");
}
?>