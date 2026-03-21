<?php 
  session_start();
  include '../../actions/connDB.php';

  if(isset($_POST['signout'])){
    session_destroy();
    header("Location: ../../login.php");
  }

  if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
  }
  else{
    $id = $_SESSION['user_id'];
    $sql = "SELECT u_role FROM tbl_user_role WHERE id = $id";
    $result = mysqli_query($connection, $sql);

    while($row = mysqli_fetch_assoc($result)){
      if($row['u_role'] == "coach"){
        header("Location: ../../coaches.php");
      }
      elseif($row['u_role'] == "customer"){
        header("Location: ../plan.php");
      }
    }
  }

  // Fetch user data
  $user_data = null;
  if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $sql = "SELECT user_id, username, email, f_name, m_name, l_name, sch_id, profile_picture FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
  }

  // Handle form submissions
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Update profile information
    if(isset($_POST['update_profile'])) {
      $firstName = $_POST['firstName'];
      $lastName = $_POST['lastName'];
      
      $sql = "UPDATE users SET f_name = ?, l_name = ? WHERE user_id = ?";
      $stmt = mysqli_prepare($connection, $sql);
      mysqli_stmt_bind_param($stmt, "ssi", $firstName, $lastName, $id);
      
      if(mysqli_stmt_execute($stmt)) {
        echo '<script>alert("Profile updated successfully!");</script>';
        echo '<script>window.location.href = "pages-account.php";</script>';
      } else {
        echo '<script>alert("Error updating profile!");</script>';
      }
    }
    
    // Upload profile picture
    if(isset($_POST['upload_picture'])) {
      $upload_dir = '../../uploads/profiles/';
      
      // Create directory if it doesn't exist
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }
      
      if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if(in_array($file_type, $allowed_types)) {
          // Delete old profile picture if exists
          if(!empty($user_data['profile_picture']) && file_exists($upload_dir . $user_data['profile_picture'])) {
            unlink($upload_dir . $user_data['profile_picture']);
          }
          
          // Upload new picture
          $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
          $new_filename = 'profile_' . $id . '_' . time() . '.' . $file_extension;
          $upload_path = $upload_dir . $new_filename;
          
          if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
            $sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($connection, $sql);
            mysqli_stmt_bind_param($stmt, "si", $new_filename, $id);
            
            if(mysqli_stmt_execute($stmt)) {
              echo '<script>alert("Profile picture updated successfully!");</script>';
              echo '<script>window.location.href = "pages-account.php";</script>';
            }
          } else {
            echo '<script>alert("Error uploading picture!");</script>';
          }
        } else {
          echo '<script>alert("Invalid file type. Please upload JPG, PNG, GIF, or WEBP.");</script>';
        }
      }
    }
    
    // Reset profile picture
    if(isset($_POST['reset_picture'])) {
      $upload_dir = '../../uploads/profiles/';
      
      // Delete current profile picture
      if(!empty($user_data['profile_picture']) && file_exists($upload_dir . $user_data['profile_picture'])) {
        unlink($upload_dir . $user_data['profile_picture']);
      }
      
      // Set to NULL in database
      $sql = "UPDATE users SET profile_picture = NULL WHERE user_id = ?";
      $stmt = mysqli_prepare($connection, $sql);
      mysqli_stmt_bind_param($stmt, "i", $id);
      
      if(mysqli_stmt_execute($stmt)) {
        echo '<script>alert("Profile picture reset successfully!");</script>';
        echo '<script>window.location.href = "pages-account.php";</script>';
      }
    }
  }

  // Set profile picture path
  $profile_pic_path = '../assets/img/avatars/1.png'; // default
  if(!empty($user_data['profile_picture'])) {
    $profile_pic_path = '../../uploads/profiles/' . $user_data['profile_picture'];
  }
?>
<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="description" content="" />
  <title>Account Settings - GymStudio</title>
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/vendor/fonts/iconify-icons.css" />
  <link rel="stylesheet" href="../assets/vendor/css/core.css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />
  <script src="../assets/vendor/js/helpers.js"></script>
  <script src="../assets/js/config.js"></script>
