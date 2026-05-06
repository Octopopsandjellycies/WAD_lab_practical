<?php
require_once 'session_bootstrap.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Enter both username and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admin_users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_regenerated'] = time();
            header('Location: index.php');
            exit();
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Student Record System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-hero">
            <span class="eyebrow">Student Record System</span>
            <h1>Simple access for student records.</h1>
            <p>Sign in to continue.</p>
            <div class="auth-note">
                <strong>Admin:</strong> admin / password
            </div>
        </section>

        <section class="auth-card">
            <div class="brand-mark">SRS</div>
            <h2>Sign in</h2>
            <p class="auth-subtitle">Minimal, secure, and persistent.</p>

            <?php if ($error !== ''): ?>
            <div class="alert alert-danger minimal-alert" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="Enter your username"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary-custom w-100">Sign in</button>
            </form>
        </section>
    </main>
</body>
</html>
