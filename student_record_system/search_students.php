<?php
require_once 'auth_check.php';
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$term = trim($_GET['q'] ?? '');
$like = '%' . $term . '%';

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, phone, course, dob, photo, created_at
     FROM students
     WHERE name LIKE ? OR email LIKE ? OR course LIKE ? OR phone LIKE ?
     ORDER BY created_at DESC, id DESC"
);

mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo json_encode([
    'status' => 'success',
    'data' => mysqli_fetch_all($result, MYSQLI_ASSOC)
]);
?>
