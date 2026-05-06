<?php
require_once 'auth_check.php';
require_once 'config.php';

$adminName = htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8');
$courseRows = mysqli_fetch_all(
    mysqli_query($conn, "SELECT course_name FROM courses ORDER BY course_name ASC"),
    MYSQLI_ASSOC
);
$courseOptions = array_column($courseRows, 'course_name');
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: view_students.php');
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header('Location: view_students.php');
    exit();
}

if ($student['course'] !== '' && !in_array($student['course'], $courseOptions, true)) {
    $courseOptions[] = $student['course'];
    sort($courseOptions);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $errors = [];

    if (!preg_match('/^[A-Za-z\s]{3,}$/', $name)) {
        $errors[] = 'Name must contain at least 3 letters only.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!preg_match('/^\d{10}$/', $phone)) {
        $errors[] = 'Phone number must contain exactly 10 digits.';
    }
    if ($course === '') {
        $errors[] = 'Course is required.';
    } elseif (!in_array($course, $courseOptions, true)) {
        $errors[] = 'Select a valid course.';
    }
    if ($dob === '' || strtotime($dob) === false || strtotime($dob) >= strtotime(date('Y-m-d'))) {
        $errors[] = 'Date of birth must be a past date.';
    }

    $photoName = $student['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $errors[] = 'Photo must be a JPG or PNG file.';
        } else {
            $newPhotoName = uniqid('student_', true) . '.' . $extension;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $newPhotoName)) {
                if ($photoName !== 'default.png' && is_file(UPLOAD_DIR . $photoName)) {
                    unlink(UPLOAD_DIR . $photoName);
                }
                $photoName = $newPhotoName;
            } else {
                $errors[] = 'Unable to upload the selected photo.';
            }
        }
    }

    if (empty($errors)) {
        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE students SET name = ?, email = ?, phone = ?, course = ?, dob = ?, photo = ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($updateStmt, 'ssssssi', $name, $email, $phone, $course, $dob, $photoName, $id);

        try {
            mysqli_stmt_execute($updateStmt);
            $success = 'Student record updated successfully.';

            $refreshStmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
            mysqli_stmt_bind_param($refreshStmt, 'i', $id);
            mysqli_stmt_execute($refreshStmt);
            $student = mysqli_fetch_assoc(mysqli_stmt_get_result($refreshStmt));
        } catch (mysqli_sql_exception $exception) {
            $error = ((int) $exception->getCode() === 1062)
                ? 'This email address is already used by another student. Please enter a different email.'
                : 'Unable to update the student right now. Please try again.';
        }
    } else {
        $error = implode(' ', $errors);
    }
}

