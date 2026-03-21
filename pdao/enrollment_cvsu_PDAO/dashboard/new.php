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

$searchQuery = '';
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
$student_result = null;

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
          <div class="d-flex align-items-center mb-3">
            <h2 class="m-0">New Enrollees</h2>
            <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#enrollees">Add Enrollees</button>
          </div>

          
          <form method="GET" class="mb-3">
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
              <?php if (!empty($searchTerm)): ?>
                <a href="new.php" class="btn btn-outline-secondary">
                  <i class="material-icons">clear</i> Clear
                </a>
              <?php endif; ?>
            </div>
          </form>

          
          <?php if (!empty($searchTerm)): ?>
            <div class="alert alert-info" role="alert">
              Search results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>" - Found <strong><?= $totalItems ?></strong> enrollee(s)
              <a href="new.php" class="ms-2">View all</a>
            </div>
          <?php endif; ?>

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
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['enrollee_id'] ?>">
                          Edit
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteId(<?= $row['enrollee_id'] ?>)">
                          Delete
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="material-icons" style="font-size: 48px; color: #ccc;">search_off</i>
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
                  <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>" <?= $currentPage <= 1 ? 'tabindex="-1"' : '' ?>>Previous</a>
                </li>

                
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=1<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">1</a>
                  </li>
                  <?php if ($startPage > 2): ?>
                    <li class="page-item disabled">
                      <span class="page-link">...</span>
                    </li>
                  <?php endif;
                endif;

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                  <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><?= $i ?></a>
                  </li>
                <?php endfor;

                if ($endPage < $totalPages): ?>
                  <?php if ($endPage < $totalPages - 1): ?>
                    <li class="page-item disabled">
                      <span class="page-link">...</span>
                    </li>
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
              Showing page <strong><?= $currentPage ?></strong> of <strong><?= $totalPages ?></strong> (Total: <strong><?= $totalItems ?></strong> enrollee<?= $totalItems !== 1 ? 's' : '' ?>)
            </div>
          <?php endif; ?>

          <?php foreach ($all_enrollees as $row): ?>
            <div class="modal fade" id="editModal<?= $row['enrollee_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['enrollee_id'] ?>" aria-hidden="true">
              <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?= $row['enrollee_id'] ?>">Edit Enrollee: <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>

                  <form enctype="multipart/form-data" method="POST" action="php/edit_student.php">
                    <input type="hidden" name="enrollee_id" value="<?= $row['enrollee_id'] ?>">
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-8">
                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_lrn_<?= $row['enrollee_id'] ?>" name="lrn_no" placeholder="LRN No." value="<?= htmlspecialchars($row['lrn_no'] ?? '') ?>">
                                <label for="edit_lrn_<?= $row['enrollee_id'] ?>">LRN No.</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="date" class="form-control" id="edit_app_date_<?= $row['enrollee_id'] ?>" name="date_application"
                                  value="<?= !empty($row['application_date']) && $row['application_date'] !== '0000-00-00' ? date('Y-m-d', strtotime($row['application_date'])) : '' ?>">
                                <label for="edit_app_date_<?= $row['enrollee_id'] ?>">Date Application</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_pwd_<?= $row['enrollee_id'] ?>" name="pwd_id" placeholder="PWD ID" value="<?= htmlspecialchars($row['pwd_id'] ?? '') ?>">
                                <label for="edit_pwd_<?= $row['enrollee_id'] ?>">PWD ID</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_lname_<?= $row['enrollee_id'] ?>" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($row['last_name'] ?? '') ?>">
                                <label for="edit_lname_<?= $row['enrollee_id'] ?>">Last Name</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_fname_<?= $row['enrollee_id'] ?>" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($row['first_name'] ?? '') ?>">
                                <label for="edit_fname_<?= $row['enrollee_id'] ?>">First Name</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_mname_<?= $row['enrollee_id'] ?>" name="middle_name" placeholder="Middle Name" value="<?= htmlspecialchars($row['middle_name'] ?? '') ?>">
                                <label for="edit_mname_<?= $row['enrollee_id'] ?>">Middle Name</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_age_<?= $row['enrollee_id'] ?>" name="age" placeholder="Age" value="<?= htmlspecialchars($row['age'] ?? '') ?>">
                                <label for="edit_age_<?= $row['enrollee_id'] ?>">Age</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="date" class="form-control" id="edit_bday_<?= $row['enrollee_id'] ?>" name="birthday"
                                  value="<?= !empty($row['birthday']) && $row['birthday'] !== '0000-00-00' ? date('Y-m-d', strtotime($row['birthday'])) : '' ?>">
                                <label for="edit_bday_<?= $row['enrollee_id'] ?>">Birthday</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_sex_<?= $row['enrollee_id'] ?>" name="sex" placeholder="Sex" value="<?= htmlspecialchars($row['sex'] ?? '') ?>">
                                <label for="edit_sex_<?= $row['enrollee_id'] ?>">Sex</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_dis_diag_<?= $row['enrollee_id'] ?>" name="disability_diagnosis" placeholder="Disability/Diagnosis" value="<?= htmlspecialchars($row['disability_type'] ?? '') ?>">
                                <label for="edit_dis_diag_<?= $row['enrollee_id'] ?>">Disability/Diagnosis</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_cong_<?= $row['enrollee_id'] ?>" name="congenital_inborn" placeholder="Congenital/Inborn" value="<?= htmlspecialchars($row['disability_cause'] ?? '') ?>">
                                <label for="edit_cong_<?= $row['enrollee_id'] ?>">Congenital/Inborn</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_acq_<?= $row['enrollee_id'] ?>" name="acquired" placeholder="Acquired" value="">
                                <label for="edit_acq_<?= $row['enrollee_id'] ?>">Acquired</label>
                              </div>
                            </div>
                          </div>

                          <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="edit_spec_dis_<?= $row['enrollee_id'] ?>" name="specific_disability" placeholder="Specific Disability" value="<?= htmlspecialchars($row['specific_disability'] ?? '') ?>">
                            <label for="edit_spec_dis_<?= $row['enrollee_id'] ?>">Specific Disability</label>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_cstat_<?= $row['enrollee_id'] ?>" name="civil_status" placeholder="Civil Status" value="<?= htmlspecialchars($row['civil_status'] ?? '') ?>">
                                <label for="edit_cstat_<?= $row['enrollee_id'] ?>">Civil Status</label>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_addr_<?= $row['enrollee_id'] ?>" name="complete_address" placeholder="Complete Address" value="<?= htmlspecialchars($row['address'] ?? '') ?>">
                                <label for="edit_addr_<?= $row['enrollee_id'] ?>">Complete Address</label>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_bgy_<?= $row['enrollee_id'] ?>" name="barangay" placeholder="Barangay" value="<?= htmlspecialchars($row['barangay'] ?? '') ?>">
                                <label for="edit_bgy_<?= $row['enrollee_id'] ?>">Barangay</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-6">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_contact_<?= $row['enrollee_id'] ?>" name="contact_no" placeholder="Contact No." value="<?= htmlspecialchars($row['contact_no'] ?? '') ?>">
                                <label for="edit_contact_<?= $row['enrollee_id'] ?>">Contact No.</label>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-floating">
                                <select class="form-select" id="edit_education_type_<?= $row['enrollee_id'] ?>" name="education_type"
                                  onchange="updateEditPreviousLevel(<?= $row['enrollee_id'] ?>)">
                                  <option value="" disabled>Select Educational Attainment</option>
                                  <?php foreach (['CARER PROGRAM', 'JUNIOR HIGH SCHOOL', 'SENIOR HIGH SCHOOL', 'COLLEGE', 'TRANSITION PROGRAM', 'ALTERNATIVE LEARNING SCHOOL (ALS)', 'PALIGAWAN'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $row['educational_attainment'] == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <label for="edit_educ_<?= $row['enrollee_id'] ?>">Educational Attainment</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_emp_stat_<?= $row['enrollee_id'] ?>" name="employment_status" placeholder="Employment Status" value="<?= htmlspecialchars($row['employment_status'] ?? '') ?>">
                                <label for="edit_emp_stat_<?= $row['enrollee_id'] ?>">Employment Status</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_emp_cat_<?= $row['enrollee_id'] ?>" name="category_of_employment" placeholder="Category of Employment" value="<?= htmlspecialchars($row['employment_category'] ?? '') ?>">
                                <label for="edit_emp_cat_<?= $row['enrollee_id'] ?>">Category of Employment</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_emp_nat_<?= $row['enrollee_id'] ?>" name="nature_of_employment" placeholder="Nature of Employment" value="<?= htmlspecialchars($row['employment_nature'] ?? '') ?>">
                                <label for="edit_emp_nat_<?= $row['enrollee_id'] ?>">Nature of Employment</label>
                              </div>
                            </div>
                          </div>

                          <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="edit_covid_<?= $row['enrollee_id'] ?>" name="covid_vaccine" placeholder="Received COVID 19 Vaccine" value="<?= htmlspecialchars($row['covid_vaccinated'] ?? '') ?>">
                            <label for="edit_covid_<?= $row['enrollee_id'] ?>">COVID 19 Vaccine</label>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_occ_<?= $row['enrollee_id'] ?>" name="occupation" placeholder="Occupation" value="<?= htmlspecialchars($row['occupation'] ?? '') ?>">
                                <label for="edit_occ_<?= $row['enrollee_id'] ?>">Occupation</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_father_<?= $row['enrollee_id'] ?>" name="father_name" placeholder="Father Name" value="<?= htmlspecialchars($row['father_name'] ?? '') ?>">
                                <label for="edit_father_<?= $row['enrollee_id'] ?>">Father Name</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_mother_<?= $row['enrollee_id'] ?>" name="mother_name" placeholder="Mother Name" value="<?= htmlspecialchars($row['mother_name'] ?? '') ?>">
                                <label for="edit_mother_<?= $row['enrollee_id'] ?>">Mother Name</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <select class="form-select" id="edit_previous_level_<?= $row['enrollee_id'] ?>" name="previous_level" data-current="<?= htmlspecialchars($row['previous_level'] ?? '') ?>">
                                  <option value="<?= htmlspecialchars($row['previous_level'] ?? '') ?>" selected>
                                    <?= htmlspecialchars($row['previous_level'] ?? 'Select Previous Level') ?>
                                  </option>
                                </select>
                                <label for="edit_prev_level_<?= $row['enrollee_id'] ?>">Previous Level/Grade</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_pstat_<?= $row['enrollee_id'] ?>" name="parent_status" placeholder="Parent Status" value="<?= htmlspecialchars($row['parent_status'] ?? '') ?>">
                                <label for="edit_pstat_<?= $row['enrollee_id'] ?>">Parent Status</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_sibs_<?= $row['enrollee_id'] ?>" name="siblings_no" placeholder="No. of Siblings" value="<?= htmlspecialchars($row['siblings_count'] ?? '') ?>">
                                <label for="edit_sibs_<?= $row['enrollee_id'] ?>">No. of Siblings</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_work_fam_<?= $row['enrollee_id'] ?>" name="working_family_no" placeholder="No. of Working in the Family" value="<?= htmlspecialchars($row['working_family_members'] ?? '') ?>">
                                <label for="edit_work_fam_<?= $row['enrollee_id'] ?>">Working in Family</label>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_min_<?= $row['enrollee_id'] ?>" name="monthly_income_head" placeholder="Monthly Income Head" value="<?= htmlspecialchars($row['monthly_income_head'] ?? '') ?>">
                                <label for="edit_min_<?= $row['enrollee_id'] ?>">Monthly Income</label>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_tfi_<?= $row['enrollee_id'] ?>" name="total_family_income" placeholder="Total Family Income" value="<?= htmlspecialchars($row['total_family_income'] ?? '') ?>">
                                <label for="edit_tfi_<?= $row['enrollee_id'] ?>">Family Income</label>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_ftype_<?= $row['enrollee_id'] ?>" name="family_type" placeholder="Type of Family" value="<?= htmlspecialchars($row['family_type'] ?? '') ?>">
                                <label for="edit_ftype_<?= $row['enrollee_id'] ?>">Family Type</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-6">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_comelec_<?= $row['enrollee_id'] ?>" name="comelec_registered" placeholder="Comelec Registered" value="<?= htmlspecialchars($row['comelec_registered'] ?? '') ?>">
                                <label for="edit_comelec_<?= $row['enrollee_id'] ?>">Comelec Registered</label>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_4ps_<?= $row['enrollee_id'] ?>" name="four_ps_member" placeholder="4Ps Member" value="<?= htmlspecialchars($row['four_ps_member'] ?? '') ?>">
                                <label for="edit_4ps_<?= $row['enrollee_id'] ?>">4Ps Member</label>
                              </div>
                            </div>
                          </div>

                          <div class="row g-2 mb-2">
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_guard_<?= $row['enrollee_id'] ?>" name="guardian" placeholder="Guardian" value="<?= htmlspecialchars($row['guardian_name'] ?? '') ?>">
                                <label for="edit_guard_<?= $row['enrollee_id'] ?>">Guardian</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_guard_contact_<?= $row['enrollee_id'] ?>" name="guardian_contact" placeholder="Guardian Contact No." value="<?= htmlspecialchars($row['guardian_contact'] ?? '') ?>">
                                <label for="edit_guard_contact_<?= $row['enrollee_id'] ?>">Guardian Contact</label>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-floating">
                                <input type="text" class="form-control" id="edit_house_<?= $row['enrollee_id'] ?>" name="house_tagging" placeholder="House Tagging" value="<?= htmlspecialchars($row['house_tagging'] ?? '') ?>">
                                <label for="edit_house_<?= $row['enrollee_id'] ?>">House Tagging</label>
                              </div>
                            </div>
                          </div>

                          <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="edit_teacher_<?= $row['enrollee_id'] ?>" name="teacher_name" placeholder="Teacher Name" value="<?= htmlspecialchars($row['teacher_name'] ?? '') ?>">
                            <label for="edit_teacher_<?= $row['enrollee_id'] ?>">Teacher Name</label>
                          </div>
                        </div>

                        <div class="col-md-4">
                          <div class="border rounded p-3 text-center h-50 d-flex flex-column justify-content-center">
                            <img id="imgPreviewEdit<?= $row['enrollee_id'] ?>"
                              src="<?= $row['photo'] ? htmlspecialchars($row['photo']) : 'https://via.placeholder.com/300x350' ?>"
                              class="img-fluid rounded mb-5"
                              alt="Photo">

                            <input type="file"
                              class="form-control"
                              name="photo"
                              accept="image/*"
                              onchange="previewImage(event, 'imgPreviewEdit<?= $row['enrollee_id'] ?>')">

                            <small class="text-muted mt-2">
                              Upload 2x2 or ID Photo (Optional)
                            </small>
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

  
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
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

  
  <div class="modal fade" id="enrollees" tabindex="-1" aria-labelledby="uploaddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">New Enrollees</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form enctype="multipart/form-data" method="POST" action="php/create_student.php">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-8">
                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_lrn" name="lrn_no" placeholder="LRN No." required>
                      <label for="new_lrn">LRN No.</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="date" class="form-control" id="new_date_app" name="date_application" required>
                      <label for="new_date_app">Date Application</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_pwd" name="pwd_id" placeholder="PWD ID">
                      <label for="new_pwd">PWD ID</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_lname" name="last_name" placeholder="Last Name" required>
                      <label for="new_lname">Last Name</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_fname" name="first_name" placeholder="First Name" required>
                      <label for="new_fname">First Name</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_mname" name="middle_name" placeholder="Middle Name">
                      <label for="new_mname">Middle Name</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_age" name="age" placeholder="Age">
                      <label for="new_age">Age</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="date" class="form-control" id="new_bday" name="birthday">
                      <label for="new_bday">Birthday</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_sex" name="sex" placeholder="Sex">
                      <label for="new_sex">Sex</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_dis_diag" name="disability_diagnosis" placeholder="Disability/Diagnosis">
                      <label for="new_dis_diag">Disability/Diagnosis</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_cong" name="congenital_inborn" placeholder="Congenital/Inborn">
                      <label for="new_cong">Congenital/Inborn</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_acq" name="acquired" placeholder="Acquired">
                      <label for="new_acq">Acquired</label>
                    </div>
                  </div>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_spec_dis" name="specific_disability" placeholder="Specific Disability">
                  <label for="new_spec_dis">Specific Disability</label>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_cstat" name="civil_status" placeholder="Civil Status" required>
                      <label for="new_cstat">Civil Status</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_addr" name="complete_address" placeholder="Complete Address (House No. & Street name)">
                      <label for="new_addr">Complete Address</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_bgy" name="barangay" placeholder="Barangay" required>
                      <label for="new_bgy">Barangay</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_contact" name="contact_no" placeholder="Contact No." required>
                      <label for="new_contact">Contact No.</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating mb-3">
                      <select class="form-select" id="education_type" name="education_type" required onchange="updatePreviousLevel()">
                        <option value="" selected disabled>Select Education Attainment</option>
                        <option value="CARER PROGRAM">CARER PROGRAM</option>
                        <option value="JUNIOR HIGH SCHOOL">JUNIOR HIGH SCHOOL</option>
                        <option value="SENIOR HIGH SCHOOL">SENIOR HIGH SCHOOL</option>
                        <option value="COLLEGE">COLLEGE</option>
                        <option value="TRANSITION PROGRAM">TRANSITION PROGRAM</option>
                        <option value="ALTERNATIVE LEARNING SCHOOL (ALS)">ALTERNATIVE LEARNING SCHOOL (ALS)</option>
                        <option value="PALIGAWAN">PALIGAWAN SATELLITE</option>
                      </select>
                      <label>Educational Attainment</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_emp_stat" name="employment_status" placeholder="Employment Status" required>
                      <label for="new_emp_stat">Employment Status</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_emp_cat" name="category_of_employment" placeholder="Category of Employment" required>
                      <label for="new_emp_cat">Category of Employment</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_emp_nat" name="nature_of_employment" placeholder="Nature of Employment" required>
                      <label for="new_emp_nat">Nature of Employment</label>
                    </div>
                  </div>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_covid" name="covid_vaccine" placeholder="Received COVID 19 Vaccine" required>
                  <label for="new_covid">COVID 19 Vaccine</label>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_occ" name="occupation" placeholder="Occupation" required>
                      <label for="new_occ">Occupation</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_father" name="father_name" placeholder="Father Name" required>
                      <label for="new_father">Father Name</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_mother" name="mother_name" placeholder="Mother Name" required>
                      <label for="new_mother">Mother Name</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <select class="form-select" id="previous_level" name="previous_level" required disabled>
                        <option value="" selected disabled>Select Previous Level/Grade</option>
                      </select>
                      <label>Previous Level/Grade</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_pstat" name="parent_status" placeholder="Parent Status" required>
                      <label for="new_pstat">Parent Status</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_sibs" name="siblings_no" placeholder="No. of Siblings" required>
                      <label for="new_sibs">No. of Siblings</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_work_fam" name="working_family_no" placeholder="No. of Working in the Family" required>
                      <label for="new_work_fam">Working in Family</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_min" name="monthly_income_head" placeholder="Monthly Income Head" required>
                      <label for="new_min">Monthly Income</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_tfi" name="total_family_income" placeholder="Total Family Income" required>
                      <label for="new_tfi">Family Income</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_ftype" name="family_type" placeholder="Type of Family" required>
                      <label for="new_ftype">Family Type</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_comelec" name="comelec_registered" placeholder="Comelec Registered" required>
                      <label for="new_comelec">Comelec Registered</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_4ps" name="four_ps_member" placeholder="4Ps Member" required>
                      <label for="new_4ps">4Ps Member</label>
                    </div>
                  </div>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_guard" name="guardian" placeholder="Guardian" required>
                      <label for="new_guard">Guardian</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_guard_contact" name="guardian_contact" placeholder="Guardian Contact No." required>
                      <label for="new_guard_contact">Guardian Contact</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="new_house" name="house_tagging" placeholder="House Tagging" required>
                      <label for="new_house">House Tagging</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="border rounded p-3 text-center h-50 d-flex flex-column justify-content-center">
                  <img id="imgPreview"
                    src="https://via.placeholder.com/300x350"
                    class="img-fluid rounded mb-5"
                    alt="Photo">

                  <input type="file"
                    class="form-control"
                    name="photo"
                    accept="image/*"
                    onchange="previewImage(event)"
                    required>

                  <small class="text-muted mt-2">
                    Upload 2x2 or ID Photo
                  </small>
                </div><br>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_emer_person" name="emergency_contact_person" placeholder="Emergency Contact Person">
                  <label for="new_emer_person">Emergency Contact Person</label>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_emer_name" name="emergency_name" placeholder="Emergency Name">
                  <label for="new_emer_name">Emergency Name</label>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_emer_addr" name="emergency_address" placeholder="Emergency Address">
                  <label for="new_emer_addr">Emergency Address</label>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_emer_contact" name="emergency_contact" placeholder="Contact No./FB Account/Guardian Contact No.">
                  <label for="new_emer_contact">Emergency Contact</label>
                </div>

                <div class="form-floating mb-2">
                  <input type="text" class="form-control" id="new_teacher" name="teacher_name" placeholder="Teacher Name">
                  <label for="new_teacher">Teacher Name</label>
                </div>
              </div>
            </div>

            <hr>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="agreement" required>
              <label class="form-check-label">
                I hereby certify that all information provided is true and correct.
              </label>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/scripts.js"></script>
  <script src="js/user.js"></script>
  <script>
    function setDeleteId(enrolleeId) {
      document.getElementById('deleteEnrolleeId').value = enrolleeId;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
      const enrolleeId = document.getElementById('deleteEnrolleeId').value;

      fetch('php/delete_student.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'enrollee_id=' + enrolleeId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while deleting the enrollee.');
        });
    });

    function previewImage(event, imgId = 'imgPreview') {
      const reader = new FileReader();
      reader.onload = function() {
        document.getElementById(imgId).src = reader.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    }

    function updatePreviousLevel() {
      const type = document.getElementById("education_type").value;
      const level = document.getElementById("previous_level");

      level.innerHTML = '<option value="" selected disabled>Select Previous Level/Grade</option>';

      level.disabled = false;

      if (type === "CARER PROGRAM") {

        const carerOptions = [
          "EARLY INTERVENTION (1 on 1)",
          "EARLY INTERVENTION (by 2’s)",
          "GROUP TUTORIAL",
          "ADAPTIVE SKILLS PROGRAM II",
          "ADAPTIVE SKILLS PROGRAM III",
          "ADAPTIVE SKILLS PROGRAM IV",
          "ON THE JOB TRAINING (OJT)",
          "HOME PROGRAM",
          "TUTORIAL PROGRAM",
          "DEPED SPED/SNED"
        ];

        carerOptions.forEach(item => {
          let option = document.createElement("option");
          option.value = item;
          option.text = item;
          level.appendChild(option);
        });

      } else if (type === "JUNIOR HIGH SCHOOL") {

        for (let i = 1; i <= 10; i++) {
          let option = document.createElement("option");
          option.value = "Grade " + i;
          option.text = "Grade " + i;
          level.appendChild(option);
        }

      } else if (type === "SENIOR HIGH SCHOOL") {

        ["Grade 11", "Grade 12"].forEach(item => {
          let option = document.createElement("option");
          option.value = item;
          option.text = item;
          level.appendChild(option);
        });

      } else if (type === "COLLEGE") {

        ["1ST YEAR", "2ND YEAR", "3RD YEAR", "4TH YEAR"].forEach(item => {
          let option = document.createElement("option");
          option.value = item;
          option.text = item;
          level.appendChild(option);
        });

      } else if (
        type === "TRANSITION PROGRAM" ||
        type === "ALTERNATIVE LEARNING SCHOOL (ALS)" ||
        type === "PALIGAWAN"
      ) {

        level.disabled = true;

      }
    }

    function updateEditPreviousLevel(id) {
      const type = document.getElementById("edit_education_type_" + id).value;
      const level = document.getElementById("edit_previous_level_" + id);
      const savedValue = level.dataset.current;

      level.innerHTML = '<option value="" selected disabled>Select Previous Level/Grade</option>';
      level.disabled = false;

      const options = {
        "CARER PROGRAM": ["EARLY INTERVENTION (1 on 1)", "EARLY INTERVENTION (by 2's)", "GROUP TUTORIAL",
          "ADAPTIVE SKILLS PROGRAM II", "ADAPTIVE SKILLS PROGRAM III", "ADAPTIVE SKILLS PROGRAM IV",
          "ON THE JOB TRAINING (OJT)", "HOME PROGRAM", "TUTORIAL PROGRAM", "DEPED SPED/SNED"
        ],
        "JUNIOR HIGH SCHOOL": Array.from({
          length: 10
        }, (_, i) => "Grade " + (i + 1)),
        "SENIOR HIGH SCHOOL": ["Grade 11", "Grade 12"],
        "COLLEGE": ["1ST YEAR", "2ND YEAR", "3RD YEAR", "4TH YEAR"]
      };

      if (options[type]) {
        options[type].forEach(item => {
          let option = document.createElement("option");
          option.value = item;
          option.text = item;
          level.appendChild(option);
        });
        if (savedValue) level.value = savedValue;
      } else {
        level.disabled = true;
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      <?php foreach ($all_enrollees as $row): ?>
        updateEditPreviousLevel(<?= $row['enrollee_id'] ?>);
      <?php endforeach; ?>
    });
  </script>
</body>

</html>