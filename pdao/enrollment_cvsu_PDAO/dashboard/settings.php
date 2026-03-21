<?php
session_start();
if (!$_SESSION['loggedin']) {
    header('Location: ../index.php');
    exit();
}

include '../php/ConnectToDb.php';

$success = '';
$error   = '';

$userId   = intval($_SESSION['user_id']);
$userStmt = $conn->prepare("SELECT username, email, role, created_at FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$curUser    = $userResult->fetch_assoc();
$userStmt->close();

$curUsername  = (string)($curUser['username']   ?? '');
$curEmail     = (string)($curUser['email']      ?? '');
$curRole      = isset($curUser['role']) ? (int)$curUser['role'] : -1;
$curCreatedAt = (string)($curUser['created_at'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password']      ?? '';
    $confirmPassword = $_POST['confirm_password']  ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($currentPassword, (string)$hashedPassword)) {
            $error = "Current password is incorrect.";
        } else {
            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $upd->bind_param("si", $newHashed, $userId);
            if ($upd->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "An error occurred. Please try again.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newUsername = trim((string)($_POST['username'] ?? ''));
    $newEmail    = trim((string)($_POST['email']    ?? ''));

    if (empty($newUsername) || empty($newEmail)) {
        $error = "Username and email are required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $check->bind_param("ssi", $newUsername, $newEmail, $userId);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "Username or email is already taken by another user.";
        } else {
            $upd = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
            $upd->bind_param("ssi", $newUsername, $newEmail, $userId);
            if ($upd->execute()) {
                $_SESSION['username'] = $newUsername;
                $curUsername = $newUsername;
                $curEmail    = $newEmail;
                $success     = "Profile updated successfully.";
            } else {
                $error = "An error occurred. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PWD-Carmona – Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="">PWD_CARMONA CITY</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <?php if ($_SESSION['role'] != 2): ?>
                            <a class="nav-link" href="new.php">
                                <div class="sb-nav-link-icon"><i class="fa fa-graduation-cap"></i></div>
                                New Enrollees
                            </a>
                        <?php endif; ?>
                        <a class="nav-link" href="masterlist.php">
                            <div class="sb-nav-link-icon"><i class="fa fa-list"></i></div>
                            Master List
                        </a>
                        <?php if ($_SESSION['role'] != 2): ?>
                            <a class="nav-link" href="schedule.php">
                                <div class="sb-nav-link-icon"><i class="fa fa-calendar"></i></div>
                                Schedule
                            </a>
                            <a class="nav-link" href="announcement.php">
                                <div class="sb-nav-link-icon"><i class="fa fa-bullhorn"></i></div>
                                Announcement
                            </a>
                        <?php endif; ?>
                        <a class="nav-link" href="report.php">
                            <div class="sb-nav-link-icon"><i class="fa fa-bar-chart"></i></div>
                            Report
                        </a>
                        <?php if ($_SESSION['role'] != 2): ?>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                                data-bs-target="#collapseEdit" aria-expanded="false" aria-controls="collapseEdit">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-edit"></i></div>
                                Learning Assessment
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseEdit" aria-labelledby="headingEdit"
                                data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link edit-section-link" href="ls/intervention/intervention.php">Intervention</a>
                                    <a class="nav-link edit-section-link" href="ls/inclusion/inclusion.php">Inclusions</a>
                                    <a class="nav-link edit-section-link" href="ls/senior/sh.php">Senior High</a>
                                    <a class="nav-link edit-section-link" href="ls/college/college.php">College</a>
                                </nav>
                            </div>
                            <div class="sb-sidenav-menu-heading">Users</div>
                            <a class="nav-link" href="user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                User Management
                            </a>
                        <?php endif; ?>
                        <div class="sb-sidenav-menu-heading">Account</div>
                        <a class="nav-link active" href="settings.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                            Settings
                        </a>
                        <a class="nav-link" href="../php/logout.php?id=<?php echo $_SESSION['user_id']; ?>&role=<?php echo $_SESSION['role']; ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            Logout
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <section class="px-4 py-4 mx-auto" style="max-width:760px;" aria-label="Settings panel">

                    <h2 class="mb-4">Settings</h2>

                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="material-icons align-middle me-1" style="font-size:1.1rem;">check_circle</i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="material-icons align-middle me-1" style="font-size:1.1rem;">error</i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold">
                            <i class="fas fa-user-edit me-2 text-muted"></i>Edit Profile
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username"
                                            value="<?= htmlspecialchars($curUsername) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= htmlspecialchars($curEmail) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" readonly
                                            value="<?= $curRole === 0 ? 'Administrator' : ($curRole === 2 ? 'Principal' : '—') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Joined</label>
                                        <input type="text" class="form-control" readonly
                                            value="<?= !empty($curCreatedAt) ? date('M d, Y', strtotime($curCreatedAt)) : '—' ?>">
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold">
                            <i class="fas fa-lock me-2 text-muted"></i>Change Password
                        </div>
                        <div class="card-body">
                            <form method="POST" id="pwForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" placeholder="Enter current password"
                                            autocomplete="current-password" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-pw"
                                            data-target="current_password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="new_password"
                                                name="new_password" placeholder="Minimum 8 characters"
                                                autocomplete="new-password" required
                                                oninput="checkStrength(this.value); checkMatch();">
                                            <button type="button" class="btn btn-outline-secondary toggle-pw"
                                                data-target="new_password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="mt-1">
                                            <div class="progress" style="height:4px;">
                                                <div class="progress-bar" id="strengthBar" style="width:0;transition:width .3s;"></div>
                                            </div>
                                            <small id="strengthLabel" class="text-muted"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password"
                                                name="confirm_password" placeholder="Re-enter new password"
                                                autocomplete="new-password" required
                                                oninput="checkMatch();">
                                            <button type="button" class="btn btn-outline-secondary toggle-pw"
                                                data-target="confirm_password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <small id="matchLabel"></small>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                                    <button type="submit" name="change_password" class="btn btn-warning text-dark">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </section>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PWD Carmona 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script>
        document.querySelectorAll('.toggle-pw').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        function checkStrength(val) {
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const map = [{
                    w: '0%',
                    cls: '',
                    label: ''
                },
                {
                    w: '25%',
                    cls: 'bg-danger',
                    label: 'Weak'
                },
                {
                    w: '50%',
                    cls: 'bg-warning',
                    label: 'Fair'
                },
                {
                    w: '75%',
                    cls: 'bg-info',
                    label: 'Good'
                },
                {
                    w: '100%',
                    cls: 'bg-success',
                    label: 'Strong'
                },
            ];
            const bar = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            bar.style.width = map[score].w;
            bar.className = 'progress-bar ' + map[score].cls;
            label.textContent = map[score].label;
        }

        function checkMatch() {
            const np = document.getElementById('new_password').value;
            const cp = document.getElementById('confirm_password').value;
            const lbl = document.getElementById('matchLabel');
            if (!cp) {
                lbl.textContent = '';
                return;
            }
            if (np === cp) {
                lbl.textContent = '✓ Passwords match';
                lbl.style.color = '#198754';
            } else {
                lbl.textContent = '✗ Passwords do not match';
                lbl.style.color = '#dc3545';
            }
        }

        document.getElementById('pwForm').addEventListener('submit', function(e) {
            const np = document.getElementById('new_password').value;
            const cp = document.getElementById('confirm_password').value;
            if (np !== cp) {
                e.preventDefault();
                checkMatch();
            }
        });
    </script>
</body>

</html>