$photoSrc = ($student['photo'] && $student['photo'] !== 'default.png')
    ? UPLOAD_URL . htmlspecialchars($student['photo'], ENT_QUOTES, 'UTF-8')
    : 'https://via.placeholder.com/96x96.png?text=Photo';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Student Record System</title>
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
                    <li class="nav-item"><a class="nav-link-custom" href="manage_courses.php">Courses</a></li>
                    <li class="nav-item"><a class="nav-link-custom active" href="view_students.php">View Students</a></li>
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
                <p class="mb-2 text-uppercase fw-bold" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.8rem;">Edit record</p>
                <h1 class="section-title">Update <span>Student</span></h1>
            </div>
            <a href="view_students.php" class="btn btn-primary-custom">Back to Table</a>
        </div>

        <?php if ($success !== ''): ?>
        <div class="alert-custom alert-success-custom mb-4"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
        <div class="alert-custom alert-danger-custom mb-4"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="content-card mb-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <img src="<?= $photoSrc ?>" alt="Student photo" style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                <div>
                    <h2 class="mb-1" style="font-size:1.8rem;"><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="mb-0" style="color:var(--text-soft);"><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?> | ID #<?= $id ?></p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <form id="studentForm" method="post" enctype="multipart/form-data" novalidate>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="field-error" id="name-error"></span>
                        <span class="field-success" id="name-success"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="field-error" id="email-error"></span>
                        <span class="field-success" id="email-success"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" maxlength="10" value="<?= htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="field-error" id="phone-error"></span>
                        <span class="field-success" id="phone-success"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="course" class="form-label">Course</label>
                        <select id="course" name="course" class="form-select">
                            <option value="">Select a course</option>
                            <?php foreach ($courseOptions as $courseOption): ?>
                            <option value="<?= htmlspecialchars($courseOption, ENT_QUOTES, 'UTF-8') ?>" <?= ($student['course'] === $courseOption) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($courseOption, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($courseOptions)): ?>
                        <div class="mt-2" style="color:var(--text-soft);font-size:0.9rem;">
                            No courses available yet. <a href="manage_courses.php">Create a course first</a>.
                        </div>
                        <?php endif; ?>
                        <span class="field-error" id="course-error"></span>
                        <span class="field-success" id="course-success"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($student['dob'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="field-error" id="dob-error"></span>
                        <span class="field-success" id="dob-success"></span>
                    </div>

                    <div class="col-md-6">
                        <label for="photo" class="form-label">Profile Photo</label>
                        <div class="upload-area">
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                            <div id="upload-placeholder">
                                <i class="bi bi-image" style="font-size:2rem;color:var(--text-muted);"></i>
                                <p class="mb-1 mt-2 fw-semibold">Upload a new JPG or PNG file</p>
                                <p class="mb-0" style="color:var(--text-soft);font-size:0.9rem;">Leave empty to keep the current photo.</p>
                            </div>
                            <img id="photo-preview" alt="Photo preview">
                        </div>
                        <span class="field-error" id="photo-error"></span>
                        <span class="field-success" id="photo-success"></span>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom">Update Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <div class="container-lg">Student Record System &copy; <?= date('Y') ?></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const form = document.getElementById('studentForm');
const photoInput = document.getElementById('photo');
const dobInput = document.getElementById('dob');
const preview = document.getElementById('photo-preview');
const placeholder = document.getElementById('upload-placeholder');

const validators = {
    name: {
        regex: /^[A-Za-z\s]{3,}$/,
        error: 'Name must contain at least 3 letters only.'
    },
    email: {
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        error: 'Enter a valid email address.'
    },
    phone: {
        regex: /^\d{10}$/,
        error: 'Phone number must contain exactly 10 digits.'
    },
    course: {
        regex: /^.+$/,
        error: 'Please select a course.'
    }
};

function setFieldState(fieldId, isValid, successMessage, errorMessage) {
    const field = document.getElementById(fieldId);
    const error = document.getElementById(fieldId + '-error');
    const success = document.getElementById(fieldId + '-success');

    field.classList.toggle('is-valid', isValid);
    field.classList.toggle('is-invalid', !isValid);
    error.textContent = isValid ? '' : errorMessage;
    success.innerHTML = isValid ? '<i class="bi bi-check-circle-fill me-1"></i>' + successMessage : '';

    return isValid;
}

function validateTextField(fieldId) {
    const rule = validators[fieldId];
    const value = document.getElementById(fieldId).value.trim();
    return setFieldState(fieldId, rule.regex.test(value), 'Looks good.', rule.error);
}

function validateDob() {
    const value = dobInput.value;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dobDate = value ? new Date(value) : null;
    const isValid = Boolean(dobDate) && dobDate < today;
    return setFieldState('dob', isValid, 'Valid date selected.', 'Date of birth must be a past date.');
}

function validatePhoto() {
    const file = photoInput.files[0];
    if (!file) {
        document.getElementById('photo-error').textContent = '';
        document.getElementById('photo-success').textContent = '';
        return true;
    }

    const valid = /\.(jpe?g|png)$/i.test(file.name);
    document.getElementById('photo-error').textContent = valid ? '' : 'Photo must be a JPG or PNG file.';
    document.getElementById('photo-success').innerHTML = valid ? '<i class="bi bi-check-circle-fill me-1"></i>Photo ready.' : '';

    if (valid) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.src = event.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }

    return valid;
}

Object.keys(validators).forEach((fieldId) => {
    const element = document.getElementById(fieldId);
    element.addEventListener('input', () => validateTextField(fieldId));
    element.addEventListener('blur', () => validateTextField(fieldId));
    if (element.tagName === 'SELECT') {
        element.addEventListener('change', () => validateTextField(fieldId));
    }
});

dobInput.addEventListener('change', validateDob);
photoInput.addEventListener('change', validatePhoto);

form.addEventListener('submit', function(event) {
    const textValid = Object.keys(validators).every(validateTextField);
    const dobValid = validateDob();
    const photoValid = validatePhoto();

    if (!(textValid && dobValid && photoValid)) {
        event.preventDefault();
    }
});
</script>
</body>
</html>
