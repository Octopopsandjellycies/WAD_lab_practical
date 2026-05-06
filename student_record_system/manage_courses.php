<?php
require_once 'auth_check.php';
require_once 'config.php';

$adminName = htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8');
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_course'])) {
        $courseName = trim($_POST['course_name'] ?? '');

        if (!preg_match('/^[A-Za-z0-9&().,\-\s]{2,100}$/', $courseName)) {
            $error = 'Enter a valid course name with at least 2 characters.';
        } else {
            $insertStmt = mysqli_prepare($conn, "INSERT INTO courses (course_name) VALUES (?)");
            mysqli_stmt_bind_param($insertStmt, 's', $courseName);

            if (mysqli_stmt_execute($insertStmt)) {
                $success = 'Course created successfully.';
                $_POST = [];
            } else {
                $error = mysqli_errno($conn) === 1062 ? 'This course already exists.' : 'Unable to create the course right now.';
            }
        }
    }

    if (isset($_POST['delete_course'])) {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $courseName = trim($_POST['course_label'] ?? '');

        if ($courseId > 0) {
            $usageStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM students WHERE course = ?");
            mysqli_stmt_bind_param($usageStmt, 's', $courseName);
            mysqli_stmt_execute($usageStmt);
            $usage = mysqli_fetch_assoc(mysqli_stmt_get_result($usageStmt));

            if ((int) ($usage['total'] ?? 0) > 0) {
                $error = 'This course is already assigned to students and cannot be deleted.';
            } else {
                $deleteStmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
                mysqli_stmt_bind_param($deleteStmt, 'i', $courseId);
                if (mysqli_stmt_execute($deleteStmt)) {
                    $success = 'Course deleted successfully.';
                } else {
                    $error = 'Unable to delete the course right now.';
                }
            }
        }
    }
}

$coursesResult = mysqli_query(
    $conn,
    "SELECT c.id, c.course_name, c.created_at, COUNT(s.id) AS student_total
     FROM courses c
     LEFT JOIN students s ON s.course = c.course_name
     GROUP BY c.id, c.course_name, c.created_at
     ORDER BY c.course_name ASC"
);
$courses = mysqli_fetch_all($coursesResult, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | Student Record System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-lg">
            <a class="navbar-brand navbar-brand-text" href="index.php">Student <span>Records</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item"><a class="nav-link-custom" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="add_student.php">Add Student</a></li>
                    <li class="nav-item"><a class="nav-link-custom active" href="manage_courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="view_students.php">View Students</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="admin-pill"><i class="bi bi-person-circle"></i> <?= $adminName ?></div>
                    <a href="logout.php" class="nav-link-custom btn-logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main class="page-wrapper">
    <div class="container-lg">
        <div class="section-header">
            <div>
                <p class="mb-2 text-uppercase fw-bold" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.8rem;">Course management</p>
                <h1 class="section-title">Create <span>Courses</span></h1>
                <p class="mt-2 mb-0" style="color:var(--text-soft);">Build the course list once, then reuse it while adding or editing students.</p>
            </div>
            <a href="add_student.php" class="btn btn-primary-custom">Add Student</a>
        </div>

        <?php if ($success !== ''): ?>
        <div class="alert-custom alert-success-custom mb-4"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
        <div class="alert-custom alert-danger-custom mb-4"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="content-card h-100">
                    <h2 class="mb-3" style="font-size:1.7rem;">New course</h2>
                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course name</label>
                            <input
                                type="text"
                                id="course_name"
                                name="course_name"
                                class="form-control"
                                value="<?= htmlspecialchars($_POST['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="Example: Cyber Security"
                                maxlength="100"
                                required
                            >
                        </div>
                        <button type="submit" name="create_course" class="btn btn-primary-custom">Create Course</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="content-card h-100">
                    <div class="section-header mb-3">
                        <div>
                            <h2 class="mb-1" style="font-size:1.7rem;">Available courses</h2>
                            <p class="mb-0" style="color:var(--text-soft);">Total courses: <?= count($courses) ?></p>
                        </div>
                    </div>

                    <?php if ($courses): ?>
                    <div class="table-custom-wrapper">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Students</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $course['student_total'] ?></td>
                                    <td><?= date('d M Y', strtotime($course['created_at'])) ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                                            <input type="hidden" name="course_label" value="<?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" name="delete_course" class="btn-sm-action btn-delete" <?= ((int) $course['student_total'] > 0) ? 'disabled title="Assigned to students"' : '' ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="content-card" style="background:var(--surface-muted);">
                        <h3 style="font-size:1.5rem;">No courses yet</h3>
                        <p class="mb-0" style="color:var(--text-soft);">Create your first course to use it in student records.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<footer>
    <div class="container-lg">Student Record System &copy; <?= date('Y') ?></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
