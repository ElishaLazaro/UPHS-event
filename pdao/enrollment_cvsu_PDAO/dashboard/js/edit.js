document.getElementById("themeColor").addEventListener("input", function () {
  document.getElementById("themeColorValue").textContent = this.value;
});
document.getElementById("hoverColor").addEventListener("input", function () {
  document.getElementById("hoverColorValue").textContent = this.value;
});

function formatFileSize(bytes) {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
}

function handleFileInputChange(inputId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(inputId + "Preview");
  const label = document.getElementById(inputId + "Label");
  const thumb = document.getElementById(inputId + "Thumb");
  const name = document.getElementById(inputId + "Name");
  const size = document.getElementById(inputId + "Size");

  if (input.files && input.files[0]) {
    const file = input.files[0];
    const fileName = file.name;
    const fileSize = formatFileSize(file.size);

    name.textContent = fileName;
    size.textContent = fileSize;

    if (file.type.match("image.*")) {
      const reader = new FileReader();
      reader.onload = function (e) {
        thumb.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    preview.style.display = "flex";
    label.textContent = "Change file";
  }
}

function handleFileSelect(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener("change", function () {
    handleFileInputChange(inputId);
  });
}

function removeFile(inputId) {
  const oldInput = document.getElementById(inputId);
  const newInput = oldInput.cloneNode(true);
  oldInput.parentNode.replaceChild(newInput, oldInput);

  handleFileSelect(inputId);

  document.getElementById(inputId + "Preview").style.display = "none";
  document.getElementById(inputId + "Label").textContent = "Choose a file";
}

function setPreselectedFile(inputId, fileData) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(inputId + "Preview");
  const nameElement = document.getElementById(inputId + "Name");
  const sizeElement = document.getElementById(inputId + "Size");
  const thumb = document.getElementById(inputId + "Thumb");
  const label = document.getElementById(inputId + "Label");

  fetch(fileData.url)
    .then((res) => res.blob())
    .then((blob) => {
      const file = new File([blob], fileData.name, { type: fileData.type });

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      input.files = dataTransfer.files;

      nameElement.textContent = fileData.name;
      sizeElement.textContent = formatFileSize(file.size);
      thumb.src = fileData.url;
      preview.style.display = "flex";
      label.textContent = "Change file";
    });
}

handleFileSelect("homepage_bg");
handleFileSelect("bg_image");
handleFileSelect("logo");
handleFileSelect("univlogo");
handleFileSelect("carouselcard1");
handleFileSelect("carouselcard2");
handleFileSelect("carouselcard3");
handleFileSelect("carouselcard4");
handleFileSelect("carouselcard5");
handleFileSelect("eventcarouselcard1");
handleFileSelect("eventcarouselcard2");
handleFileSelect("eventcarouselcard3");
handleFileSelect("eventcarouselcard4");
handleFileSelect("eventcarouselcard5");
handleFileSelect("courses_image");

(function () {
  const btn = document.getElementById("editDropdownBtn");
  const menu = document.getElementById("editDropdownMenu");
  const items = menu.querySelectorAll(".dropdown-item");
  let menuOpen = false;

  btn.addEventListener("click", function (e) {
    e.stopPropagation();
    menu.classList.toggle("show");
    menuOpen = menu.classList.contains("show");
  });

  document.addEventListener("click", function (e) {
    if (menuOpen && !menu.contains(e.target) && e.target !== btn) {
      menu.classList.remove("show");
      menuOpen = false;
    }
  });

  items.forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href").replace("#", "");
      const target = document.getElementById(targetId);
      if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "start" });
        menu.classList.remove("show");
        menuOpen = false;
      }
    });
  });

  const sectionIds = Array.from(items).map((i) =>
    i.getAttribute("href").replace("#", "")
  );
  const sectionEls = sectionIds.map((id) => document.getElementById(id));
  window.addEventListener("scroll", function () {
    let activeIdx = -1;
    for (let i = 0; i < sectionEls.length; i++) {
      const rect = sectionEls[i].getBoundingClientRect();
      if (rect.top <= 80) activeIdx = i;
    }
    items.forEach((item, idx) => {
      if (idx === activeIdx) item.classList.add("active");
      else item.classList.remove("active");
    });
  });
})();

(function () {
  const links = document.querySelectorAll(".edit-section-link");
  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href").replace("#", "");
      const target = document.getElementById(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
        const sidebar = document.getElementById("layoutSidenav_nav");
        if (window.innerWidth < 992 && sidebar) {
          document.body.classList.remove("sb-sidenav-toggled");
        }
      }
    });
  });
})();

window.addEventListener("DOMContentLoaded", function () {
  const upBtn = document.getElementById("scrollUpBtn");
  const downBtn = document.getElementById("scrollDownBtn");
  if (upBtn) {
    upBtn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
  if (downBtn) {
    downBtn.addEventListener("click", function () {
      window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
    });
  }
});
