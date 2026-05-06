<?php
require_once 'auth_check.php';
require_once 'config.php';

$adminName = htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8');

$totalStudents = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM students"))[0];
$totalCourses = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM courses"))[0];

$latestResult = mysqli_query($conn, "SELECT name FROM students ORDER BY created_at DESC, id DESC LIMIT 1");
$latestStudent = mysqli_fetch_assoc($latestResult)['name'] ?? 'No records yet';

$recentResult = mysqli_query(
    $conn,
    "SELECT id, name, course, photo, created_at
     FROM students
     ORDER BY created_at DESC, id DESC
     LIMIT 5"
);
$recentStudents = mysqli_fetch_all($recentResult, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Student Record System</title>
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
                    <li class="nav-item"><a class="nav-link-custom active" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="add_student.php">Add Student</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="manage_courses.php">Courses</a></li>
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
        <div class="fade-in-main mb-4">
            <p class="mb-2 text-uppercase fw-bold" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.8rem;">Dashboard</p>
            <h1 class="section-title">Welcome back, <span><?= $adminName ?></span></h1>
            <p class="mt-2 mb-0" style="color:var(--text-soft);max-width:640px;">
                Track total students, course coverage, and recent registrations from one clean admin panel.
            </p>
        </div>

        <section class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-icon blue"><i class="bi bi-people"></i></div>
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-label">Total students</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-icon green"><i class="bi bi-journal-text"></i></div>
                    <div class="stat-value"><?= $totalCourses ?></div>
                    <div class="stat-label">Courses offered</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-icon yellow"><i class="bi bi-person-badge"></i></div>
                    <div class="stat-value" style="font-size:1.8rem;"><?= htmlspecialchars($latestStudent, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="stat-label">Latest student</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="add_student.php" class="stat-card h-100 d-block text-decoration-none">
                    <div class="stat-icon red"><i class="bi bi-plus-lg"></i></div>
                    <div class="stat-value" style="font-size:1.8rem;">Create</div>
                    <div class="stat-label">Add a new student record</div>
                </a>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-lg-4">
                <div class="content-card h-100">
                    <h2 class="mb-3" style="font-size:1.6rem;">Quick actions</h2>
                    <div class="d-grid gap-3">
                        <a href="add_student.php" class="content-card text-decoration-none" style="padding:20px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon blue mb-0"><i class="bi bi-person-plus"></i></div>
                                <div>
                                    <div class="fw-bold text-dark">Register student</div>
                                    <div style="color:var(--text-soft);">Create a new profile with photo and details.</div>
                                </div>
                            </div>
                        </a>
                        <a href="view_students.php" class="content-card text-decoration-none" style="padding:20px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon green mb-0"><i class="bi bi-table"></i></div>
                                <div>
                                    <div class="fw-bold text-dark">Browse records</div>
                                    <div style="color:var(--text-soft);">Search, sort, edit, and delete students.</div>
                                </div>
                            </div>
                        </a>
                        <a href="manage_courses.php" class="content-card text-decoration-none" style="padding:20px;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon yellow mb-0"><i class="bi bi-book"></i></div>
                                <div>
                                    <div class="fw-bold text-dark">Manage courses</div>
                                    <div style="color:var(--text-soft);">Create course options before assigning students.</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="content-card h-100">
                    <div class="section-header mb-3">
                        <div>
                            <h2 class="mb-1" style="font-size:1.7rem;">Recent students</h2>
                            <p class="mb-0" style="color:var(--text-soft);">Latest five entries from the database.</p>
                        </div>
                        <a href="view_students.php" class="btn btn-primary-custom">Open Table</a>
                    </div>

                    <?php if ($recentStudents): ?>
                    <div class="table-custom-wrapper">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentStudents as $student): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge-course"><?= htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><?= date('d M Y', strtotime($student['created_at'])) ?></td>
                                    <td><a href="edit_student.php?id=<?= (int) $student['id'] ?>" class="btn-sm-action btn-edit"><i class="bi bi-pencil"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="content-card" style="background:var(--surface-muted);">
                        <h3 style="font-size:1.5rem;">No students added yet</h3>
                        <p class="mb-3" style="color:var(--text-soft);">Create your first student record to start populating the dashboard.</p>
                        <a href="add_student.php" class="btn btn-primary-custom">Add Student</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<footer>
    <div class="container-lg">Student Record System &copy; <?= date('Y') ?></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
