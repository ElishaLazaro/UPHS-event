// Modal functionality
const modal = document.getElementById("modal");
const recomodal = document.getElementById("recoveryModal");
const messageModal = document.getElementById("messageModal");
const registerLink = document.querySelector(".register");
const closeBtn = document.querySelector(".close");
const closeMessageBtn = document.querySelector(".close-message");
const closeRecoveryBtn = document.querySelector(".close-recovery");

function openRecovery() {
  document.getElementById("modal").style.display = "none";
  document.getElementById("recoveryModal").style.display = "flex";
  // Reset password match messages when opening recovery modal
  passwordMatchMsg2.textContent = "";
}

registerLink.addEventListener("click", function (e) {
  e.preventDefault();
  modal.style.display = "block";
  showTab("login");
});

closeBtn.addEventListener("click", function () {
  modal.style.display = "none";
});

closeMessageBtn.addEventListener("click", function () {
  messageModal.style.display = "none";
});

closeRecoveryBtn.addEventListener("click", function () {
  recomodal.style.display = "none";
});

window.addEventListener("click", function (e) {
  if (e.target == modal || e.target == recomodal) {
    recomodal.style.display = "none";
    modal.style.display = "none";
  }
  if (e.target == messageModal) {
    messageModalc;
  }
});

// Tab functionality
const tabButtons = document.querySelectorAll(".tab-btn");
tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const tabName = button.getAttribute("data-tab");
    showTab(tabName);
    // Clear form when switching to register tab
    if (tabName === "register") {
      document.getElementById("register-form").reset();
      document.getElementById("forgot-btn").style.display = "none";
    }
    if (tabName === "login") {
      document.getElementById("forgot-btn").style.display = "flex";
    }
  });
});

function showTab(tabName) {
  document.querySelectorAll(".auth-form").forEach((form) => {
    form.style.display = "none";
  });

  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  document.getElementById(`${tabName}-form`).style.display = "block";
  document
    .querySelector(`.tab-btn[data-tab="${tabName}"]`)
    .classList.add("active");
}

const messageContent = document.getElementById("messageContent");

function showMessage(message, type = "error") {
  messageContent.innerHTML = `
        <div class="${type}">${message}</div>
        ${type === "success" ? "" : ""}
    `;
  messageModal.style.display = "block";
}

document
  .getElementById("register-form")
  .addEventListener("submit", function (e) {
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm-password").value;
    const age = document.getElementById("age").value;
    const gender = document.getElementById("gender").value;

    if (password !== confirmPassword) {
      e.preventDefault();
      return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      return;
    }

    if (age < 16 || age > 99) {
      e.preventDefault();
      return;
    }
  });

const recoveryNewPass = document.getElementById("recovery_newpass");
const confirmRecovery = document.getElementById("confirm_recovery");
const passwordMatchMsg2 = document.createElement("div");
passwordMatchMsg2.className = "password-match";
confirmRecovery.parentNode.insertBefore(
  passwordMatchMsg2,
  confirmRecovery.nextSibling,
);

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm-password");

const passwordMatchMsg = document.createElement("div");
passwordMatchMsg.className = "password-match";
confirmPassword.parentNode.insertBefore(
  passwordMatchMsg,
  confirmPassword.nextSibling,
);

function checkPasswordMatch(num) {
  if (num === 1) {
    if (password.value && confirmPassword.value) {
      if (password.value === confirmPassword.value) {
        passwordMatchMsg.textContent = "Passwords match!";
        passwordMatchMsg.className = "password-match match";
      } else {
        passwordMatchMsg.textContent = "Passwords do not match!";
        passwordMatchMsg.className = "password-match no-match";
      }
    } else {
      passwordMatchMsg.textContent = "";
    }
  } else if (num === 3) {
    if (recoveryNewPass.value && confirmRecovery.value) {
      if (recoveryNewPass.value === confirmRecovery.value) {
        passwordMatchMsg2.textContent = "Passwords match!";
        passwordMatchMsg2.className = "password-match match";
      } else {
        passwordMatchMsg2.textContent = "Passwords do not match!";
        passwordMatchMsg2.className = "password-match no-match";
      }
    } else {
      passwordMatchMsg2.textContent = "";
    }
  }
}

password.addEventListener("input", () => checkPasswordMatch(1));
confirmPassword.addEventListener("input", () => checkPasswordMatch(1));
recoveryNewPass.addEventListener("input", () => checkPasswordMatch(3));
confirmRecovery.addEventListener("input", () => checkPasswordMatch(3));
