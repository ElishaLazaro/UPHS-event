<?php
session_start();
if (!$_SESSION['loggedin']) {
    header('Location: ../index.php');
    exit();
}

include '../php/ConnectToDb.php';

$announcements = [];
$result = $conn->query("SELECT * FROM announcements ORDER BY date DESC, time DESC");
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
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
        .ann-img-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .ann-img-preview {
            max-height: 280px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .no-img-placeholder {
            width: 60px;
            height: 60px;
            background: #e9ecef;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
        }
    </style>
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
                        <a class="nav-link active" href="announcement.php">
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
                <section class="admin-panel" aria-label="Announcement">

                    <div class="d-flex align-items-center mb-3">
                        <h2 class="m-0">Announcement</h2>
                        <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <i class="fas fa-plus me-1"></i> Create Announcement
                        </button>
                    </div>

                    
                    <div class="position-relative mb-3">
                        <input type="search" id="searchInput" class="form-control"
                            placeholder="Search by activity or description..."
                            autocomplete="off" spellcheck="false" />
                        <span class="material-icons search-icon" aria-hidden="true">search</span>
                    </div>

                    
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless shadow-sm mb-0 custom-table">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th class="text-muted py-3">Image</th>
                                    <th class="text-muted py-3">Date</th>
                                    <th class="text-muted py-3">Time</th>
                                    <th class="text-muted py-3">Activity</th>
                                    <th class="text-muted py-3">Description</th>
                                    <th class="text-muted py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="annTableBody" class="text-center">
                                <?php if (count($announcements) > 0): ?>
                                    <?php foreach ($announcements as $ann): ?>
                                        <tr class="ann-row"
                                            data-activity="<?= htmlspecialchars(strtolower($ann['activity'])) ?>"
                                            data-description="<?= htmlspecialchars(strtolower($ann['description'] ?? '')) ?>">
                                            <td class="align-middle">
                                                <?php if (!empty($ann['image'])): ?>
                                                    <img src="<?= htmlspecialchars($ann['image']) ?>" class="ann-img-thumb" alt="Image">
                                                <?php else: ?>
                                                    <div class="no-img-placeholder">
                                                        <i class="material-icons text-muted">image_not_supported</i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= htmlspecialchars(date('m/d/Y', strtotime($ann['date']))) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= htmlspecialchars(date('g:i A', strtotime($ann['time']))) ?>
                                            </td>
                                            <td class="align-middle"><?= htmlspecialchars($ann['activity']) ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($ann['description'] ?? '') ?></td>
                                            <td class="align-middle">
                                                
                                                <button class="btn btn-info btn-sm text-white"
                                                    onclick="viewAnnouncement(<?= htmlspecialchars(json_encode([
                                                                                    'image'       => $ann['image'] ?? '',
                                                                                    'date'        => date('m/d/Y', strtotime($ann['date'])),
                                                                                    'time'        => date('g:i A', strtotime($ann['time'])),
                                                                                    'activity'    => $ann['activity'],
                                                                                    'description' => $ann['description'] ?? '',
                                                                                ]), ENT_QUOTES) ?>)">
                                                    View
                                                </button>
                                                
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                                                                'announcement_id' => $ann['announcement_id'],
                                                                                'date'            => $ann['date'],
                                                                                'time'            => date('H:i', strtotime($ann['time'])),
                                                                                'activity'        => $ann['activity'],
                                                                                'description'     => $ann['description'] ?? '',
                                                                                'image'           => $ann['image'] ?? '',
                                                                            ]), ENT_QUOTES) ?>)">
                                                    Edit
                                                </button>
                                                
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete(<?= $ann['announcement_id'] ?>)">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="noDataRow">
                                        <td colspan="6" class="text-center py-4">
                                            <i class="material-icons" style="font-size:48px;color:#ccc;">campaign</i>
                                            <p class="text-muted mt-2">No announcements yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

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

    
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Time <span class="text-danger">*</span></label>
                                    <input type="time" name="time" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Activity / Title <span class="text-danger">*</span></label>
                                    <input type="text" name="activity" class="form-control" placeholder="e.g. Sportsfest 2026" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Enter description..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Image</label>
                                <div class="border rounded p-3 text-center h-100 d-flex flex-column justify-content-center">
                                    <img id="createImgPreview"
                                        src="https://via.placeholder.com/300x200?text=No+Image"
                                        class="img-fluid rounded mb-3" alt="Preview">
                                    <input type="file" name="image" class="form-control" accept="image/*"
                                        onchange="previewImg(event, 'createImgPreview')">
                                    <small class="text-muted mt-2">Optional — JPG, PNG, GIF, WEBP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewActivity">Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="viewImage" src="" alt="Image" class="ann-img-preview mb-3" style="display:none;">
                    <p><strong>Date:</strong> <span id="viewDate"></span></p>
                    <p><strong>Time:</strong> <span id="viewTime"></span></p>
                    <p><strong>Description:</strong></p>
                    <p id="viewDescription" class="text-muted"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <input type="hidden" name="announcement_id" id="editAnnId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="editDate" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Time <span class="text-danger">*</span></label>
                                    <input type="time" name="time" id="editTime" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Activity / Title <span class="text-danger">*</span></label>
                                    <input type="text" name="activity" id="editActivity" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="editDescription" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Image</label>
                                <div class="border rounded p-3 text-center h-100 d-flex flex-column justify-content-center">
                                    <img id="editImgPreview"
                                        src="https://via.placeholder.com/300x200?text=No+Image"
                                        class="img-fluid rounded mb-3" alt="Preview">
                                    <input type="file" name="image" class="form-control" accept="image/*"
                                        onchange="previewImg(event, 'editImgPreview')">
                                    <small class="text-muted mt-2">Leave blank to keep current image</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-1">
                    <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
                    <input type="hidden" id="deleteAnnId" value="">
                </div>
                <div class="modal-footer border-0 pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/user.js"></script>

    <script>
        
        function previewImg(event, imgId) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                document.getElementById(imgId).src = reader.result;
            };
            reader.readAsDataURL(file);
        }

        document.getElementById('announcementModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('createImgPreview').src = 'https://via.placeholder.com/300x200?text=No+Image';
            document.getElementById('createForm').reset();
        });

        document.querySelectorAll('.ann-img-thumb').forEach(img => {
            img.onerror = function() {
                this.style.display = 'none';
                const ph = document.createElement('div');
                ph.className = 'no-img-placeholder';
                ph.innerHTML = '<i class="material-icons text-muted">image_not_supported</i>';
                this.parentNode.appendChild(ph);
            };
        });

    
        document.getElementById('searchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.ann-row').forEach(row => {
                const activity = row.dataset.activity || '';
                const description = row.dataset.description || '';
                row.style.display = (activity.includes(q) || description.includes(q)) ? '' : 'none';
            });
        });

        function viewAnnouncement(data) {
            document.getElementById('viewActivity').textContent = data.activity || 'Announcement';
            document.getElementById('viewDate').textContent = data.date || '—';
            document.getElementById('viewTime').textContent = data.time || '—';
            document.getElementById('viewDescription').textContent = data.description || 'No description.';

            const img = document.getElementById('viewImage');
            if (data.image && data.image.trim() !== '') {
                img.src = data.image;
                img.style.display = 'block';
                img.onerror = () => {
                    img.style.display = 'none';
                };
            } else {
                img.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function openEditModal(data) {
            document.getElementById('editAnnId').value = data.announcement_id;
            document.getElementById('editDate').value = data.date;
            document.getElementById('editTime').value = data.time;
            document.getElementById('editActivity').value = data.activity;
            document.getElementById('editDescription').value = data.description;

            const preview = document.getElementById('editImgPreview');
            if (data.image && data.image.trim() !== '') {
                preview.src = data.image;
                preview.onerror = () => {
                    preview.src = 'https://via.placeholder.com/300x200?text=No+Image';
                };
            } else {
                preview.src = 'https://via.placeholder.com/300x200?text=No+Image';
            }

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function confirmDelete(id) {
            document.getElementById('deleteAnnId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const id = document.getElementById('deleteAnnId').value;
            fetch('php/announcement_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&announcement_id=' + id
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                });
        });

        document.getElementById('createForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'create');

            fetch('php/announcement_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                });
        });

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

            fetch('php/announcement_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred.');
                });
        });
    </script>
</body>

</html>