</head>
<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Sidebar (keep your existing sidebar code) -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <!-- Your existing sidebar code here -->
        <div class="app-brand demo">
          <a href="index.php" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bold ms-2">GymStudio</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
          </a>
        </div>
        <div class="menu-divider mt-0"></div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item">
            <a href="index.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-smile"></i>
              <div class="text-truncate">Dashboard</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="logbook.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-book"></i>
              <div class="text-truncate">Logbook</div>
            </a>
          </li>
          <li class="menu-item active">
            <a href="pages-account.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div class="text-truncate">Account Settings</div>
            </a>
          </li>
        </ul>
      </aside>

      <div class="layout-page">
        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
              <i class="icon-base bx bx-menu icon-md"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
            <div class="navbar-nav align-items-center me-auto">
              <div class="nav-item d-flex align-items-center">
                <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none" placeholder="Search..." aria-label="Search..." />
              </div>
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-md-auto">
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt class="w-px-40 h-auto rounded-circle" onerror="this.src='../assets/img/avatars/1.png'" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt class="w-px-40 h-auto rounded-circle" onerror="this.src='../assets/img/avatars/1.png'" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <h6 class="mb-0"><?php echo htmlspecialchars($user_data['f_name'] . ' ' . $user_data['l_name']); ?></h6>
                          <small class="text-body-secondary"><?php echo htmlspecialchars($user_data['username']); ?></small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  <li><a class="dropdown-item" href="pages-account.php"><i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span></a></li>
                  <li><div class="dropdown-divider my-1"></div></li>
                  <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <button name="signout" type="submit" class="dropdown-item">
                      <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                    </button>
                  </form>
                </ul>
              </li>
            </ul>
          </div>
        </nav>

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Account Settings Card -->
            <div class="card mb-4">
              <div class="card-body">
                <div class="d-flex align-items-start align-items-sm-center gap-4 pb-4 border-bottom">
                  <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" 
                       alt="user-avatar" 
                       class="d-block w-px-100 h-px-100 rounded" 
                       id="uploadedAvatar" 
                       onerror="this.src='../assets/img/avatars/1.png'" />
                  <div class="button-wrapper">
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                      <label for="upload" class="btn btn-primary me-3 mb-2" tabindex="0">
                        <span class="d-none d-sm-block">Upload new photo</span>
                        <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                        <input type="file" id="upload" name="profile_picture" class="account-file-input" hidden accept="image/png, image/jpeg, image/gif, image/webp" onchange="previewAndUpload(this)" />
                      </label>
                    </form>
                    <form method="POST" style="display: inline;">
                      <button type="submit" name="reset_picture" class="btn btn-outline-secondary account-image-reset mb-2">
                        <i class="icon-base bx bx-reset d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Reset</span>
                      </button>
                    </form>
                    <div class="text-muted">Allowed JPG, PNG, GIF or WEBP.</div>
                  </div>
                </div>

                <form id="formAccountSettings" method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" class="pt-4">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label for="firstName" class="form-label">First Name</label>
                      <input class="form-control" type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user_data['f_name']); ?>" required autofocus />
                    </div>
                    <div class="col-md-6">
                      <label for="lastName" class="form-label">Last Name</label>
                      <input class="form-control" type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($user_data['l_name']); ?>" required />
                    </div>
                    <div class="col-md-6">
                      <label for="email" class="form-label">Email</label>
                      <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled />
                    </div>
                    <div class="col-md-6">
                      <label for="username" class="form-label">Username</label>
                      <input class="form-control" type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled />
                    </div>
                    <div class="col-md-6">
                      <label for="sch_id" class="form-label">School ID</label>
                      <input class="form-control" type="text" id="sch_id" name="sch_id" value="<?php echo htmlspecialchars($user_data['sch_id']); ?>" disabled />
                    </div>
                  </div>
                  <div class="mt-4">
                    <button type="submit" name="update_profile" class="btn btn-primary me-2">Save changes</button>
                    <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Delete Account Card -->
            <div class="card">
              <h5 class="card-header">Delete Account</h5>
              <div class="card-body">
                <div class="alert alert-warning mb-4">
                  <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                  <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                </div>
                <form id="formAccountDeactivation" method="POST">
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" required />
                    <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
                  </div>
                  <button type="submit" name="deactivate_account" class="btn btn-danger deactivate-account">Deactivate Account</button>
                </form>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <script src="../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../assets/vendor/libs/popper/popper.js"></script>
  <script src="../assets/vendor/js/bootstrap.js"></script>
  <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="../assets/vendor/js/menu.js"></script>
  <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="../assets/js/dashboards-analytics.js"></script>

  <script>
    // Preview and auto-upload profile picture
    function previewAndUpload(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          document.getElementById('uploadedAvatar').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
        
        // Auto-submit the form
        const formData = new FormData();
        formData.append('profile_picture', input.files[0]);
        formData.append('upload_picture', '1');
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
          method: 'POST',
          body: formData
        })
        .then(response => response.text())
        .then(data => {
          alert('Profile picture updated successfully!');
          window.location.reload();
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error uploading picture!');
        });
      }
    }
  </script>
</body>
</html>