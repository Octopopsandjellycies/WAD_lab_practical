<?php
require_once 'auth_check.php';
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$result = mysqli_query(
    $conn,
    "SELECT id, name, email, phone, course, dob, photo, created_at
     FROM students
     ORDER BY created_at DESC, id DESC"
);

$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'data' => $students
]);
?>
