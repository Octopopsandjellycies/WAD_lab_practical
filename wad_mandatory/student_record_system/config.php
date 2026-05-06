<?php
// Database configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_db');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function tableExists(mysqli $conn, string $tableName): bool
{
    $safeName = mysqli_real_escape_string($conn, $tableName);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$safeName}'");
    return mysqli_num_rows($result) > 0;
}

function ensureCoursesTable(mysqli $conn): void
{
    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_name VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    if (tableExists($conn, 'students')) {
        mysqli_query(
            $conn,
            "INSERT IGNORE INTO courses (course_name)
             SELECT DISTINCT course
             FROM students
             WHERE TRIM(course) <> ''"
        );
    }

    $countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses");
    $countRow = mysqli_fetch_assoc($countResult);

    if ((int) ($countRow['total'] ?? 0) === 0) {
        $defaultCourses = [
            'Computer Science',
            'Information Technology',
            'Data Science',
            'Business Administration'
        ];

        $insertStmt = mysqli_prepare($conn, "INSERT IGNORE INTO courses (course_name) VALUES (?)");
        foreach ($defaultCourses as $courseName) {
            mysqli_stmt_bind_param($insertStmt, 's', $courseName);
            mysqli_stmt_execute($insertStmt);
        }
    }
}

try {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, 'utf8mb4');
    ensureCoursesTable($conn);
} catch (Throwable $e) {
    http_response_code(500);
    die(
        '<div style="font-family: Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 24px; border: 1px solid #f5c2c7; background: #f8d7da; color: #842029; border-radius: 12px;">'
        . '<h2 style="margin-top: 0;">Database Connection Error</h2>'
        . '<p>Unable to connect to <strong>student_db</strong>. Please verify your MySQL service and credentials in <code>config.php</code>.</p>'
        . '<p style="margin-bottom: 0;"><strong>Details:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div>'
    );
}
?>
