<?php
// delete.php - deletes registration and removes uploaded profile pic if exists
require 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header('Location: show_all.php');
    exit;
}

// get profile pic path to remove file
$stmt = $mysqli->prepare("SELECT profile_pic FROM registrations WHERE id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row) {
    // delete DB row
    $del = $mysqli->prepare("DELETE FROM registrations WHERE id = ?");
    $del->bind_param("i",$id);
    $ok = $del->execute();
    $del->close();

    // remove profile pic file if deletion succeeded and file exists
    if ($ok && $row['profile_pic']) {
        $file = __DIR__ . '/' . $row['profile_pic'];
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}

// redirect back with optional message
header('Location: show_all.php?msg=deleted');
exit;
