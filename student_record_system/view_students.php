<?php
require_once 'auth_check.php';
$adminName = htmlspecialchars($_SESSION['admin_username'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students | Student Record System</title>
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
                <p class="mb-2 text-uppercase fw-bold" style="letter-spacing:0.08em;color:var(--text-muted);font-size:0.8rem;">Directory</p>
                <h1 class="section-title">Student <span>Table</span></h1>
                <p class="mt-2 mb-0" id="record-count" style="color:var(--text-soft);">Loading student records...</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email, phone, or course">
                    <div class="search-spinner" id="searchSpinner">
                        <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                    </div>
                </div>
                <a href="add_student.php" class="btn btn-primary-custom">Add Student</a>
            </div>
        </div>

        <div id="action-alert" class="alert-custom mb-3" style="display:none;"></div>

        <div class="table-custom-wrapper">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Photo</th>
                        <th class="sortable" data-col="name">Name <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="sortable" data-col="course">Course <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th>DOB</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" class="text-center py-4" style="color:var(--text-soft);">Loading students...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="empty-state" class="content-card mt-3" style="display:none;">
            <h2 class="mb-2" style="font-size:1.7rem;">No matching students</h2>
            <p class="mb-0" style="color:var(--text-soft);">Try another search term or create a new record.</p>
        </div>
    </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container-lg">Student Record System &copy; <?= date('Y') ?></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
let allStudents = [];
let filteredStudents = [];
let currentSortColumn = '';
let currentSortDirection = 'asc';
let pendingDeleteId = null;

const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

function formatDate(value) {
    if (!value) {
        return '-';
    }

    const date = new Date(value);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function buildPhotoPath(photo) {
    return photo ? 'uploads/' + photo : 'https://via.placeholder.com/42x42.png?text=P';
}

function updateCount(count) {
    $('#record-count').text(count === 0 ? 'No records found.' : 'Showing ' + count + ' student' + (count === 1 ? '' : 's') + '.');
}

function renderRows(students) {
    const tbody = $('#tableBody');
    tbody.empty();

    if (!students.length) {
        updateCount(0);
        $('#empty-state').show();
        tbody.html('<tr><td colspan="8"></td></tr>');
        return;
    }

    $('#empty-state').hide();
    updateCount(students.length);

    students.forEach((student, index) => {
        const photo = student.photo && student.photo !== 'default.png' ? buildPhotoPath(student.photo) : 'https://via.placeholder.com/42x42.png?text=' + encodeURIComponent(student.name.charAt(0).toUpperCase());

        const row = $(`
            <tr id="row-${student.id}" style="display:none;">
                <td>${index + 1}</td>
                <td><img src="${photo}" alt="${student.name}" class="student-photo"></td>
                <td class="fw-semibold text-dark">${student.name}</td>
                <td>${student.email}</td>
                <td>${student.phone}</td>
                <td><span class="badge-course">${student.course}</span></td>
                <td>${formatDate(student.dob)}</td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="edit_student.php?id=${student.id}" class="btn-sm-action btn-edit"><i class="bi bi-pencil"></i></a>
                        <button type="button" class="btn-sm-action btn-delete delete-btn" data-id="${student.id}" data-name="${student.name}"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        `);

        tbody.append(row);
        row.fadeIn(180);
    });
}

function applyCurrentSort() {
    if (!currentSortColumn) {
        renderRows(filteredStudents);
        return;
    }

    const sorted = [...filteredStudents].sort((left, right) => {
        const a = String(left[currentSortColumn] || '').toLowerCase();
        const b = String(right[currentSortColumn] || '').toLowerCase();
        return currentSortDirection === 'asc' ? a.localeCompare(b) : b.localeCompare(a);
    });

    renderRows(sorted);
}

function loadStudents() {
    $.ajax({
        url: 'fetch_students.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                allStudents = response.data;
                filteredStudents = response.data;
                applyCurrentSort();
            }
        },
        error: function() {
            $('#tableBody').html('<tr><td colspan="8" class="text-center py-4 text-danger">Unable to load students.</td></tr>');
        }
    });
}

let searchTimer;
$('#searchInput').on('keyup', function() {
    const query = $(this).val().trim();
    clearTimeout(searchTimer);
    $('#searchSpinner').show();

    searchTimer = setTimeout(function() {
        if (query === '') {
            filteredStudents = allStudents;
            $('#searchSpinner').hide();
            applyCurrentSort();
            return;
        }

        $.ajax({
            url: 'search_students.php',
            method: 'GET',
            dataType: 'json',
            data: { q: query },
            success: function(response) {
                $('#searchSpinner').hide();
                if (response.status === 'success') {
                    filteredStudents = response.data;
                    applyCurrentSort();
                }
            },
            error: function() {
                $('#searchSpinner').hide();
            }
        });
    }, 250);
});

$(document).on('click', '.sortable', function() {
    const column = $(this).data('col');
    currentSortDirection = currentSortColumn === column && currentSortDirection === 'asc' ? 'desc' : 'asc';
    currentSortColumn = column;
    applyCurrentSort();
});

$(document).on('click', '.delete-btn', function() {
    pendingDeleteId = $(this).data('id');
    $('#deleteName').text($(this).data('name'));
    deleteModal.show();
});

$('#confirmDeleteBtn').on('click', function() {
    if (!pendingDeleteId) {
        return;
    }

    const deletedId = Number(pendingDeleteId);

    $.ajax({
        url: 'delete_student.php',
        method: 'POST',
        dataType: 'json',
        data: { id: deletedId },
        success: function(response) {
            deleteModal.hide();
            if (response.status === 'success') {
                $('#row-' + deletedId).fadeOut(220, function() {
                    allStudents = allStudents.filter((student) => Number(student.id) !== deletedId);
                    filteredStudents = filteredStudents.filter((student) => Number(student.id) !== deletedId);
                    applyCurrentSort();
                    showAlert('success', 'Student deleted successfully.');
                });
            } else {
                showAlert('error', response.message || 'Delete failed.');
            }
            pendingDeleteId = null;
        },
        error: function() {
            deleteModal.hide();
            showAlert('error', 'Unable to delete the student right now.');
            pendingDeleteId = null;
        }
    });
});

function showAlert(type, message) {
    const alert = $('#action-alert');
    alert.removeClass('alert-success-custom alert-danger-custom');
    alert.addClass(type === 'success' ? 'alert-success-custom' : 'alert-danger-custom');
    alert.text(message).stop(true, true).fadeIn(180).delay(2400).fadeOut(180);
}

loadStudents();
</script>
</body>
</html>
