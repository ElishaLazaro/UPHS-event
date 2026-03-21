<?php
session_start();
if (!$_SESSION['loggedin']) {
  header('Location: ../index.php');
  exit();
}
if ($_SESSION['role'] == 1) {
  header('Location: ../homepage.php');
  exit();
}

if ($_SESSION['role'] != 0) {
  header('Location: ../../index.php');
  exit();
}

include '../../../php/ConnectToDb.php';

// Pagination settings
$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Grade filter
$gradeFilter = isset($_GET['grade']) ? trim($_GET['grade']) : '';

// Search functionality
$searchTerm = '';
$conditions = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
  $searchTerm = trim($_GET['search']);
  $searchQuery = mysqli_real_escape_string($conn, $searchTerm);
  $conditions[] = "(lrn_no LIKE '%$searchQuery%'
                    OR CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE '%$searchQuery%'
                    OR contact_no LIKE '%$searchQuery%'
                    OR address LIKE '%$searchQuery%'
                    OR barangay LIKE '%$searchQuery%'
                    OR previous_level LIKE '%$searchQuery%'
                    OR educational_attainment LIKE '%$searchQuery%'
                    OR disability_type LIKE '%$searchQuery%'
                    OR specific_disability LIKE '%$searchQuery%')";
}

// Always filter to JUNIOR HIGH SCHOOL educational_attainment for inclusion
$conditions[] = "educational_attainment = 'JUNIOR HIGH SCHOOL'";

// Grade filter
$validGrades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
if (!empty($gradeFilter) && in_array($gradeFilter, $validGrades)) {
  $escapedGrade = mysqli_real_escape_string($conn, $gradeFilter);
  $conditions[] = "previous_level = '$escapedGrade'";
}

$whereClause = 'WHERE ' . implode(' AND ', $conditions);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM enrollees $whereClause";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$totalItems = $countRow['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$currentPage = min($currentPage, max(1, $totalPages));
$offset = ($currentPage - 1) * $itemsPerPage;

$sql = "SELECT * FROM enrollees $whereClause ORDER BY enrollee_id DESC LIMIT $itemsPerPage OFFSET $offset";
$result = mysqli_query($conn, $sql);

