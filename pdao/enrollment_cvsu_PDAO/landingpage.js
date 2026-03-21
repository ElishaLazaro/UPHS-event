const modal = document.getElementById("modal");
const messageModal = document.getElementById("messageModal");
const registerLink = document.querySelector(".register");
const closeBtn = document.querySelector(".close");
const closeMessageBtn = document.querySelector(".close-message");

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

window.addEventListener("click", function (e) {
  if (e.target == modal) {
    modal.style.display = "none";
  }
  if (e.target == messageModal) {
    messageModal.style.display = "none";
  }
});

const tabButtons = document.querySelectorAll(".tab-btn");
tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const tabName = button.getAttribute("data-tab");
    showTab(tabName);
    if (tabName === "register") {
      document.getElementById("register-form").reset();
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
  messageModal.style.display = "flex";
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

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm-password");
const passwordMatchMsg = document.createElement("div");
passwordMatchMsg.className = "password-match";
confirmPassword.parentNode.insertBefore(
  passwordMatchMsg,
  confirmPassword.nextSibling
);

function checkPasswordMatch() {
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
}

password.addEventListener("input", checkPasswordMatch);
confirmPassword.addEventListener("input", checkPasswordMatch);
