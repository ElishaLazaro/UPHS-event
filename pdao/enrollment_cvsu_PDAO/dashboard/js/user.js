function generatePastelColor() {
  const h = Math.floor(Math.random() * 360);
  const s = Math.floor(Math.random() * 30) + 30;
  const l = Math.floor(Math.random() * 15) + 70;
  return `hsl(${h},${s}%,${l}%)`;
}

function renderQuizResults(data) {
  const aggregated = data.reduce((acc, curr) => {
    const found = acc.find((item) => item.subject === curr.subject);
    if (found) {
      found.correct += Number(curr.correct);
      found.total += Number(curr.total);
    } else {
      acc.push({
        subject: curr.subject,
        correct: Number(curr.correct),
        total: Number(curr.total),
        color: generatePastelColor(),
      });
    }
    return acc;
  }, []);

  aggregated.forEach((d) => {
    d.score = (d.correct / d.total) * 100;
  });

  const totalCorrect = aggregated.reduce((sum, d) => sum + d.correct, 0);

  const svgGroup = document.getElementById("pie-slices");
  const radius = 140;
  const center = { x: 0, y: 0 };

  while (svgGroup.firstChild) svgGroup.removeChild(svgGroup.firstChild);

  function polarToCartesian(cx, cy, r, angle) {
    const rad = ((angle - 90) * Math.PI) / 180;
    return {
      x: cx + r * Math.cos(rad),
      y: cy + r * Math.sin(rad),
    };
  }

  function describeArc(cx, cy, r, startAngle, endAngle) {
    const start = polarToCartesian(cx, cy, r, endAngle);
    const end = polarToCartesian(cx, cy, r, startAngle);
    const largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;
    return `M${start.x} ${start.y} A${r} ${r} 0 ${largeArcFlag} 0 ${end.x} ${end.y} L${cx} ${cy} Z`;
  }

  let currentAngle = 0;
  aggregated.forEach((subject) => {
    if (subject.correct === 0) return;
    const sliceAngle = (subject.correct / totalCorrect) * 360;

    const pathData = describeArc(
      center.x,
      center.y,
      radius,
      currentAngle,
      currentAngle + sliceAngle
    );
    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("d", pathData);
    path.setAttribute("fill", subject.color);
    path.setAttribute("stroke", "#fff");
    path.setAttribute("stroke-width", "2");
    path.setAttribute(
      "aria-label",
      `${subject.subject}: ${subject.score.toFixed(1)}% correct`
    );
    svgGroup.appendChild(path);

    const midAngle = currentAngle + sliceAngle / 2;
    const labelPos = polarToCartesian(
      center.x,
      center.y,
      radius * 0.65,
      midAngle
    );
    if (sliceAngle >= 10) {
      const text = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "text"
      );
      text.setAttribute("x", labelPos.x);
      text.setAttribute("y", labelPos.y);
      text.setAttribute("fill", "#374151");
      text.setAttribute("font-size", "16");
      text.setAttribute("font-weight", "600");
      text.setAttribute("text-anchor", "middle");
      text.setAttribute("dominant-baseline", "middle");
      text.style.userSelect = "none";
      text.textContent = `${subject.score.toFixed(0)}%`;
      svgGroup.appendChild(text);
    }
    currentAngle += sliceAngle;
  });

  const legendContainer = document.querySelector(".legend-container");
  legendContainer.innerHTML = "";
  aggregated.forEach((d) => {
    const item = document.createElement("div");
    item.className = "legend-item";

    const colorBox = document.createElement("div");
    colorBox.className = "color-box";
    colorBox.style.backgroundColor = d.color;
    colorBox.setAttribute("aria-hidden", "true");

    const legendText = document.createElement("div");
    legendText.className = "legend-text";

    const name = document.createElement("div");
    name.textContent = d.subject;
    name.style.fontWeight = "700";
    name.style.color = "#111827";

    const sub = document.createElement("div");
    sub.className = "legend-subtext";
    sub.textContent = `${d.correct} out of ${d.total} correct`;

    legendText.appendChild(name);
    legendText.appendChild(sub);

    item.appendChild(colorBox);
    item.appendChild(legendText);

    legendContainer.appendChild(item);
  });

  const highestScore = Math.max(...aggregated.map((d) => d.score));
  const topSubjects = aggregated
    .filter((d) => d.score === highestScore)
    .map((d) => d.subject);

  let recommendationText;
  if (topSubjects.length === 1) {
    const subject = topSubjects[0];
    recommendationText = `Recommended Course: ${subject}`;
  } else {
    const courses = topSubjects
      .map((subj) => {
        return `${subj}`;
      })
      .join(" | ");
    recommendationText = `Recommended Courses for top subjects: ${courses}`;
  }

  const recElem = document.querySelector(".recommendation");
  recElem.textContent = recommendationText;
}

