<?php
session_start();
if (!$_SESSION['loggedin']) {
   header('Location: ../index.php');
   exit();
}


if (isset($_SESSION['upload_message'])) {
   $message = $_SESSION['upload_message'];
   echo "<script>alert('" . addslashes($message) . "');</script>";
   unset($_SESSION['upload_message']);
}

include '../php/ConnectToDb.php';

if (isset($_POST['done_schedule_id'])) {
   $del_id = intval($_POST['done_schedule_id']);
   if ($del_id > 0) {
      $stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
      $stmt->bind_param('i', $del_id);
      $stmt->execute();
      $stmt->close();
   }
   header("Location: index.php?sched_deleted=1");
   exit();
}

$today = date('Y-m-d');
$scheduleResult = $conn->query("
   SELECT schedule_id, title, event_date, event_time, description
   FROM schedule
   WHERE event_date >= '$today'
   ORDER BY event_date ASC, event_time ASC
   LIMIT 10
");
$schedules = [];
while ($row = $scheduleResult->fetch_assoc()) {
   $schedules[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
   <title>PWD-Carmona</title>
   <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
   <link href="css/styles.css" rel="stylesheet" />
   <link href="css/admin.css" rel="stylesheet" />
   <link href="css/user.css" rel="stylesheet" />
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
   <link rel="stylesheet" href="css/print.css">
   <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

   <style>
      .chart-card {
         height: 500px;
      }

      .chart-container {
         position: relative;
         height: 100%;
         width: 100%;
      }

      body {
         font-family: Arial, sans-serif;
      }

      button {
         padding: 10px 20px;
         font-size: 16px;
         cursor: pointer;
      }

      .modal {
         display: none;
         position: fixed;
         z-index: 9999;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background: rgba(0, 0, 0, 0.6);
      }

      .modal-content {
         background: #fff;
         width: 80%;
         max-width: 700px;
         margin: 8% auto;
         padding: 20px;
         border-radius: 10px;
      }

      .close {
         float: right;
         font-size: 24px;
         cursor: pointer;
      }

      /* ===== SCHEDULE CARD ===== */
      .schedule-list {
         flex: 1;
         overflow-y: auto;
         display: flex;
         flex-direction: column;
         gap: 8px;
         padding-right: 2px;
      }

      .schedule-item {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;
         padding: 10px 12px;
         border-radius: 8px;
         background: #f1f8ff;
         border-left: 4px solid #007bff;
         transition: background 0.2s;
      }

      .schedule-item.today-event {
         background: #f0fff4;
         border-left-color: #28a745;
      }

      .schedule-item-info {
         flex: 1;
         min-width: 0;
      }

      .schedule-item-title {
         font-weight: 600;
         font-size: 14px;
         color: #333;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .schedule-item-date {
         font-size: 12px;
         color: #6c757d;
         margin-top: 2px;
      }

      .schedule-item-desc {
         font-size: 12px;
         color: #888;
         margin-top: 2px;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .schedule-item-actions {
         display: flex;
         gap: 6px;
         flex-shrink: 0;
         margin-left: 10px;
         align-items: center;
      }

      .schedule-item-actions .btn {
         padding: 4px 10px;
         font-size: 12px;
      }

      .schedule-empty {
         text-align: center;
         color: #aaa;
         padding: 30px 0;
         font-size: 14px;
      }

      .schedule-empty i {
         font-size: 32px;
         display: block;
         margin-bottom: 8px;
      }

      /* View detail modal */
      #scheduleViewModal .sv-title {
         font-size: 18px;
         font-weight: 700;
         margin-bottom: 8px;
      }

      #scheduleViewModal .sv-row {
         margin-bottom: 6px;
         font-size: 14px;
      }

      #scheduleViewModal .sv-label {
         font-weight: 600;
         color: #555;
         min-width: 90px;
         display: inline-block;
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
                  <a class="nav-link active" href="index.php">
                     <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
                  </a>
                  <?php if ($_SESSION['role'] != 2): ?>
                     <a class="nav-link" href="new.php">
                        <div class="sb-nav-link-icon"><i class="fa fa-graduation-cap"></i></div>New Enrollees
                     </a>
                  <?php endif; ?>
                  <a class="nav-link" href="masterlist.php">
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
                           <a class="nav-link edit-section-link" href="ls/intervention/intervention.php">Intervention</a>
                           <a class="nav-link edit-section-link" href="ls/inclusion/inclusion.php">Inclusions</a>
                           <a class="nav-link edit-section-link" href="ls/senior/sh.php">Senior High</a>
                           <a class="nav-link edit-section-link" href="ls/college/college.php">College</a>
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
            <div class="container-fluid" role="main" aria-label="Admin dashboard">

               <?php if (isset($_GET['sched_deleted'])): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                     Schedule marked as done and removed.
                     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
               <?php endif; ?>

               <div class="d-flex align-items-center">
                  <h2 class="m-0">Admin Dashboard</h2>
                  <button class="btn btn-secondary ms-auto" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Files</button>
                  <button class="btn btn-primary no-print m-3" onclick="printDashboard()">
                     <i class="fa fa-print"></i> Print Dashboard
                  </button>
               </div><br />

               <div class="row mb-4" aria-label="Summary statistics">

                  <div class="col-sm-12 col-md-4 mb-5">
                     <div class="card" style="height: 250px;" aria-live="polite">
                        <div class="card-body">
                           <h5 class="card-title">Total No. of Enrollees</h5>
                           <h6 id="totalUsers" class="counter" tabindex="0">Loading...</h6>
                           <h5>NO. OF FEMALE: <span id="totalFemale">—</span></h5>
                           <h5>NO. OF MALE: <span id="totalMale">—</span></h5>
                           <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pergender">View Details</button>
                        </div>
                     </div><br />

                     <div class="card" style="height: 175px;" aria-live="polite">
                        <div class="card-body">
                           <h6 class="card-title">Total No. of Disability Enrollees</h6>
                           <h6 id="totalDisability" class="counter" tabindex="0">Loading...</h6>
                           <button class="btn btn-primary btn-sm" onclick="openDisabilityModal()">View Details</button>
                        </div>
                     </div>
                  </div>

                  
                  <div class="col-sm-4 col-md-8 mb-3">
                     <div class="card" style="height: 450px;">
                        <div class="card-body d-flex flex-column" style="overflow:hidden;">
                           <div class="d-flex align-items-center mb-3">
                              <h5 class="card-title m-0">Upcoming Schedules</h5>
                              <a href="schedule.php" class="btn btn-outline-primary btn-sm ms-auto">
                                 <i class="fa fa-calendar me-1"></i> Full Calendar
                              </a>
                           </div>

                           <div class="schedule-list" id="dashScheduleList">
                              <?php if (empty($schedules)): ?>
                                 <div class="schedule-empty">
                                    <i class="fa fa-calendar-check"></i>
                                    No upcoming schedules
                                 </div>
                              <?php else: ?>
                                 <?php foreach ($schedules as $sched):
                                    $isToday = ($sched['event_date'] === $today);
                                    $dateLabel = $isToday ? 'Today' : date('F j, Y', strtotime($sched['event_date']));
                                    $timeLabel = !empty($sched['event_time']) ? date('g:i A', strtotime($sched['event_time'])) : '';
                                 ?>
                                    <div class="schedule-item <?= $isToday ? 'today-event' : '' ?>" id="sched-<?= $sched['schedule_id'] ?>">
                                       <div class="schedule-item-info">
                                          <div class="schedule-item-title" title="<?= htmlspecialchars($sched['title']) ?>">
                                             <?= htmlspecialchars($sched['title']) ?>
                                          </div>
                                          <div class="schedule-item-date">
                                             <?= $dateLabel ?><?= $timeLabel ? ' &bull; ' . $timeLabel : '' ?>
                                          </div>
                                          <?php if (!empty($sched['description'])): ?>
                                             <div class="schedule-item-desc" title="<?= htmlspecialchars($sched['description']) ?>">
                                                <?= htmlspecialchars(mb_strimwidth($sched['description'], 0, 60, '…')) ?>
                                             </div>
                                          <?php endif; ?>
                                       </div>
                                       <div class="schedule-item-actions">
                                          <button class="btn btn-primary btn-sm"
                                             onclick='viewSchedule(<?= htmlspecialchars(json_encode($sched), ENT_QUOTES) ?>)'>
                                             View
                                          </button>
                                          <form method="POST" style="margin:0;">
                                             <input type="hidden" name="done_schedule_id" value="<?= $sched['schedule_id'] ?>">
                                             <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Mark this schedule as done and delete it?')">
                                                Done
                                             </button>
                                          </form>
                                       </div>
                                    </div>
                                 <?php endforeach; ?>
                              <?php endif; ?>
                           </div>
                        </div>
                     </div>
                  </div>
                  

                  <div class="row mb-4" aria-label="Summary statistics">
                     <div class="col-sm-4 col-md-4 mb-3">
                        <div class="card" aria-live="polite">
                           <div class="card-body">
                              <h5 class="card-title">Total No. of Enrollees per Age</h5>
                              <div id="topAgesDisplay">Loading...</div>
                              <button class="btn btn-primary btn-sm mt-2" onclick="openAgeModal()">View Details</button>
                           </div>
                        </div>
                     </div>
                     <div class="col-sm-4 col-md-4 mb-3">
                        <div class="card" aria-live="polite">
                           <div class="card-body">
                              <h5 class="card-title">Total No. of Enrollees per Level</h5>
                              <div id="topLevelsDisplay">Loading...</div>
                              <button class="btn btn-primary btn-sm mt-2" onclick="openLevelModal()">View Details</button>
                           </div>
                        </div>
                     </div>
                     <div class="col-sm-4 col-md-4 mb-3">
                        <div class="card" aria-live="polite">
                           <div class="card-body">
                              <h5 class="card-title">Total No. of Enrollees per Barangay</h5>
                              <div id="topBarangaysDisplay">Loading...</div>
                              <button class="btn btn-primary btn-sm mt-2" onclick="openBarangayModal()">View Details</button>
                           </div>
                        </div>
                     </div>
                  </div>

                  <div class="row mb-4"></div>

                  <div class="col-sm-12 col-md-12 mb-3">
                     <div class="card" style="height: 400px;" aria-live="polite">
                        <div class="card-body d-flex flex-column">
                           <h5 class="card-title mb-3">Yearly Enrollments</h5>
                           <div style="flex: 1; position: relative;">
                              <canvas id="barChart"></canvas>
                           </div>
                        </div>
                     </div>
                  </div>

               </div>
            </div>
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

   
   <div class="modal fade" id="modalFieldCreate" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <form class="modal-content" id="formFieldCreate" method="POST" action="FieldHandler.php">
            <div class="modal-header">
               <h5 class="modal-title">Create New Field</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <label for="inputFieldName" class="form-label">Field Name</label>
               <input type="text" name="inputFieldName" id="inputFieldName" class="form-control" placeholder="Enter field name" required />
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" name="CreateField" class="btn btn-primary">Save</button>
            </div>
         </form>
      </div>
   </div>

   
   <div id="levelModal" class="modal">
      <div class="modal-content">
         <span class="close" onclick="closeLevelModal()">&times;</span>
         <h2>Enrolled Per Level</h2>
         <div style="position:relative;height:300px;"><canvas id="levelChart"></canvas></div>
      </div>
   </div>

   
   <div id="barangayModal" class="modal">
      <div class="modal-content">
         <span class="close" onclick="closeBarangayModal()">&times;</span>
         <h2>Enrolled Per Barangay</h2>
         <div style="position:relative;height:300px;"><canvas id="barangayChart"></canvas></div>
      </div>
   </div>

   
   <div id="ageModal" class="modal">
      <div class="modal-content">
         <span class="close" onclick="closeAgeModal()">&times;</span>
         <h2>Enrolled Per Age</h2>
         <div style="position:relative;height:300px;"><canvas id="ageChart"></canvas></div>
      </div>
   </div>

   
   <div class="modal fade" id="pergender" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Total no. of Enrollees Per Gender</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div style="position:relative;height:280px;"><canvas id="responsivePieChart"></canvas></div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>

   
   <div id="graphModal" class="modal">
      <div class="modal-content">
         <span class="close" onclick="closeDisabilityModal()">&times;</span>
         <h2>Disability Enrolled</h2>
         <div style="position:relative;height:300px;"><canvas id="lineChart"></canvas></div>
      </div>
   </div>

   
   <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Upload File</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="php/upload_students.php" method="POST" enctype="multipart/form-data">
               <div class="modal-body">
                  <div class="mb-3">
                     <label for="fileUpload" class="form-label">Choose a file</label>
                     <input type="file" class="form-control" id="fileUpload" name="fileUpload" required>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Upload</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   
   <div class="modal fade" id="scheduleViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title"><i class="fa fa-calendar-day me-2 text-primary"></i>Schedule Details</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div class="sv-title" id="svTitle">—</div>
               <div class="sv-row"><span class="sv-label">Date:</span> <span id="svDate">—</span></div>
               <div class="sv-row"><span class="sv-label">Time:</span> <span id="svTime">—</span></div>
               <div class="sv-row" id="svDescRow"><span class="sv-label">Description:</span><br><span id="svDesc" style="color:#555;"></span></div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
               <a href="schedule.php" class="btn btn-primary">Go to Calendar</a>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
   <script src="js/scripts.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
   <script src="js/admin.js"></script>
   <script src="js/print.js"></script>

   <script>
      let barChartInstance = null,
         pieChartInstance = null;
      let levelChartInstance = null,
         barangayChartInstance = null;
      let ageChartInstance = null,
         disabilityChartInstance = null;
      let pieChartLoaded = false,
         levelChartLoaded = false;
      let barangayChartLoaded = false,
         ageChartLoaded = false,
         disabilityChartLoaded = false;

      document.addEventListener('DOMContentLoaded', () => {
         fetch('php/dashboard_stats.php?type=all')
            .then(r => r.json())
            .then(d => {
               if (d.error) {
                  console.error(d.error);
                  return;
               }
               document.getElementById('totalUsers').textContent = d.total.toLocaleString();
               document.getElementById('totalDisability').textContent = d.dis_total.toLocaleString();
               document.getElementById('totalFemale').textContent = d.female.toLocaleString();
               document.getElementById('totalMale').textContent = d.male.toLocaleString();
               document.getElementById('topAgesDisplay').innerHTML = d.top_ages.map(a => `Age <strong>${a.group}</strong>: ${a.count}`).join('<br>');
               document.getElementById('topLevelsDisplay').innerHTML = d.top_levels.map(l => `<strong>${l.level}</strong>: ${l.count}`).join('<br>');
               document.getElementById('topBarangaysDisplay').innerHTML = d.top_barangays.map(b => `<strong>${b.barangay}</strong>: ${b.count}`).join('<br>');
               buildBarChart(d.yearly.labels, d.yearly.data);
            })
            .catch(e => console.error('Dashboard stats error:', e));
      });

      function buildBarChart(labels, data) {
         if (barChartInstance) barChartInstance.destroy();
         const ctx = document.getElementById('barChart').getContext('2d');
         barChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
               labels,
               datasets: [{
                  data,
                  backgroundColor: '#4caf50',
                  borderRadius: 4
               }]
            },
            options: {
               responsive: true,
               maintainAspectRatio: false,
               plugins: {
                  legend: {
                     display: false
                  }
               },
               scales: {
                  y: {
                     beginAtZero: true
                  }
               }
            }
         });
      }

      function viewSchedule(sched) {
         document.getElementById('svTitle').textContent = sched.title || '—';
         const d = new Date(sched.event_date + 'T00:00:00');
         document.getElementById('svDate').textContent = d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
         });
         document.getElementById('svTime').textContent = sched.event_time ?
            new Date('1970-01-01T' + sched.event_time).toLocaleTimeString([], {
               hour: '2-digit',
               minute: '2-digit'
            }) :
            'Not set';
         const descRow = document.getElementById('svDescRow');
         if (sched.description) {
            document.getElementById('svDesc').textContent = sched.description;
            descRow.style.display = 'block';
         } else {
            descRow.style.display = 'none';
         }
         new bootstrap.Modal(document.getElementById('scheduleViewModal')).show();
      }

      document.getElementById('pergender').addEventListener('shown.bs.modal', () => {
         if (pieChartLoaded) return;
         fetch('php/dashboard_stats.php?type=gender').then(r => r.json()).then(d => {
            if (pieChartInstance) pieChartInstance.destroy();
            pieChartInstance = new Chart(document.getElementById('responsivePieChart').getContext('2d'), {
               type: 'pie',
               data: {
                  labels: ['Female', 'Male'],
                  datasets: [{
                     data: [d.female, d.male],
                     backgroundColor: ['#4caf50', '#ffc107']
                  }]
               },
               options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                     legend: {
                        position: 'bottom'
                     }
                  }
               }
            });
            pieChartLoaded = true;
         });
      });

      function openDisabilityModal() {
         document.getElementById("graphModal").style.display = "block";
         if (disabilityChartLoaded) return;
         fetch('php/dashboard_stats.php?type=disability').then(r => r.json()).then(d => {
            if (disabilityChartInstance) disabilityChartInstance.destroy();
            disabilityChartInstance = new Chart(document.getElementById("lineChart").getContext("2d"), {
               type: "line",
               data: {
                  labels: d.labels,
                  datasets: [{
                     label: "Disability Enrolled",
                     data: d.data,
                     borderColor: '#4caf50',
                     backgroundColor: 'rgba(76,175,80,0.1)',
                     borderWidth: 3,
                     tension: 0.4,
                     fill: false
                  }]
               },
               options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                     legend: {
                        display: false
                     }
                  },
                  scales: {
                     y: {
                        beginAtZero: true
                     }
                  }
               }
            });
            disabilityChartLoaded = true;
         });
      }

      function closeDisabilityModal() {
         document.getElementById("graphModal").style.display = "none";
      }

      function openAgeModal() {
         document.getElementById("ageModal").style.display = "block";
         if (ageChartLoaded) return;
         fetch('php/dashboard_stats.php?type=age').then(r => r.json()).then(d => {
            if (ageChartInstance) ageChartInstance.destroy();
            ageChartInstance = new Chart(document.getElementById("ageChart").getContext("2d"), {
               type: "bar",
               data: {
                  labels: d.labels,
                  datasets: [{
                     label: "Enrolled Per Age",
                     data: d.data,
                     borderWidth: 2
                  }]
               },
               options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                     y: {
                        beginAtZero: true
                     }
                  }
               }
            });
            ageChartLoaded = true;
         });
      }

      function closeAgeModal() {
         document.getElementById("ageModal").style.display = "none";
      }

      function openBarangayModal() {
         document.getElementById("barangayModal").style.display = "block";
         if (barangayChartLoaded) return;
         fetch('php/dashboard_stats.php?type=barangay').then(r => r.json()).then(d => {
            if (barangayChartInstance) barangayChartInstance.destroy();
            barangayChartInstance = new Chart(document.getElementById("barangayChart").getContext("2d"), {
               type: "bar",
               data: {
                  labels: d.labels,
                  datasets: [{
                     label: "Enrolled Per Barangay",
                     data: d.data,
                     borderWidth: 2
                  }]
               },
               options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                     y: {
                        beginAtZero: true
                     }
                  }
               }
            });
            barangayChartLoaded = true;
         });
      }

      function closeBarangayModal() {
         document.getElementById("barangayModal").style.display = "none";
      }

      function openLevelModal() {
         document.getElementById("levelModal").style.display = "block";
         if (levelChartLoaded) return;
         fetch('php/dashboard_stats.php?type=level').then(r => r.json()).then(d => {
            if (levelChartInstance) levelChartInstance.destroy();
            levelChartInstance = new Chart(document.getElementById("levelChart").getContext("2d"), {
               type: "bar",
               data: {
                  labels: d.labels,
                  datasets: [{
                     label: "Enrolled Per Level",
                     data: d.data,
                     borderWidth: 2
                  }]
               },
               options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                     y: {
                        beginAtZero: true
                     }
                  }
               }
            });
            levelChartLoaded = true;
         });
      }

      function closeLevelModal() {
         document.getElementById("levelModal").style.display = "none";
      }
   </script>

</body>

</html>