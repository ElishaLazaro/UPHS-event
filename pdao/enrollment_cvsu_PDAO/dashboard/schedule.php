<?php
session_start();
if (!$_SESSION['loggedin']) {
   header('Location: ../index.php');
   exit();
}

include '../php/ConnectToDb.php';

$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
$year  = isset($_GET['year'])  ? intval($_GET['year'])  : intval(date('Y'));

if ($month < 1) {
   $month = 12;
   $year--;
}
if ($month > 12) {
   $month = 1;
   $year++;
}

$start = sprintf('%04d-%02d-01', $year, $month);
$end   = date('Y-m-t', strtotime($start));

$events = [];
$stmt = $conn->prepare("SELECT schedule_id, title, event_date, event_time, description FROM schedule WHERE event_date BETWEEN ? AND ? ORDER BY event_date, event_time");
$stmt->bind_param('ss', $start, $end);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
   $day = intval(date('j', strtotime($row['event_date'])));
   $events[$day][] = $row;
}
$stmt->close();

if (isset($_POST['delete_id'])) {
   $del = intval($_POST['delete_id']);
   $stmt2 = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
   $stmt2->bind_param('i', $del);
   $stmt2->execute();
   $stmt2->close();
   header("Location: schedule.php?month=$month&year=$year");
   exit();
}

if (isset($_POST['edit_id'])) {
   $edit_id    = intval($_POST['edit_id']);
   $edit_title = trim($_POST['edit_title'] ?? '');
   $edit_date  = trim($_POST['edit_date'] ?? '');
   $edit_time  = trim($_POST['edit_time'] ?? '') ?: null;
   $edit_desc  = trim($_POST['edit_description'] ?? '') ?: null;

   if ($edit_title && $edit_date) {
      $stmt3 = $conn->prepare("UPDATE schedule SET title=?, event_date=?, event_time=?, description=? WHERE schedule_id=?");
      $stmt3->bind_param('ssssi', $edit_title, $edit_date, $edit_time, $edit_desc, $edit_id);
      $stmt3->execute();
      $stmt3->close();
      $month = intval(date('n', strtotime($edit_date)));
      $year  = intval(date('Y', strtotime($edit_date)));
   }
   header("Location: schedule.php?month=$month&year=$year&updated=1");
   exit();
}

$monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
   $prevMonth = 12;
   $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
   $nextMonth = 1;
   $nextYear++;
}

