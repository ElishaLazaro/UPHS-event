<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="../../public/F&E_logo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css">
    <!-- <link rel="stylesheet" href="../css/responsive.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <title>PWD-Carmona City</title>
</head>

<body>
    <br /><br /><br />
    <div id="preloader">
        <div id="status">
            <div class="la-ball-triangle-path">
            </div>
        </div>
    </div>

    <div class="container login-container">
        <div class="background">
            <img src="img/bg1.png" alt="">
        </div>
        <br /><br /><br /><br /><br />
        <div class="title-banner">
            <div class="title-content">
                <a href="../../index.php" class="title-link">
                    MIS-LWD
                </a>
            </div>
        </div>

        <form id="loginForm" class="form active" method="POST" action="../php/LoginAcc.php?role=admin">
            <h1>Sign in</h1>
            <p>Welcome back! Please sign in to your account.</p>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label for="username">Username:</label>
            <input type="text" name="login-email" placeholder="Username" required autocomplete="off" />

            <label class="signin-label">Password:</label>

            <div class="password-wrapper">
                <input type="password" id="password-input" name="login-password" placeholder="Password" required />
                <button type="button" class="toggle-password-btn" onclick="togglePassword()" tabindex="-1">
                    <i id="eye-icon" class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="login-options">
                <label>
                    <input type="checkbox" name="remember"> Remember Me
                </label>
                <a href="forgot_password_steps/forgot-password.php" class="forgot-pass">Forgot Password?</a>
            </div>

            <button type="submit" class="btn">
                <i class="fa fa-sign-in-alt"></i> &nbsp; Sign in
            </button>
        </form>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 order-last order-md-first">
                    <div class="copyright text-center text-md-start">
                        <p class="text-sm">
                            &copy; MIS-LWD. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="../src/js/login.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>

    <script>
        window.addEventListener("load", function() {
            const preloader = document.getElementById("preloader");
            preloader.style.opacity = "1";

            setTimeout(() => {
                preloader.style.transition = "opacity 0.6s ease";
                preloader.style.opacity = "0";
            }, 200);

            setTimeout(() => {
                preloader.style.display = "none";
            }, 800);
        });
    </script>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password-input');
            const eyeIcon = document.getElementById('eye-icon');

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-auto-dismiss');

            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    alert.addEventListener('transitionend', () => {
                        alert.remove();
                    });
                }, 5000);
            });
        });
    </script>

    <script>
        window.addEventListener("load", function() {
            const preloader = document.getElementById("preloader");
            preloader.style.opacity = "1";

            setTimeout(() => {
                preloader.style.transition = "opacity 0.6s ease";
                preloader.style.opacity = "0";
            }, 200);

            setTimeout(() => {
                preloader.style.display = "none";
            }, 800);
        });
    </script>
</body>

</html>