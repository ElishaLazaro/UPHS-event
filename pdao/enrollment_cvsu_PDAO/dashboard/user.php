<?php
session_start();
if (!$_SESSION['loggedin']) {
    header('Location: ../index.php');
    exit();
}

if ($_SESSION['role'] != 0) {
    header('Location: index.php');
    exit();
}

include '../php/ConnectToDb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['approve_user'])) {
        $uid  = intval($_POST['user_id']);
        $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $_SESSION['success'] = "User approved successfully.";
        header('Location: user.php');
        exit();
    }

    if (isset($_POST['revoke_user'])) {
        $uid  = intval($_POST['user_id']);
        $stmt = $conn->prepare("UPDATE users SET is_approved = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $_SESSION['success'] = "User approval has been revoked.";
        header('Location: user.php');
        exit();
    }

    if (isset($_POST['delete_user'])) {
        $uid  = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $_SESSION['success'] = "User deleted successfully.";
        header('Location: user.php');
        exit();
    }

    if (isset($_POST['change_role']) && $_POST['change_role'] == '1') {
        $uid     = intval($_POST['user_id']);
        $newRole = intval($_POST['new_role']);
        if (in_array($newRole, [0, 2])) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $newRole, $uid);
            $stmt->execute();
            $_SESSION['success'] = "User role updated successfully.";
        }
        header('Location: user.php');
        exit();
    }
}

$searchTerm   = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery  = mysqli_real_escape_string($conn, $searchTerm);
$itemsPerPage = 10;
$currentPage  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$selfId       = intval($_SESSION['user_id']);

$searchWhere = !empty($searchTerm)
    ? "AND (username LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%')"
    : "";

$roleFilter = "AND role IN (0, 2)";

$countRow    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE user_id != $selfId $roleFilter $searchWhere"));
$totalItems  = $countRow['total'];
$totalPages  = max(1, ceil($totalItems / $itemsPerPage));
$currentPage = min($currentPage, $totalPages);
$offset      = ($currentPage - 1) * $itemsPerPage;

$result    = mysqli_query($conn, "SELECT * FROM users WHERE user_id != $selfId $roleFilter $searchWhere ORDER BY user_id DESC LIMIT $itemsPerPage OFFSET $offset");
$all_users = [];
while ($row = mysqli_fetch_assoc($result)) $all_users[] = $row;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PWD-Carmona – User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/user.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="">PWD_CARMONA CITY</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
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
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseEdit" aria-expanded="false" aria-controls="collapseEdit">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-edit"></i></div>
                                Learning Assessment
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseEdit" aria-labelledby="headingEdit" data-bs-parent="#sidenavAccordion">
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
                <section class="admin-panel" aria-label="User management panel">

                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="material-icons align-middle me-1" style="font-size:1.1rem;">check_circle</i>
                            <?= htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="material-icons align-middle me-1" style="font-size:1.1rem;">error</i>
                            <?= htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="m-0">User Management</h2>
                    </div>

                    
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search by name or email…"
                                value="<?= htmlspecialchars($searchTerm) ?>"
                                aria-label="Search users">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="material-icons">search</i> Search
                            </button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="user.php" class="btn btn-outline-secondary">
                                    <i class="material-icons">clear</i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    
                    <?php if (!empty($searchTerm)): ?>
                        <div class="alert alert-info" role="alert">
                            Search results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>" — Found <strong><?= $totalItems ?></strong> user(s)
                            <a href="user.php" class="ms-2">View all</a>
                        </div>
                    <?php endif; ?>

                    
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless shadow-sm mb-0 custom-table">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th scope="col" class="text-muted py-3">Username</th>
                                    <th scope="col" class="text-muted py-3">Email</th>
                                    <th scope="col" class="text-muted py-3">Role</th>
                                    <th scope="col" class="text-muted py-3">Approval Status</th>
                                    <th scope="col" class="text-muted py-3">Joined</th>
                                    <th scope="col" class="text-muted py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php if (count($all_users) > 0): ?>
                                    <?php foreach ($all_users as $u):
                                        $roleVal = (int)$u['role'];
                                    ?>
                                        <tr>
                                            <td class="align-middle fw-semibold"><?= htmlspecialchars($u['username']) ?></td>
                                            <td class="align-middle text-muted"><?= htmlspecialchars($u['email']) ?></td>
                                            <td class="align-middle">
                                                <form method="POST" class="d-flex justify-content-center" id="roleForm_<?= $u['user_id'] ?>">
                                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                    <input type="hidden" name="change_role" value="1">
                                                    <select name="new_role" class="form-select form-select-sm" style="width:120px;"
                                                        onchange="document.getElementById('roleForm_' + <?= $u['user_id'] ?>).submit()">
                                                        <option value="0" <?= $roleVal === 0 ? 'selected' : '' ?>>Admin</option>
                                                        <option value="2" <?= $roleVal === 2 ? 'selected' : '' ?>>Principal</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($u['is_approved']): ?>
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                                        <i class="material-icons" style="font-size:.75rem;vertical-align:middle;">check_circle</i>
                                                        Approved
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1">
                                                        <i class="material-icons" style="font-size:.75rem;vertical-align:middle;">hourglass_empty</i>
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle text-muted">
                                                <?= !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : '—' ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (!$u['is_approved']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                        <button type="submit" name="approve_user" class="btn btn-success btn-sm">
                                                            Approve
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-warning btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#revokeModal"
                                                        onclick="setRevokeId(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                                                        Revoke
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-danger btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    onclick="setDeleteId(<?= $u['user_id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="material-icons" style="font-size:48px;color:#ccc;">manage_accounts</i>
                                            <p class="text-muted mt-2">No users found<?= !empty($searchTerm) ? ' matching your search.' : '.' ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="User pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" <?= $currentPage <= 1 ? 'tabindex="-1"' : '' ?>>Previous</a>
                                </li>
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage   = min($totalPages, $currentPage + 2);
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $totalPages ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" <?= $currentPage >= $totalPages ? 'tabindex="-1"' : '' ?>>Next</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted mb-3">
                            Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong>
                            (Total: <strong><?= $totalItems ?></strong> user<?= $totalItems !== 1 ? 's' : '' ?>)
                        </div>
                    <?php endif; ?>

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

    
    <div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="revokeModalLabel">Confirm Revoke</h5>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-1">
                    <p>Are you sure you want to revoke approval for <strong id="revokeUserName"></strong>? They will no longer be able to log in.</p>
                </div>
                <div class="modal-footer border-0 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" id="revokeUserId">
                        <button type="submit" name="revoke_user" class="btn btn-warning rounded-pill px-4">Revoke</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-1">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" name="delete_user" class="btn btn-danger rounded-pill px-4">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/user.js"></script>
    <script>
        function setRevokeId(id, name) {
            document.getElementById('revokeUserId').value = id;
            document.getElementById('revokeUserName').textContent = name;
        }

        function setDeleteId(id, name) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;
        }
    </script>
</body>

</html>