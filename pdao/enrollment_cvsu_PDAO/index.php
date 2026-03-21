<?php
session_start();
include 'php/ConnectToDb.php';
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COURSE COMPASS</title>
    <link rel="stylesheet" href="css/landingpage.css">
</head>
<style>
    :root {
        --landingpage-bg: url('../images/landingpage.png');
        ;
        --theme-color: #14a800;
        --hover-color: #4ddb00;
    }
</style>

<body>
    <header>
        <div class="modal" id="recoveryModal">
            <span class="close-recovery">&times;</span>
            <div class="modal-content modal-custom">
                <form class="reco-modal" method="POST" action="php/Recovery.php">
                    <h2>Recover Password:</h2>
                    <label for="recovery_email">Email</label>
                    <input type="email" name="recovery_email" id="recovery_email" placeholder="Enter your email" required>

                    <label for="otp" style="display:block;margin:0 0 4px 0;font-weight:600;">Email OTP</label>
                    <input type="number" name="otp" id="otp" minlength="6" placeholder="Enter verification code" required style="width:100%;padding:8px;margin:0 0 6px 0;box-sizing:border-box;">

                    <label for="recovery_newpass">New Password:</label>
                    <input type="password" name="recovery_newpass" id="recovery_newpass" minlength="8" placeholder="Enter new password" required>

                    <label for="confirm_recovery">Confirm New Password:</label>
                    <input type="password" name="confirm_recovery" id="confirm_recovery" minlength="8" placeholder="Confirm new password" required>
                    <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;justify-content:center;">
                        <button type="button" id="send-otp-btn" class="btn" style="min-width:140px;">Send OTP</button>
                        <button type="submit" name="reset" class="btn" style="min-width:160px;">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="messageModal" class="modal">
            <div class="modal-content">
                <span class="close-message">&times;</span>
                <div id="messageContent"></div>
            </div>
        </div>

        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="form-tabs">
                    <button class="tab-btn active" data-tab="login">Login</button>
                    <button class="tab-btn" data-tab="register">Register</button>
                </div>

                <form id="login-form" class="auth-form" method="POST" action="php/LoginAcc.php">
                    <label for="login-email">Email:</label>
                    <input type="email" id="login-email" name="login-email" required>

                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" name="login-password" required>
                    <button type="submit" name="login">Login</button>
                </form>
                <span class="link" id="forgot-btn" onclick="openRecovery()">Forgot Password?</span>

                <form id="register-form" class="auth-form" style="display: none;" method="POST" action="php/RegisterAcc.php">
                    <div class="reg-form-modal">
                        <div>
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required>

                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>


                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer-not-to-say">Prefer not to say</option>
                            </select>
                            <label for="register_otp" style="display:block;margin:6px 0 4px 0;font-weight:600;">Email OTP</label>
                            <input type="text" id="register_otp" name="register_otp" placeholder="Enter verification code" style="width:100%;padding:8px;box-sizing:border-box;margin-bottom:6px;">
                        </div>
                        <div>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>

                            <label for="confirm-password">Confirm Password:</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>

                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age" min="16" max="99" required>

                            <label for="sendotp">Send OTP</label>
                            <div for="sendotp" style="margin-bottom:8px;display:flex;justify-content:center;">
                                <button type="button" id="send-register-otp" class="btn" style="min-width:140px;">Send OTP</button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>

        <nav>
            <div class="logo">
                <img src="images/finallogo.png" width="150" height="150">
            </div>
        </nav>
    </header>
    <div class="container">
        <section class="h-text">
            <h1>Cavite State University</h1>
            <p>COURSE COMPASS</p>
            <span>Recommends courses based on a student's learning style, performance, and preferences.</span>
            <br><br>
            <div class="center-login">
                <a class="register" href="#">Register / Login</a>
            </div>
        </section>
    </div>
    <script src="js/landingpage.js"></script>
    <script>
        (function() {
            const btn = document.getElementById('send-otp-btn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                const emailInput = document.getElementById('recovery_email');
                const otpInput = document.getElementById('otp');
                if (!emailInput || !otpInput) return;
                const email = emailInput.value.trim();
                if (!email) {
                    showMessage('Please enter your email before requesting an OTP.', 'error');
                    return;
                }

                btn.disabled = true;
                btn.textContent = 'Sending...';

                fetch('php/SendOtp.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            recovery_email: email,
                            action: 'recovery'
                        })
                    }).then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message || 'OTP sent.', 'success');
                        } else {
                            showMessage(data.message || 'Failed to send OTP.', 'error');
                        }
                    }).catch(err => {
                        console.error(err);
                        showMessage('Network error while sending OTP.', 'error');
                    }).finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Send OTP';
                    });
            });
        })();
    </script>
    <script>
        (function() {
            const btn = document.getElementById('send-register-otp');
            if (!btn) return;
            btn.addEventListener('click', function() {
                const emailInput = document.getElementById('email');
                const otpInput = document.getElementById('register_otp');
                if (!emailInput || !otpInput) return;
                const email = emailInput.value.trim();
                if (!email) {
                    showMessage('Please enter your email before requesting an OTP.', 'error');
                    return;
                }

                btn.disabled = true;
                btn.textContent = 'Sending...';

                fetch('php/SendOtp.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            recovery_email: email,
                            action: 'register'
                        })
                    }).then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message || 'OTP sent.', 'success');
                        } else {
                            showMessage(data.message || 'Failed to send OTP.', 'error');
                        }
                    }).catch(err => {
                        console.error(err);
                        showMessage('Network error while sending OTP.', 'error');
                    }).finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Send OTP';
                    });
            });
        })();
    </script>
    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if (isset($_SESSION['error'])): ?>
                    showMessage('<?php echo $_SESSION['error']; ?>', 'error');
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    showMessage('<?php echo $_SESSION['success']; ?>', 'success');
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>

</html>