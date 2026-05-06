<?php
require_once 'auth_check.php';
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid student ID.']);
    exit();
}

$photo = '';

$photoStmt = mysqli_prepare($conn, "SELECT photo FROM students WHERE id = ?");
mysqli_stmt_bind_param($photoStmt, 'i', $id);
mysqli_stmt_execute($photoStmt);
$photoResult = mysqli_stmt_get_result($photoStmt);

if ($row = mysqli_fetch_assoc($photoResult)) {
    $photo = $row['photo'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
    exit();
}

$deleteStmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
mysqli_stmt_bind_param($deleteStmt, 'i', $id);

if (mysqli_stmt_execute($deleteStmt)) {
    if ($photo && $photo !== 'default.png') {
        $filePath = UPLOAD_DIR . $photo;
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Student deleted successfully.']);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Delete failed.']);
?>