$firstDay    = intval(date('w', strtotime($start)));
$daysInMonth = intval(date('t', strtotime($start)));
$today_d = intval(date('j'));
$today_m = intval(date('n'));
$today_y = intval(date('Y'));
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
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
   <link rel="stylesheet" href="css/print.css">
   <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
   <style>
      body {
         font-family: Arial, sans-serif;
      }

      button {
         padding: 10px 20px;
         font-size: 16px;
         cursor: pointer;
      }

      form {
         display: flex;
         flex-direction: column;
      }

      form label {
         margin-top: 10px;
         margin-bottom: 5px;
         font-weight: bold;
      }

      form input,
      form textarea {
         padding: 10px;
         border-radius: 6px;
         border: 1px solid #ccc;
         font-size: 16px;
      }

      form input[type="date"],
      form input[type="time"] {
         padding: 8px;
      }

      #fullScreenCalendar {
         display: flex;
         flex-direction: column;
         height: 80%;
         width: 100%;
         background: #f8f9fa;
         padding: 10px;
         box-sizing: border-box;
      }

      .calendar-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 0 20px;
         margin-bottom: 10px;
      }

      .calendar-header a.btn-nav {
         padding: 10px 20px;
         font-size: 20px;
         cursor: pointer;
         background: #fff;
         border: 1px solid #ccc;
         border-radius: 4px;
         text-decoration: none;
         color: #333;
      }

      .calendar-header a.btn-nav:hover {
         background: #e9ecef;
      }

      #calendarTable {
         width: 100%;
         border-collapse: collapse;
         table-layout: fixed;
         flex-grow: 1;
      }

      #calendarTable th,
      #calendarTable td {
         border: 1px solid #ddd;
         text-align: left;
         width: 14.28%;
         height: calc((100vh - 70px) / 10);
         vertical-align: top;
         padding: 4px 6px;
      }

      #calendarTable th {
         background: #007bff;
         color: white;
         font-weight: bold;
         text-align: center;
         vertical-align: middle;
         height: auto;
         padding: 8px;
      }

      #calendarTable td {
         font-size: 14px;
         transition: background 0.2s;
      }

      #calendarTable td.today {
         background: #d4edda;
      }

      #calendarTable td:hover {
         background: #cce5ff;
         cursor: pointer;
      }

      #calendarTable td.empty {
         background: #f8f9fa;
         cursor: default;
      }

      .day-num {
         font-size: 15px;
         font-weight: bold;
         color: #333;
         display: block;
         margin-bottom: 2px;
      }

      #calendarTable td.today .day-num {
         color: #28a745;
      }

      .event-label {
         display: block;
         background: #007bff;
         color: #fff;
         font-size: 11px;
         border-radius: 3px;
         padding: 2px 5px;
         margin-bottom: 2px;
         overflow: hidden;
         line-height: 1.3;
      }

      #calendarTable td.today .event-label {
         background: #28a745;
      }

      .ev-time {
         font-size: 10px;
         opacity: 0.9;
      }

      .ev-desc {
         font-size: 10px;
         opacity: 0.85;
         display: block;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .event-more {
         font-size: 10px;
         color: #6c757d;
      }

      /* Event panel action buttons */
      .event-actions {
         display: flex;
         gap: 4px;
         align-items: center;
         flex-shrink: 0;
      }

      .btn-edit-ev {
         background: none;
         border: none;
         color: #007bff;
         cursor: pointer;
         padding: 0 5px;
         font-size: 14px;
      }

      .btn-edit-ev:hover {
         color: #0056b3;
      }

      .btn-delete-ev {
         background: none;
         border: none;
         color: #dc3545;
         cursor: pointer;
         padding: 0 5px;
         font-size: 14px;
      }

      .btn-delete-ev:hover {
         color: #a71d2a;
      }
   </style>
</head>

<body class="sb-nav-fixed">
   <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
      <a class="navbar-brand ps-3" href="">PWD_CARMONA CITY</a>
      <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
   </nav>
   <div id="layoutSidenav">
      <div id="layoutSidenav_nav">
         <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
               <div class="nav">
                  <a class="nav-link" href="index.php">
                     <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
                  </a>
                  <a class="nav-link" href="new.php">
                     <div class="sb-nav-link-icon"><i class="fa fa-graduation-cap"></i></div>New Enrollees
                  </a>
                  <a class="nav-link" href="masterlist.php">
                     <div class="sb-nav-link-icon"><i class="fa fa-list"></i></div>Master List
                  </a>
                  <a class="nav-link" href="schedule.php">
                     <div class="sb-nav-link-icon"><i class="fa fa-calendar"></i></div>Schedule
                  </a>
                  <a class="nav-link" href="announcement.php">
                     <div class="sb-nav-link-icon"><i class="fa fa-bullhorn"></i></div>Announcement
                  </a>
                  <a class="nav-link" href="report.php">
                     <div class="sb-nav-link-icon"><i class="fa fa-bar-chart"></i></div>Report
                  </a>
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
            <div class="container-fluid">

               <div class="d-flex align-items-center mb-3">
                  <h2 class="m-0">Schedule</h2>
                  <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#scheduleModal">Create Schedule</button>
               </div>

               <?php if (isset($_GET['saved'])): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                     Schedule saved successfully!
                     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
               <?php endif; ?>

               <?php if (isset($_GET['updated'])): ?>
                  <div class="alert alert-success alert-dismissible fade show" role="alert">
                     Schedule updated successfully!
                     <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>
               <?php endif; ?>

               <div class="card" style="height: 550px;">
                  <div id="fullScreenCalendar">
                     <div class="calendar-header">
                        <a class="btn-nav" href="schedule.php?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">&lt;</a>
                        <h3><?= $monthNames[$month] . ' ' . $year ?></h3>
                        <a class="btn-nav" href="schedule.php?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">&gt;</a>
                     </div>
                     <table id="calendarTable">
                        <thead>
                           <tr>
                              <th>Sun</th>
                              <th>Mon</th>
                              <th>Tue</th>
                              <th>Wed</th>
                              <th>Thu</th>
                              <th>Fri</th>
                              <th>Sat</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
                           $day = 1;
                           $started = false;
                           for ($row = 0; $row < 6; $row++):
                              if ($day > $daysInMonth) break;
                              echo '<tr>';
                              for ($col = 0; $col < 7; $col++):
                                 if (!$started && $col < $firstDay):
                                    echo '<td class="empty"></td>';
                                 elseif ($day > $daysInMonth):
                                    echo '<td class="empty"></td>';
                                 else:
                                    $started = true;
                                    $isToday = ($day == $today_d && $month == $today_m && $year == $today_y);
                                    $tdClass = $isToday ? 'today' : '';
                                    echo '<td class="' . $tdClass . '" data-day="' . $day . '">';
                                    echo '<span class="day-num">' . $day . '</span>';
                                    if (!empty($events[$day])):
                                       $shown = 0;
                                       foreach ($events[$day] as $ev):
                                          if ($shown < 2):
                                             $timeStr = '';
                                             if (!empty($ev['event_time'])) {
                                                $timeStr = date('g:i A', strtotime($ev['event_time']));
                                             }
                                             $descSnippet = '';
                                             if (!empty($ev['description'])) {
                                                $descSnippet = mb_strimwidth($ev['description'], 0, 30, '…');
                                             }
                                             $tooltip = htmlspecialchars($ev['title']);
                                             if ($timeStr) $tooltip .= ' | ' . $timeStr;
                                             if (!empty($ev['description'])) $tooltip .= ' — ' . htmlspecialchars($ev['description']);

                                             echo '<span class="event-label" title="' . $tooltip . '">';
                                             echo '<strong>' . htmlspecialchars($ev['title']) . '</strong>';
                                             if ($timeStr) echo ' <span class="ev-time">' . $timeStr . '</span>';
                                             if ($descSnippet) echo '<br><span class="ev-desc">' . htmlspecialchars($descSnippet) . '</span>';
                                             echo '</span>';
                                             $shown++;
                                          endif;
                                       endforeach;
                                       $extra = count($events[$day]) - 2;
                                       if ($extra > 0): echo '<span class="event-more">+' . $extra . ' more</span>';
                                       endif;
                                    endif;
                                    echo '</td>';
                                    $day++;
                                 endif;
                              endfor;
                              echo '</tr>';
                           endfor;
                           ?>
                        </tbody>
                     </table>
                  </div>
               </div>

               
               <div id="eventPanel" style="display:none; background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:16px 20px; margin-top:12px;">
                  <h5 id="eventPanelTitle"></h5>
                  <div id="eventList"></div>
               </div>

            </div>
         </main>
         <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
               <div class="text-muted small">Copyright &copy; PERSON WITH DISABILITY IN CARMONA CITY, CAVITE 2026</div>
            </div>
         </footer>
      </div>
   </div>

   
   <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="myModalLabel">Create Schedule</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <form id="scheduleForm" method="POST" action="php/ScheduleHandler.php">
                  <label for="eventTitle">Title:</label>
                  <input type="text" id="eventTitle" name="title" placeholder="Enter event title" required>

                  <label for="eventDate">Date:</label>
                  <input type="date" id="eventDate" name="event_date" required>

                  <label for="eventTime">Time:</label>
                  <input type="time" id="eventTime" name="event_time">

                  <label for="eventDesc">Description:</label>
                  <textarea id="eventDesc" name="description" placeholder="Add description..." rows="4"></textarea>

                  <div class="d-flex justify-content-end gap-2 mt-3">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-primary">Save</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   
   <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="editModalLabel">Edit Schedule</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <form id="editScheduleForm" method="POST" action="schedule.php?month=<?= $month ?>&year=<?= $year ?>">
                  <input type="hidden" id="editId" name="edit_id">

                  <label for="editTitle">Title:</label>
                  <input type="text" id="editTitle" name="edit_title" placeholder="Enter event title" required>

                  <label for="editDate">Date:</label>
                  <input type="date" id="editDate" name="edit_date" required>

                  <label for="editTime">Time:</label>
                  <input type="time" id="editTime" name="edit_time">

                  <label for="editDescription">Description:</label>
                  <textarea id="editDescription" name="edit_description" placeholder="Add description..." rows="4"></textarea>

                  <div class="d-flex justify-content-end gap-2 mt-3">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                     <button type="submit" class="btn btn-warning text-white">Update</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   
   <script>
      const phpEvents = <?= json_encode($events) ?>;
      const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      const currentMonth = <?= $month ?>;
      const currentYear = <?= $year ?>;

      function openEditModal(ev) {
         document.getElementById('editId').value = ev.schedule_id;
         document.getElementById('editTitle').value = ev.title;
         document.getElementById('editDate').value = ev.event_date;
         document.getElementById('editTime').value = ev.event_time ? ev.event_time.substring(0, 5) : '';
         document.getElementById('editDescription').value = ev.description || '';
         const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
         modal.show();
      }

      document.querySelectorAll('#calendarTable td[data-day]').forEach(cell => {
         cell.addEventListener('click', function() {
            const day = parseInt(this.dataset.day);
            const panel = document.getElementById('eventPanel');
            const title = document.getElementById('eventPanelTitle');
            const list = document.getElementById('eventList');

            title.textContent = monthNames[currentMonth] + ' ' + day + ', ' + currentYear;
            list.innerHTML = '';

            const dayEvents = phpEvents[day] || [];
            if (dayEvents.length === 0) {
               list.innerHTML = '<p class="text-muted">No events on this day.</p>';
            } else {
               dayEvents.forEach(ev => {
                  const timeStr = ev.event_time ?
                     new Date('1970-01-01T' + ev.event_time).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                     }) :
                     '';

                  const evJson = JSON.stringify(ev).replace(/'/g, "\\'");

                  list.innerHTML += `
                  <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:8px 10px;border-radius:6px;background:#f1f8ff;margin-bottom:8px;border-left:4px solid #007bff;">
                     <div>
                        <strong>${ev.title}</strong>
                        ${timeStr ? '<br><small>' + timeStr + '</small>' : ''}
                        ${ev.description ? '<br><small>' + ev.description + '</small>' : ''}
                     </div>
                     <div class="event-actions">
                        <button type="button" class="btn-edit-ev" title="Edit"
                           onclick='openEditModal(${JSON.stringify(ev)})'>
                           <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" style="margin:0;">
                           <input type="hidden" name="delete_id" value="${ev.schedule_id}">
                           <input type="hidden" name="month" value="${currentMonth}">
                           <input type="hidden" name="year" value="${currentYear}">
                           <button type="submit" class="btn-delete-ev" title="Delete"
                              onclick="return confirm('Delete this event?')">
                              <i class="fas fa-trash-alt"></i>
                           </button>
                        </form>
                     </div>
                  </div>`;
               });
            }
            panel.style.display = 'block';
         });
      });
   </script>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
   <script src="js/scripts.js"></script>
   <script src="js/admin.js"></script>
</body>

</html>
<?php $conn->close(); ?>