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

$itemsPerPage = 10;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$searchTerm = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
  $searchTerm = trim($_GET['search']);
  $searchQuery = mysqli_real_escape_string($conn, $searchTerm);
  $whereClause = "WHERE (lrn_no LIKE '%$searchQuery%' 
                    OR CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE '%$searchQuery%'
                    OR contact_no LIKE '%$searchQuery%'
                    OR address LIKE '%$searchQuery%'
                    OR barangay LIKE '%$searchQuery%'
                    OR previous_level LIKE '%$searchQuery%'
                    OR educational_attainment LIKE '%$searchQuery%'
                    OR disability_type LIKE '%$searchQuery%'
                    OR specific_disability LIKE '%$searchQuery%')";
} else {
  $whereClause = '';
}

$countSql = "SELECT COUNT(*) as total FROM enrollees $whereClause";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$totalItems = $countRow['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

$currentPage = min($currentPage, max(1, $totalPages));
$offset = ($currentPage - 1) * $itemsPerPage;

$sql = "SELECT * FROM enrollees $whereClause ORDER BY enrollee_id DESC LIMIT $itemsPerPage OFFSET $offset";
$student_result = mysqli_query($conn, $sql);

$all_enrollees = [];
while ($row = mysqli_fetch_assoc($student_result)) {
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
  <link href="css/styles.css" rel="stylesheet" />
  <link href="css/user.css" rel="stylesheet" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

  <style>
    @page {
      size: 210mm 148.5mm;
      margin: 0;
    }

    button {
      padding: 10px 20px;
      background: #0f7f2d;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    .id-modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      z-index: 9999;
      overflow-y: auto;
    }

    .id-modal-box {
      background: white;
      margin: 4% auto;
      padding: 20px;
      width: 95%;
      max-width: 900px;
      border-radius: 10px;
      position: relative;
    }

    .id-modal-box h2 {
      margin-bottom: 16px;
      font-size: 20px;
    }

    .id-modal-close {
      position: absolute;
      right: 15px;
      top: 10px;
      font-size: 25px;
      cursor: pointer;
      color: #333;
      line-height: 1;
      background: none;
      border: none;
      padding: 0;
    }

    .id-modal-close:hover {
      color: #000;
    }

    .id-wrapper {
      display: flex;
      justify-content: center;
      gap: 40px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .id-card {
      width: 53.98mm;
      height: 85.60mm;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      box-sizing: border-box;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      border: 2px solid #0f7f2d;
      font-family: Arial, sans-serif;
    }

    .front {
      background: linear-gradient(160deg, #0f7f2d 50%, #ffffff 35%);
      height: 85.60mm;
      padding: 3mm 4mm;
      box-sizing: border-box;
      text-align: center;
    }

    .front .logo {
      color: white;
      font-size: 9px;
      font-weight: bold;
    }

    .logo-container {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 5mm;
      margin-bottom: 2mm;
    }

    .logo-container img {
      width: 12mm;
      height: auto;
    }

    .front .photo {
      width: 28mm;
      height: 32mm;
      background: #e0e0e0;
      margin: 4mm auto;
      border-radius: 6px;
      border: 2px solid yellow;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .front .photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .front .details {
      font-size: 9px;
      margin-top: 1mm;
    }

    .front .details strong {
      display: block;
      font-size: 12px;
      color: #0f7f2d;
    }

    .teacher-sig {
      margin-top: 3mm;
      text-align: center;
      font-size: 9px;
      padding: 0 4mm;
    }

    .teacher-name-val {
      font-size: 9px;
      color: #222;
      min-height: 3.5mm;
      line-height: 1.2;
      margin-bottom: 0.5mm;
    }

    .teacher-sig-line {
      border-top: 1px solid #444;
      width: 100%;
      margin: 0 auto 1mm;
    }

    .teacher-sig-label {
      font-size: 8px;
      color: #444;
    }

    .back {
      background: linear-gradient(160deg, #ffffff 75%, #0f7f2d 30%);
      height: 85.60mm;
      padding: 6mm 5mm;
      box-sizing: border-box;
      font-size: 9px;
      text-align: left;
    }

    .back .section {
      margin-bottom: 3mm;
    }

    .back .title {
      font-weight: bold;
    }

    .back ul {
      padding-left: 14px;
      margin: 2mm 0;
    }

    .back ul li {
      margin-bottom: 2mm;
    }

    .signature {
      margin-top: 6mm;
      text-align: center;
      font-size: 8px;
    }

    .id-action-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 4px;
    }

    .id-action-row button {
      padding: 8px 22px;
      font-size: 14px;
      border-radius: 6px;
    }

    .btn-pdf-id {
      background: #0d6efd;
    }

    .btn-cancel-id {
      background: #6c757d;
    }

    @media print {
      body * {
        visibility: hidden;
      }

      #idPrintArea,
      #idPrintArea * {
        visibility: visible;
      }

      #idPrintArea {
        position: fixed;
        top: 10mm;
        left: 10mm;
        display: flex !important;
        gap: 10mm;
      }

      .id-card {
        box-shadow: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .front,
      .back {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
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
            <a class="nav-link" href="index.php">
              <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link" href="new.php">
                <div class="sb-nav-link-icon"><i class="fa fa-graduation-cap"></i></div>New Enrollees
              </a>
            <?php endif; ?>
            <a class="nav-link active" href="masterlist.php">
              <div class="sb-nav-link-icon"><i class="fa fa-list"></i></div>Master List
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link" href="schedule.php">
                <div class="sb-nav-link-icon"><i class="fa fa-calendar"></i></div>Schedule
              </a>
              <a class="nav-link" href="announcement.php">
                <div class="sb-nav-link-icon"><i class="fa fa-bullhorn"></i></div>Announcement
              </a>
            <?php endif; ?>
            <a class="nav-link" href="report.php">
              <div class="sb-nav-link-icon"><i class="fa fa-bar-chart"></i></div>Report
            </a>
            <?php if ($_SESSION['role'] != 2): ?>
              <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseEdit" aria-expanded="false" aria-controls="collapseEdit">
                <div class="sb-nav-link-icon"><i class="fas fa-user-edit"></i></div>Learning Assessment
                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
              </a>
              <div class="collapse" id="collapseEdit" aria-labelledby="headingEdit" data-bs-parent="#sidenavAccordion">
                <nav class="sb-sidenav-menu-nested nav">
                  <a class="nav-link" href="ls/intervention/intervention.php">Intervention</a>
                  <a class="nav-link" href="ls/inclusion/inclusion.php">Inclusions</a>
                  <a class="nav-link" href="ls/senior/sh.php">Senior High</a>
                  <a class="nav-link" href="ls/college/college.php">College</a>
                </nav>
              </div>
              <div class="sb-sidenav-menu-heading">Users</div>
              <a class="nav-link" href="user.php">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>User Management
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
        <section class="admin-panel" aria-label="Masterlist panel">
          <div class="d-flex align-items-center mb-3">
            <h2 class="m-0">Master List</h2>
          </div>

          <form method="GET" class="mb-3">
            <div class="input-group">
              <input type="text" name="search" class="form-control"
                placeholder="Search by LRN, Name, Contact, Address, Barangay, Disability, or Grade Level..."
                value="<?= htmlspecialchars($searchTerm) ?>" aria-label="Search enrollees">
              <button class="btn btn-outline-primary" type="submit">
                <i class="material-icons">search</i> Search
              </button>
              <?php if (!empty($searchTerm)): ?>
                <a href="masterlist.php" class="btn btn-outline-secondary">
                  <i class="material-icons">clear</i> Clear
                </a>
              <?php endif; ?>
            </div>
          </form>

          <?php if (!empty($searchTerm)): ?>
            <div class="alert alert-info" role="alert">
              Search results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>" — Found <strong><?= $totalItems ?></strong> enrollee(s)
              <a href="masterlist.php" class="ms-2">View all</a>
            </div>
          <?php endif; ?>

          <div class="table-responsive">
            <table class="table table-hover table-borderless shadow-sm mb-0 custom-table">
              <thead class="bg-light text-center">
                <tr>
                  <th class="text-muted py-3">LRN No.</th>
                  <th class="text-muted py-3">Name</th>
                  <th class="text-muted py-3">Contact No.</th>
                  <th class="text-muted py-3">Address</th>
                  <th class="text-muted py-3">Barangay</th>
                  <th class="text-muted py-3 text-center">Action</th>
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
                      <td class="align-middle">
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['enrollee_id'] ?>">
                          View
                        </button>
                        <?php if ($_SESSION['role'] != 2): ?>
                          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['enrollee_id'] ?>">
                            Edit
                          </button>
                          <button class="btn btn-secondary btn-sm"
                            onclick="openIdModal(<?= htmlspecialchars(json_encode([
                                                    'id'           => $row['lrn_no'] ?? '',
                                                    'name'         => $row['first_name'] . ' ' . $row['last_name'],
                                                    'program'      => (function ($ea, $pl) {
                                                      $ea = strtoupper(trim($ea ?? ''));
                                                      $pl = trim($pl ?? '');
                                                      $map = [
                                                        'ALS'               => 'Alternative Learning System',
                                                        'PALIGAWAN'         => 'Paligawan Program',
                                                        'TRANSITION PROGRAM' => 'Transition Program',
                                                        'CARER PROGRAM'     => $pl ? $pl : 'Carer Program',
                                                        'JUNIOR HIGH SCHOOL' => $pl ? $pl . ' - Junior High School' : 'Junior High School',
                                                        'SENIOR HIGH SCHOOL' => $pl ? $pl . ' - Senior High School' : 'Senior High School',
                                                        'COLLEGE'           => $pl ? ucwords(strtolower($pl)) . ' - College' : 'College',
                                                      ];
                                                      return $map[$ea] ?? ($pl ? $pl : $ea);
                                                    })($row['educational_attainment'] ?? '', $row['previous_level'] ?? ''),
                                                    'teacher_name' => $row['teacher_name'] ?? '',
                                                    'address'      => $row['address'] ?? '',
                                                    'birthdate'    => (!empty($row['birthday']) && $row['birthday'] !== '0000-00-00')
                                                      ? date('M j, Y', strtotime($row['birthday'])) : '',
                                                    'age'          => $row['age'] ?? '',
                                                    'mother_name'  => $row['guardian_name'] ?? '',
                                                    'phone'        => $row['guardian_contact'] ?? '',
                                                    'image'        => $row['photo'] ?? '',
                                                  ]), ENT_QUOTES) ?>)">
                            ID
                          </button>
                          <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteId(<?= $row['enrollee_id'] ?>)">
                            Delete
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="material-icons" style="font-size:48px;color:#ccc;">search_off</i>
                      <p class="text-muted mt-2">No enrollees found<?= !empty($searchTerm) ? ' matching your search.' : '.' ?></p>
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
                  <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">Previous</a>
                </li>
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage   = min($totalPages, $currentPage + 2);
                if ($startPage > 1): ?>
                  <li class="page-item"><a class="page-link" href="?page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">1</a></li>
                  <?php if ($startPage > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                  <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <?php if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                  <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">Next</a>
                </li>
              </ul>
            </nav>
            <div class="text-center text-muted mb-3">
              Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong>
              (Total: <strong><?= $totalItems ?></strong> enrollee<?= $totalItems !== 1 ? 's' : '' ?>)
            </div>
          <?php endif; ?>

          <!-- VIEW MODALS -->
          <?php foreach ($all_enrollees as $row): ?>
            <div class="modal fade" id="viewModal<?= $row['enrollee_id'] ?>" tabindex="-1" aria-hidden="true">
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
                          <div class="col-md-4"><label class="form-label text-muted small">LRN No.</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['lrn_no'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Date of Application</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['application_date'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">PWD ID</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['pwd_id'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Last Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['last_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">First Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['first_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Middle Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['middle_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Age</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['age'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Birthday</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['birthday'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Sex</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['sex'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Disability/Diagnosis</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['disability_type'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Congenital/Inborn</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['disability_cause'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Specific Disability</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['specific_disability'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-3"><label class="form-label text-muted small">Civil Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['civil_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6"><label class="form-label text-muted small">Address</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['address'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3"><label class="form-label text-muted small">Barangay</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['barangay'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-6"><label class="form-label text-muted small">Contact No.</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['contact_no'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6"><label class="form-label text-muted small">Educational Attainment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['educational_attainment'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Employment Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Category of Employment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_category'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Nature of Employment</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['employment_nature'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-6"><label class="form-label text-muted small">Occupation</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['occupation'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6"><label class="form-label text-muted small">COVID 19 Vaccine</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['covid_vaccinated'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-6"><label class="form-label text-muted small">Father Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['father_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6"><label class="form-label text-muted small">Mother Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['mother_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Previous Level/Grade</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['previous_level'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Parent Status</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['parent_status'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">No. of Siblings</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['siblings_count'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-3"><label class="form-label text-muted small">Working in Family</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['working_family_members'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3"><label class="form-label text-muted small">Monthly Income</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['monthly_income_head'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3"><label class="form-label text-muted small">Total Family Income</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['total_family_income'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-3"><label class="form-label text-muted small">Family Type</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['family_type'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-6"><label class="form-label text-muted small">Comelec Registered</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['comelec_registered'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-6"><label class="form-label text-muted small">4Ps Member</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['four_ps_member'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-4"><label class="form-label text-muted small">Guardian</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['guardian_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">Guardian Contact</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['guardian_contact'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                          <div class="col-md-4"><label class="form-label text-muted small">House Tagging</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['house_tagging'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                        <div class="row g-2 mb-2">
                          <div class="col-md-12"><label class="form-label text-muted small">Teacher's Name</label>
                            <div class="form-control bg-light min-height-field"><?= htmlspecialchars($row['teacher_name'] ?? '') ?: '&nbsp;' ?></div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="border rounded p-3 text-center d-flex flex-column align-items-center justify-content-center" style="min-height:350px;">
                          <?php if (!empty($row['photo'])): ?>
                            <img src="<?= htmlspecialchars($row['photo']) ?>" class="img-fluid rounded" style="max-height:320px;object-fit:cover;" alt="Enrollee Photo">
                          <?php else: ?>
                            <i class="material-icons" style="font-size:64px;color:#ccc;">no_photography</i>
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

          <!-- EDIT MODALS -->
          <?php if ($_SESSION['role'] != 2): ?>
            <?php foreach ($all_enrollees as $row): ?>
              <div class="modal fade" id="editModal<?= $row['enrollee_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Enrollee: <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form enctype="multipart/form-data" method="POST" action="php/edit_student.php">
                      <input type="hidden" name="enrollee_id" value="<?= $row['enrollee_id'] ?>">
                      <div class="modal-body">
                        <div class="row g-3">
                          <div class="col-md-8">
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="lrn_no" placeholder="LRN No." value="<?= htmlspecialchars($row['lrn_no'] ?? '') ?>"><label>LRN No.</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="date" class="form-control" name="date_application" value="<?= !empty($row['application_date']) && $row['application_date'] !== '0000-00-00' ? date('Y-m-d', strtotime($row['application_date'])) : '' ?>"><label>Date Application</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="pwd_id" placeholder="PWD ID" value="<?= htmlspecialchars($row['pwd_id'] ?? '') ?>"><label>PWD ID</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($row['last_name'] ?? '') ?>"><label>Last Name</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($row['first_name'] ?? '') ?>"><label>First Name</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="<?= htmlspecialchars($row['middle_name'] ?? '') ?>"><label>Middle Name</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="age" placeholder="Age" value="<?= htmlspecialchars($row['age'] ?? '') ?>"><label>Age</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="date" class="form-control" name="birthday" value="<?= !empty($row['birthday']) && $row['birthday'] !== '0000-00-00' ? date('Y-m-d', strtotime($row['birthday'])) : '' ?>"><label>Birthday</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="sex" placeholder="Sex" value="<?= htmlspecialchars($row['sex'] ?? '') ?>"><label>Sex</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="disability_diagnosis" placeholder="Disability/Diagnosis" value="<?= htmlspecialchars($row['disability_type'] ?? '') ?>"><label>Disability/Diagnosis</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="congenital_inborn" placeholder="Congenital/Inborn" value="<?= htmlspecialchars($row['disability_cause'] ?? '') ?>"><label>Congenital/Inborn</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="acquired" placeholder="Acquired" value=""><label>Acquired</label></div>
                              </div>
                            </div>
                            <div class="form-floating mb-2"><input type="text" class="form-control" name="specific_disability" placeholder="Specific Disability" value="<?= htmlspecialchars($row['specific_disability'] ?? '') ?>"><label>Specific Disability</label></div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="civil_status" placeholder="Civil Status" value="<?= htmlspecialchars($row['civil_status'] ?? '') ?>"><label>Civil Status</label></div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-floating"><input type="text" class="form-control" name="complete_address" placeholder="Complete Address" value="<?= htmlspecialchars($row['address'] ?? '') ?>"><label>Complete Address</label></div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="barangay" placeholder="Barangay" value="<?= htmlspecialchars($row['barangay'] ?? '') ?>"><label>Barangay</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-6">
                                <div class="form-floating"><input type="text" class="form-control" name="contact_no" placeholder="Contact No." value="<?= htmlspecialchars($row['contact_no'] ?? '') ?>"><label>Contact No.</label></div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-floating">
                                  <select class="form-select" id="edit_education_type_<?= $row['enrollee_id'] ?>" name="education_type" onchange="updateEditPreviousLevel(<?= $row['enrollee_id'] ?>)">
                                    <option value="" disabled>Select Educational Attainment</option>
                                    <?php foreach (['CARER PROGRAM', 'JUNIOR HIGH SCHOOL', 'SENIOR HIGH SCHOOL', 'COLLEGE', 'TRANSITION PROGRAM', 'ALS', 'PALIGAWAN'] as $opt): ?>
                                      <option value="<?= $opt ?>" <?= $row['educational_attainment'] == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                  </select>
                                  <label>Educational Attainment</label>
                                </div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="employment_status" placeholder="Employment Status" value="<?= htmlspecialchars($row['employment_status'] ?? '') ?>"><label>Employment Status</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="category_of_employment" placeholder="Category of Employment" value="<?= htmlspecialchars($row['employment_category'] ?? '') ?>"><label>Category of Employment</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="nature_of_employment" placeholder="Nature of Employment" value="<?= htmlspecialchars($row['employment_nature'] ?? '') ?>"><label>Nature of Employment</label></div>
                              </div>
                            </div>
                            <div class="form-floating mb-2"><input type="text" class="form-control" name="covid_vaccine" placeholder="COVID 19 Vaccine" value="<?= htmlspecialchars($row['covid_vaccinated'] ?? '') ?>"><label>COVID 19 Vaccine</label></div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="occupation" placeholder="Occupation" value="<?= htmlspecialchars($row['occupation'] ?? '') ?>"><label>Occupation</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="father_name" placeholder="Father Name" value="<?= htmlspecialchars($row['father_name'] ?? '') ?>"><label>Father Name</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="mother_name" placeholder="Mother Name" value="<?= htmlspecialchars($row['mother_name'] ?? '') ?>"><label>Mother Name</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating">
                                  <select class="form-select" id="edit_previous_level_<?= $row['enrollee_id'] ?>" name="previous_level" data-current="<?= htmlspecialchars($row['previous_level'] ?? '') ?>">
                                    <option value="<?= htmlspecialchars($row['previous_level'] ?? '') ?>" selected><?= htmlspecialchars($row['previous_level'] ?? 'Select Previous Level') ?></option>
                                  </select>
                                  <label>Previous Level/Grade</label>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="parent_status" placeholder="Parent Status" value="<?= htmlspecialchars($row['parent_status'] ?? '') ?>"><label>Parent Status</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="siblings_no" placeholder="No. of Siblings" value="<?= htmlspecialchars($row['siblings_count'] ?? '') ?>"><label>No. of Siblings</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="working_family_no" placeholder="Working in Family" value="<?= htmlspecialchars($row['working_family_members'] ?? '') ?>"><label>Working in Family</label></div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="monthly_income_head" placeholder="Monthly Income" value="<?= htmlspecialchars($row['monthly_income_head'] ?? '') ?>"><label>Monthly Income</label></div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="total_family_income" placeholder="Total Family Income" value="<?= htmlspecialchars($row['total_family_income'] ?? '') ?>"><label>Family Income</label></div>
                              </div>
                              <div class="col-md-3">
                                <div class="form-floating"><input type="text" class="form-control" name="family_type" placeholder="Family Type" value="<?= htmlspecialchars($row['family_type'] ?? '') ?>"><label>Family Type</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-6">
                                <div class="form-floating"><input type="text" class="form-control" name="comelec_registered" placeholder="Comelec Registered" value="<?= htmlspecialchars($row['comelec_registered'] ?? '') ?>"><label>Comelec Registered</label></div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-floating"><input type="text" class="form-control" name="four_ps_member" placeholder="4Ps Member" value="<?= htmlspecialchars($row['four_ps_member'] ?? '') ?>"><label>4Ps Member</label></div>
                              </div>
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="guardian" placeholder="Guardian" value="<?= htmlspecialchars($row['guardian_name'] ?? '') ?>"><label>Guardian</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="guardian_contact" placeholder="Guardian Contact" value="<?= htmlspecialchars($row['guardian_contact'] ?? '') ?>"><label>Guardian Contact</label></div>
                              </div>
                              <div class="col-md-4">
                                <div class="form-floating"><input type="text" class="form-control" name="house_tagging" placeholder="House Tagging" value="<?= htmlspecialchars($row['house_tagging'] ?? '') ?>"><label>House Tagging</label></div>
                              </div>
                            </div>
                            <div class="form-floating mb-2"><input type="text" class="form-control" name="teacher_name" placeholder="Teacher Name" value="<?= htmlspecialchars($row['teacher_name'] ?? '') ?>"><label>Teacher Name</label></div>
                          </div>
                          <div class="col-md-4">
                            <div class="border rounded p-3 text-center h-50 d-flex flex-column justify-content-center">
                              <img id="imgPreviewEdit<?= $row['enrollee_id'] ?>"
                                src="<?= $row['photo'] ? htmlspecialchars($row['photo']) : 'https://via.placeholder.com/300x350' ?>"
                                class="img-fluid rounded mb-5" alt="Photo">
                              <input type="file" class="form-control" name="photo" accept="image/*"
                                onchange="previewImage(event, 'imgPreviewEdit<?= $row['enrollee_id'] ?>')">
                              <small class="text-muted mt-2">Upload 2x2 or ID Photo (Optional)</small>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </section>
      </main>
      <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
          <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; PERSON WITH DISABILITY IN CARMONA CITY, CAVITE 2026</div>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- DELETE MODAL -->
  <?php if ($_SESSION['role'] != 2): ?>
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-1">
            <p>Are you sure you want to delete this enrollee? This action cannot be undone.</p>
            <input type="hidden" id="deleteEnrolleeId" value="">
          </div>
          <div class="modal-footer border-0 pt-3">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ID CARD MODAL — HTML preview unchanged -->
  <div id="idModal" class="id-modal-overlay">
    <div class="id-modal-box">
      <button class="id-modal-close" onclick="closeIdModal()">&times;</button>
      <h2>CR80 Portrait ID Preview</h2>

      <div class="id-wrapper" id="idPrintArea">

        <!-- FRONT -->
        <div class="id-card front" id="idFront">
          <div class="logo">
            <div class="logo-container">
              <img src="logo.png" alt="Logo 1">
              <img src="log.png" alt="Logo 2">
            </div>
            City Government of Carmona<br>
            Persons with Disability Affairs Office
          </div>
          <div class="photo">
            <img id="idPhotoImg" src="" alt="Photo" style="display:none;">
          </div>
          <div class="details">
            <strong id="idName">—</strong>
            ID No: <span id="idNo">—</span><br>
            <span id="idProgram">—</span>
          </div>
          <div class="teacher-sig">
            <div class="teacher-name-val" id="idTeacher">&nbsp;</div>
            <div class="teacher-sig-line"></div>
            <div class="teacher-sig-label">Teacher's Name</div>
          </div>
        </div>

        <!-- BACK -->
        <div class="id-card back" id="idBack">
          <div class="section">
            <span class="title">Address:</span><br>
            <span id="idAddress">—</span>
          </div>
          <div class="section">
            <span class="title">Birthday:</span> <span id="idBirthday">—</span><br>
            <span class="title">Age:</span> <span id="idAge">—</span>
          </div>
          <div class="section">
            <span class="title">In case of Emergency:</span><br>
            <span id="idGuardian">—</span><br>
            <span id="idGuardianContact">—</span>
          </div>
          <div class="section">
            <span class="title">Reminders:</span>
            <ul>
              <li>Wear this ID inside the school premises</li>
              <li>If found, please return to the Person with Disability Affairs Office - Carmona City</li>
            </ul>
          </div>
          <div class="signature">
            _______________________________<br>
            City Government Department Head - PDAO
          </div>
        </div>

      </div>

      <div class="id-action-row">
        <button class="btn-pdf-id" id="btnDownloadId" onclick="downloadIdCard()">
          <i class="fas fa-file-pdf me-1"></i> Download PDF
        </button>
        <button class="btn-cancel-id" onclick="closeIdModal()">
          <i class="fas fa-times me-1"></i> Close
        </button>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/scripts.js"></script>
  <script src="js/user.js"></script>

  <script>
   
    window.jsPDF = window.jspdf.jsPDF;

    let doc;

    const EMPTY_IMAGE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    async function getDataUri(url, dWidth, dHeight) {
      return new Promise((resolve) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';

        image.onload = function() {
          const canvas = document.createElement('canvas');
          canvas.width = dWidth;
          canvas.height = dHeight;
          const ctx = canvas.getContext('2d');

          const radius = 40;

          ctx.beginPath();
          ctx.moveTo(radius, 0);
          ctx.lineTo(dWidth - radius, 0);
          ctx.quadraticCurveTo(dWidth, 0, dWidth, radius);
          ctx.lineTo(dWidth, dHeight - radius);
          ctx.quadraticCurveTo(dWidth, dHeight, dWidth - radius, dHeight);
          ctx.lineTo(radius, dHeight);
          ctx.quadraticCurveTo(0, dHeight, 0, dHeight - radius);
          ctx.lineTo(0, radius);
          ctx.quadraticCurveTo(0, 0, radius, 0);
          ctx.closePath();
          ctx.clip();

          const hRatio = dWidth / this.naturalWidth;
          const vRatio = dHeight / this.naturalHeight;
          const ratio = Math.max(hRatio, vRatio);
          const newWidth = this.naturalWidth * ratio;
          const newHeight = this.naturalHeight * ratio;
          const x = (dWidth - newWidth) / 2;
          const y = (dHeight - newHeight) / 2;
          ctx.drawImage(this, x, y, newWidth, newHeight);

          ctx.lineWidth = 8;
          ctx.strokeStyle = '#ffff00';
          ctx.beginPath();
          ctx.moveTo(radius, 0);
          ctx.lineTo(dWidth - radius, 0);
          ctx.quadraticCurveTo(dWidth, 0, dWidth, radius);
          ctx.lineTo(dWidth, dHeight - radius);
          ctx.quadraticCurveTo(dWidth, dHeight, dWidth - radius, dHeight);
          ctx.lineTo(radius, dHeight);
          ctx.quadraticCurveTo(0, dHeight, 0, dHeight - radius);
          ctx.lineTo(0, radius);
          ctx.quadraticCurveTo(0, 0, radius, 0);
          ctx.closePath();
          ctx.stroke();

          resolve(canvas.toDataURL('image/png'));
        };

        image.onerror = () => resolve(EMPTY_IMAGE);
        image.src = url;
      });
    }

    function downloadIdCard() {
      if (doc) {
        const name = document.getElementById('idName').textContent.trim();
        const safeName = name.replace(/[^a-z0-9]/gi, '_');
        doc.save('ID_Card_' + safeName + '.pdf');
      }
    }

    async function generateIdCard(data) {
      const cardW = 2.125;
      const cardH = 3.375;
      const gap = 0.25;
      const marginX = 0.5;
      const marginY = 0.5;
      const pageW = cardW * 2 + gap + marginX * 2;
      const pageH = cardH + marginY * 2;

      doc = new jsPDF({
        orientation: 'landscape',
        unit: 'in',
        format: [pageW, pageH],
      });

      for (const [i, student] of data.entries()) {
        if (i > 0) doc.addPage();

        const frontX = marginX;
        const backX = marginX + cardW + gap;
        const cardY = marginY;

        const fx = frontX;
        const fy = cardY;

        doc.setFillColor('#0f7f2d');
        doc.triangle(fx + 0, fy + 2.073, fx + 0, fy + 1, fx + 2.125, fy + 1.3, 'F');
        doc.roundedRect(fx + 0, fy + 0, 2.125, 1.3, 0.1, 0.1, 'F');
        doc.rect(fx + 0, fy + 1, 2.125, 0.3, 'F');

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(6.5);
        doc.setTextColor('#fff');
        doc.text('City Government of Carmona', fx + 1.0625, fy + 0.53, null, null, 'center');
        doc.text('Person with Disability Affairs Office', fx + 1.0625, fy + 0.667, null, null, 'center');

        doc.setFillColor('#ffffff');
        doc.roundedRect(fx + 0.513, fy + 0.93, 1.093, 1.253, 0.08, 0.8, 'F');
        const profileUri = await getDataUri(student.image, 450, 500);
        doc.addImage(profileUri, fx + 0.513, fy + 0.93, 1.093, 1.253);

        doc.setFont('helvetica', 'bold');
        doc.setFontSize(9);
        doc.setTextColor('#0f7f2d');
        doc.text(student.name, fx + 1.0625, fy + 2.458, null, null, 'center');

        doc.setFont('helvetica', 'normal');
        doc.setFontSize(6.8);
        doc.setTextColor('#000');
        doc.text('ID No: ' + student.id, fx + 1.0625, fy + 2.637, null, null, 'center');

        doc.setFontSize(7);
        doc.text(student.program, fx + 1.0625, fy + 2.771, null, null, 'center');

        doc.setFontSize(7.5);
        doc.text(student.teacher_name, fx + 1.0625, fy + 3.02, null, null, 'center');

        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.01);
        doc.line(fx + 0.33, fy + 3.087, fx + 1.8, fy + 3.087);

        doc.setFontSize(6.3);
        doc.text("Teacher's Name", fx + 1.0625, fy + 3.23, null, null, 'center');

        doc.setDrawColor('#000000');
        doc.setLineWidth(0.01);
        doc.roundedRect(fx + 0, fy + 0, 2.125, 3.375, 0.1, 0.1, 'S');

        const bx = backX;
        const by = cardY;

        doc.setFillColor('#0f7f2d');
        doc.triangle(bx + 0, by + 3.087, bx + 2.125, by + 3.087, bx + 2.125, by + 2.313, 'F');
        doc.roundedRect(bx + 0, by + 3.075, 2.125, 0.3, 0.1, 0.1, 'F');
        doc.rect(bx + 0, by + 3.086, 2.125, 0.2, 'F');

        doc.setDrawColor('#000000');
        doc.setLineWidth(0.01);
        doc.roundedRect(bx + 0, by + 0, 2.125, 3.375, 0.1, 0.1, 'S');

        const maxWidth = 2.125 - 0.2 - 0.2;

        doc.setFontSize(7);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor('#000');
        doc.text('Address:', bx + 0.2, by + 0.3);

        doc.setFont('helvetica', 'normal');
        const lines = doc.splitTextToSize(student.address, maxWidth);
        doc.text(lines, bx + 0.2, by + 0.45);

        doc.setFont('helvetica', 'bold');
        doc.text('Birthday:', bx + 0.2, by + 0.75);
        doc.setFont('helvetica', 'normal');
        doc.text(student.birthdate, bx + 0.66, by + 0.75);

        doc.setFont('helvetica', 'bold');
        doc.text('Age:', bx + 0.2, by + 0.9);
        doc.setFont('helvetica', 'normal');
        doc.text(student.age.toString(), bx + 0.45, by + 0.9);

        doc.setFont('helvetica', 'bold');
        doc.text('In case of Emergency:', bx + 0.2, by + 1.15);
        doc.setFont('helvetica', 'normal');
        doc.text(student.mother_name, bx + 0.2, by + 1.29);
        doc.text(student.phone, bx + 0.2, by + 1.42);

        doc.setFontSize(7.2);
        doc.setFont('helvetica', 'bold');
        doc.text('Reminders:', bx + 0.2, by + 1.65);

        doc.setFontSize(6.7);
        doc.setFont('helvetica', 'normal');
        doc.text('• Where this ID inside the school premises', bx + 0.2, by + 1.85);
        doc.text('• If found, please return to the Person with', bx + 0.2, by + 2.1);
        doc.text('Disability Affairs Office - Carmona City', bx + 0.255, by + 2.22);

        doc.setDrawColor(0, 0, 0);
        doc.setLineWidth(0.008);
        doc.line(bx + 0.33, by + 2.8, bx + 1.8, by + 2.8);

        doc.setFontSize(6);
        doc.text('City Government Department Head - PDAO', bx + 1.0625, by + 2.9, null, null, 'center');
      }
    }

   
    async function openIdModal(data) {
      document.getElementById('idName').textContent = data.name || '—';
      document.getElementById('idNo').textContent = data.id || '—';
      document.getElementById('idProgram').textContent = data.program || '—';
      document.getElementById('idTeacher').textContent = data.teacher_name || '';

      const photoEl = document.getElementById('idPhotoImg');
      if (data.image && data.image.trim() !== '') {
        photoEl.src = data.image;
        photoEl.style.display = 'block';
        photoEl.onerror = () => {
          photoEl.style.display = 'none';
        };
      } else {
        photoEl.style.display = 'none';
      }

      document.getElementById('idAddress').textContent = data.address || '—';
      document.getElementById('idBirthday').textContent = data.birthdate || '—';
      document.getElementById('idAge').textContent = data.age || '—';
      document.getElementById('idGuardian').textContent = data.mother_name || '—';
      document.getElementById('idGuardianContact').textContent = data.phone || '—';

      document.getElementById('idModal').style.display = 'block';

      const btn = document.getElementById('btnDownloadId');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';

      try {
        await generateIdCard([{
          id: data.id || '',
          name: data.name || '',
          program: data.program || '',
          teacher_name: data.teacher_name || '',
          address: data.address || '',
          birthdate: data.birthdate || '',
          age: data.age || '',
          mother_name: data.mother_name || '',
          phone: data.phone || '',
          image: data.image || '',
        }]);
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i> Download PDF';
      }
    }

    function closeIdModal() {
      document.getElementById('idModal').style.display = 'none';
    }

    window.addEventListener('click', function(e) {
      if (e.target === document.getElementById('idModal')) closeIdModal();
    });

    function setDeleteId(enrolleeId) {
      document.getElementById('deleteEnrolleeId').value = enrolleeId;
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
      const enrolleeId = document.getElementById('deleteEnrolleeId').value;
      fetch('php/delete_student.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'enrollee_id=' + enrolleeId
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            location.reload();
          } else alert('Error: ' + data.message);
        })
        .catch(err => {
          console.error(err);
          alert('An error occurred.');
        });
    });

    function previewImage(event, imgId = 'imgPreview') {
      const reader = new FileReader();
      reader.onload = () => {
        document.getElementById(imgId).src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }

    function updateEditPreviousLevel(id) {
      const type = document.getElementById('edit_education_type_' + id).value;
      const level = document.getElementById('edit_previous_level_' + id);
      const saved = level.dataset.current;

      level.innerHTML = '<option value="" selected disabled>Select Previous Level/Grade</option>';
      level.disabled = false;

      const options = {
        'CARER PROGRAM': [
          'EARLY INTERVENTION (1 on 1)', "EARLY INTERVENTION (by 2's)", 'GROUP TUTORIAL',
          'ADAPTIVE SKILLS PROGRAM II', 'ADAPTIVE SKILLS PROGRAM III', 'ADAPTIVE SKILLS PROGRAM IV',
          'ON THE JOB TRAINING (OJT)', 'HOME PROGRAM', 'TUTORIAL PROGRAM', 'DEPED SPED/SNED'
        ],
        'JUNIOR HIGH SCHOOL': Array.from({
          length: 10
        }, (_, i) => 'Grade ' + (i + 1)),
        'SENIOR HIGH SCHOOL': ['Grade 11', 'Grade 12'],
        'COLLEGE': ['1ST YEAR', '2ND YEAR', '3RD YEAR', '4TH YEAR']
      };

      if (options[type]) {
        options[type].forEach(item => {
          const opt = document.createElement('option');
          opt.value = item;
          opt.text = item;
          level.appendChild(opt);
        });
        if (saved) level.value = saved;
      } else {
        level.disabled = true;
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      <?php foreach ($all_enrollees as $row): ?>
        <?php if ($_SESSION['role'] != 2): ?>
          updateEditPreviousLevel(<?= $row['enrollee_id'] ?>);
        <?php endif; ?>
      <?php endforeach; ?>
    });
  </script>
</body>

</html>