let bsModal = null;

document.addEventListener("DOMContentLoaded", function () {
  const viewButtons = document.querySelectorAll(".btn-view");
  viewButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const userId = this.getAttribute("data-user-id");

      if (!bsModal) {
        const modalEl = document.getElementById("viewTestModal");
        bsModal = new bootstrap.Modal(modalEl);
      }

      fetch(`../php/Get_Score.php?user_id=${userId}`)
        .then((response) => {
          if (!response.ok)
            throw new Error(`Network response not ok: ${response.statusText}`);
          return response.json();
        })
        .then((data) => {
          if (!Array.isArray(data) || data.length === 0) {
            throw new Error("Invalid or empty data received");
          }
          renderQuizResults(data);
          bsModal.show();
        })
        .catch((error) => {
          console.error("Fetch or rendering error:", error);
          alert("Error loading test results. Please try again.");
        });
    });
  });

  const searchInput = document.getElementById("searchInput");
  searchInput.addEventListener("input", function (e) {
    const searchText = e.target.value.toLowerCase();
    const rows = document.querySelectorAll("#userTableBody tr");

    rows.forEach((row) => {
      const username = row.children[1].textContent.toLowerCase();
      const email = row.children[2].textContent.toLowerCase();

      if (username.includes(searchText) || email.includes(searchText)) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  });

  const deleteButtons = document.querySelectorAll(".btn-delete");
  let deleteUserId = null;
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      deleteUserId = this.getAttribute("data-user-id");
      document.getElementById("deleteUserId").value = deleteUserId;
      const deleteModalEl = document.getElementById("deleteUserModal");
      const deleteModal = new bootstrap.Modal(deleteModalEl);
      deleteModal.show();
    });
  });

  document
    .getElementById("confirmDeleteUserBtn")
    .addEventListener("click", function () {
      const userId = document.getElementById("deleteUserId").value;
      fetch("php/DeleteUser.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `user_id=${encodeURIComponent(userId)}`,
      })
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            bootstrap.Modal.getInstance(
              document.getElementById("deleteUserModal")
            ).hide();
            window.location.href = window.location.pathname;
          } else {
            alert(result.message || "Failed to delete user.");
          }
        })
        .catch(() => {
          alert("Error deleting user.");
        });
    });

  // Bin modal and AJAX logic
  let binUserId = null;
  let binAction = null;
  const binModalEl = document.getElementById("binConfirmModal");
  if (binModalEl) {
    const binModal = new bootstrap.Modal(binModalEl);
    const binMsg = document.getElementById("binConfirmModalMsg");
    const binUserIdInput = document.getElementById("binActionUserId");
    const binActionInput = document.getElementById("binActionType");

    document.querySelectorAll(".btn-restore").forEach((btn) => {
      btn.addEventListener("click", function () {
        binUserId = this.dataset.userId;
        binAction = "restore";
        binMsg.textContent = "Are you sure you want to restore this user?";
        binUserIdInput.value = binUserId;
        binActionInput.value = binAction;
        binModal.show();
      });
    });
    document.querySelectorAll(".btn-delete-perm").forEach((btn) => {
      btn.addEventListener("click", function () {
        binUserId = this.dataset.userId;
        binAction = "delete";
        binMsg.textContent =
          "Permanently delete this user? This cannot be undone.";
        binUserIdInput.value = binUserId;
        binActionInput.value = binAction;
        binModal.show();
      });
    });
    document
      .getElementById("binConfirmBtn")
      .addEventListener("click", function () {
        const userId = binUserIdInput.value;
        const action = binActionInput.value;
        let url = "",
          body = "";
        if (action === "restore") {
          url = "php/RestoreUser.php";
          body = "user_id=" + encodeURIComponent(userId);
        } else if (action === "delete") {
          url = "php/DeleteUserPerm.php";
          body = "user_id=" + encodeURIComponent(userId);
        }
        if (url) {
          fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body,
          })
            .then((res) => res.json())
            .then((data) => {
              binModal.hide();
              if (data.success) window.location.href = window.location.pathname;
              else alert(data.message || "Action failed.");
            });
        }
      });
  }
});