$all_enrollees = [];
while ($row = mysqli_fetch_assoc($result)) {
  $all_enrollees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PWD-Carmona</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link href="../../css/styles.css" rel="stylesheet" />
  <link href="../../css/user.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

  <style>
    .grade-nav-wrapper {
      background: #FFDE21;
      padding: 12px 16px;
      border-radius: 10px;
      overflow-x: auto;
    }

    .grade-nav {
      gap: 10px;
    }

    .grade-nav .nav-link {
      color: #fff;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 30px;
      padding: 8px 18px;
      font-weight: 600;
      white-space: nowrap;
      transition: all 0.3s ease;
    }

    .grade-nav .nav-link:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-1px);
    }

    .grade-nav .nav-link.active {
      background: #ffffff;
      color: #ffc107;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .min-height-field {
      min-height: 38px;
      line-height: 1.5;
      padding: 6px 12px;
    }
  </style>
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
            <a class="nav-link" href="../../index.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link" href="../../new.php">
                <div class="sb-nav-link-icon"><i class="fa fa-graduation-cap"></i></div>New Enrollees
              </a>
            <?php endif; ?>
            <a class="nav-link active" href="../../masterlist.php">
              <div class="sb-nav-link-icon"><i class="fa fa-list"></i></div>Master List
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link" href="../../schedule.php">
                <div class="sb-nav-link-icon"><i class="fa fa-calendar"></i></div>Schedule
              </a>
              <a class="nav-link" href="../../announcement.php">
                <div class="sb-nav-link-icon"><i class="fa fa-bullhorn"></i></div>Announcement
              </a>
            <?php endif; ?>
            <a class="nav-link" href="../../report.php">
              <div class="sb-nav-link-icon"><i class="fa fa-bar-chart"></i></div>Report
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseEdit" aria-expanded="false" aria-controls="collapseEdit">
                <div class="sb-nav-link-icon"><i class="fas fa-user-edit"></i></div>Learning Assessment
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
              </a>
              <div class="collapse" id="collapseEdit" aria-labelledby="headingEdit" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested nav">
                  <a class="nav-link" href="../../ls/intervention/intervention.php">Intervention</a>
                  <a class="nav-link" href="../../ls/inclusion/inclusion.php">Inclusions</a>
                  <a class="nav-link" href="../../ls/senior/sh.php">Senior High</a>
                  <a class="nav-link" href="../../ls/college/college.php">College</a>
                </nav>
              </div>
              <div class="sb-sidenav-menu-heading">Users</div>
              <a class="nav-link" href="../../user.php">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>User Management
              </a>
            <?php endif; ?>
            <div class="sb-sidenav-menu-heading">Account</div>
            <a class="nav-link active" href="../../settings.php">
              <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
              Settings
            </a>
            <a class="nav-link" href="../../../php/logout.php?id=<?php echo $_SESSION['user_id']; ?>&role=<?php echo $_SESSION['role']; ?>">
              <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
              Logout
            </a>
          </div>
        </div>
      </nav>
    </div>

    <div id="layoutSidenav_content">
      <main>
        <section class="admin-panel" aria-label="Inclusion panel">
          <div class="d-flex align-items-center mb-3">
            <h2 class="m-0">Inclusion</h2>
          </div>


          <form method="GET" class="mb-3">
            <?php if (!empty($gradeFilter)): ?>
              <input type="hidden" name="grade" value="<?= htmlspecialchars($gradeFilter) ?>">
            <?php endif; ?>
            <div class="input-group">
              <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search by LRN, Name, Contact, Address, or Barangay..."
                value="<?= htmlspecialchars($searchTerm) ?>"
                aria-label="Search enrollees">
              <button class="btn btn-outline-primary" type="submit">
                <i class="material-icons">search</i> Search
              </button>
              <?php if (!empty($searchTerm) || !empty($gradeFilter)): ?>
                <a href="inclusion.php" class="btn btn-outline-secondary">
                  <i class="material-icons">clear</i> Clear
                </a>
              <?php endif; ?>
            </div>
          </form>


          <div class="grade-nav-wrapper mb-3">
            <ul class="nav grade-nav flex-nowrap">
              <?php
              $grades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
              foreach ($grades as $grade):
                $isActive = ($gradeFilter === $grade);
                $href = '?grade=' . urlencode($grade) . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '');
              ?>
                <li class="nav-item">
                  <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $isActive ? 'inclusion.php' . (!empty($searchTerm) ? '?search=' . urlencode($searchTerm) : '') : $href ?>">
                    <?= htmlspecialchars($grade) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>


          <div class="table-responsive">
            <table class="table table-hover table-borderless shadow-sm mb-0 custom-table">
              <thead class="bg-light text-center">
                <tr>
                  <th scope="col" class="text-muted py-3">LRN No.</th>
                  <th scope="col" class="text-muted py-3">Name</th>
                  <th scope="col" class="text-muted py-3">Contact No.</th>
                  <th scope="col" class="text-muted py-3">Address</th>
                  <th scope="col" class="text-muted py-3">Barangay</th>
                  <th scope="col" class="text-muted py-3">Sex</th>
                  <th scope="col" class="text-muted py-3 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <?php if (count($all_enrollees) > 0): ?>
                  <?php foreach ($all_enrollees as $row): ?>
                    <tr>
                      <td class="align-middle"><?= htmlspecialchars($row['lrn_no']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($row['contact_no']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($row['address']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($row['barangay']) ?></td>
                      <td class="align-middle"><?= htmlspecialchars($row['sex']) ?></td>
                      <td class="align-middle">
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['enrollee_id'] ?>">
                          View
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="material-icons" style="font-size: 48px; color: #ccc;">search_off</i>
                      <p class="text-muted mt-2">No enrollees found<?= (!empty($searchTerm) || !empty($gradeFilter)) ? ' matching your filter.' : '.' ?></p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>


          <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
              <ul class="pagination justify-content-center">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($gradeFilter) ? '&grade=' . urlencode($gradeFilter) : '' ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">Previous</a>
                </li>

                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                $pageExtra = (!empty($gradeFilter) ? '&grade=' . urlencode($gradeFilter) : '') . (!empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '');

                if ($startPage > 1): ?>
                  <li class="page-item"><a class="page-link" href="?page=1<?= $pageExtra ?>">1</a></li>
                  <?php if ($startPage > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                  <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $pageExtra ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                  <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?><?= $pageExtra ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $pageExtra ?>">Next</a>
                </li>
              </ul>
            </nav>
            <div class="text-center text-muted mb-3">
              Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (Total: <strong><?= $totalItems ?></strong> enrollee<?= $totalItems !== 1 ? 's' : '' ?>)
            </div>
          <?php endif; ?>


          <?php foreach ($all_enrollees as $row): ?>
            <div class="modal fade" id="viewModal<?= $row['enrollee_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $row['enrollee_id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Enrollee: <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row g-3">

                      <div class="col-md-8">

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">LRN No.</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['lrn_no'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Date of Application</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['application_date'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">PWD ID</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['pwd_id'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Last Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['last_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">First Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['first_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Middle Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['middle_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Age</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['age'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Birthday</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['birthday'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Sex</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['sex'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Disability/Diagnosis</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['disability_type'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Congenital/Inborn</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['disability_cause'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Specific Disability</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['specific_disability'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Civil Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['civil_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Address</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['address'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Barangay</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['barangay'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Contact No.</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['contact_no'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Educational Attainment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['educational_attainment'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Employment Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Category of Employment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_category'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Nature of Employment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_nature'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Occupation</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['occupation'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label text-muted small">COVID 19 Vaccine</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['covid_vaccinated'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Father Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['father_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Mother Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['mother_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Previous Level/Grade</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['previous_level'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Parent Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['parent_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">No. of Siblings</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['siblings_count'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Working in Family</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['working_family_members'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Monthly Income</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['monthly_income_head'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Total Family Income</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['total_family_income'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label text-muted small">Family Type</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['family_type'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-6">
                            <label class="form-label text-muted small">Comelec Registered</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['comelec_registered'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label text-muted small">4Ps Member</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['four_ps_member'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Guardian</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['guardian_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">Guardian Contact</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['guardian_contact'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label text-muted small">House Tagging</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['house_tagging'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-md-12">
                            <label class="form-label text-muted small">Teacher's Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['teacher_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>

                      </div>


                      <div class="col-md-4">
                        <div class="border rounded p-3 text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 350px;">
                          <?php if (!empty($row['photo'])): ?>
                            <img src="../../<?= htmlspecialchars($row['photo']) ?>"
                              class="img-fluid rounded"
                              style="max-height: 320px; object-fit: cover;"
                              alt="Enrollee Photo">
                          <?php else: ?>
                            <i class="material-icons" style="font-size: 64px; color: #ccc;">no_photography</i>
                            <p class="text-muted mt-2 mb-0">No Image</p>
                          <?php endif; ?>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

        </section>
      </main>
      <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
          <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; Course Compass 2025</div>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../js/scripts.js"></script>
  <script src="../../js/user.js"></script>
</body>

</html>