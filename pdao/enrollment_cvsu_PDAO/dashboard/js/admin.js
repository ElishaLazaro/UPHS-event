function animateCounter(el, target) {
  const duration = 1400;
  const frameDuration = 1000 / 60;
  const totalFrames = Math.round(duration / frameDuration);
  let frame = 0;
  const countTo = target;

  const counter = () => {
    frame++;
    const progress = Math.min(frame / totalFrames, 1);
    const currentCount = Math.floor(progress * countTo);
    el.textContent = currentCount.toLocaleString();

    if (progress < 1) {
      requestAnimationFrame(counter);
    } else {
      el.textContent = countTo.toLocaleString();
    }
  };
  counter();
}

document.addEventListener("DOMContentLoaded", () => {
  fetchDashboardData();
});

let skillsChart = null;
let currentPage = 1;
const logsPerPage = 8;
let allLogs = [];

async function fetchDashboardData() {
  document.getElementById("skillsChartLoading").style.display = "block";
  document.getElementById("activityLogsLoading").style.display = "block";

  try {
    let dashboardData;
    try {
      const response = await fetch("php/Get_Data.php");
      dashboardData = await response.json();
    } catch (_) {}

    updateSummaryCards(dashboardData.summary);

    renderSkillsChart(dashboardData.skills);

    allLogs = dashboardData.logs || [];
    renderActivityLogs();
  } catch (error) {
    console.error("Error fetching dashboard data:", error);
  } finally {
    document.getElementById("skillsChartLoading").style.display = "none";
    document.getElementById("activityLogsLoading").style.display = "none";
  }
}

function updateSummaryCards(data) {
  const totalUsersEl = document.getElementById("totalUsers");
  const totalTestsEl = document.getElementById("totalTests");
  const totalSkillsEl = document.getElementById("totalSkills");
  animateCounter(totalUsersEl, data.totalUsers || 0);
  animateCounter(totalTestsEl, data.totalTests || 0);
  animateCounter(totalSkillsEl, data.recommended || 0);
}

function renderSkillsChart(skills) {
  if (!skillsChart) {
    const ctx = document.getElementById("skillsChart").getContext("2d");
    const labels = skills.map((skill) => skill.name);
    const data = skills.map((skill) => skill.count);

    skillsChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Most Recommended Skills",
            data: data,
            backgroundColor: "rgba(99, 102, 241, 0.2)",
            borderColor: "rgba(99, 102, 241, 1)",
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            pointRadius: 6,
            pointHoverRadius: 8,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: "Number of Recommendations",
              font: { weight: "600" },
            },
            ticks: {
              precision: 0,
            },
          },
          x: {
            title: {
              display: true,
              text: "Skills",
              font: { weight: "600" },
            },
          },
        },
        plugins: {
          legend: { display: true, position: "top" },
          tooltip: { mode: "index", intersect: false },
        },
      },
    });
  } else {
    skillsChart.data.labels = skills.map((s) => s.name);
    skillsChart.data.datasets[0].data = skills.map((s) => s.count);
    skillsChart.update();
  }
}

function renderActivityLogs() {
  const tb = document.getElementById("activityLogs");
  const searchTerm = document
    .getElementById("searchBar")
    .value.trim()
    .toLowerCase();
  const filteredLogs = allLogs.filter(
    (log) =>
      log.username.toLowerCase().includes(searchTerm) ||
      log.activity.toLowerCase().includes(searchTerm) ||
      log.timestamp.toLowerCase().includes(searchTerm)
  );
  const totalPages = Math.ceil(filteredLogs.length / logsPerPage);
  if (currentPage > totalPages) currentPage = totalPages || 1;

  const start = (currentPage - 1) * logsPerPage;
  const paginatedLogs = filteredLogs.slice(start, start + logsPerPage);

  tb.innerHTML = "";
  if (paginatedLogs.length === 0) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 3;
    td.className = "text-center text-muted";
    td.textContent = "No matching activity logs found.";
    tr.appendChild(td);
    tb.appendChild(tr);
  } else {
    for (const log of paginatedLogs) {
      const tr = document.createElement("tr");
      tr.innerHTML = `
            <td data-label="Username">${log.username}</td>
            <td data-label="Activity">${log.activity}</td>
            <td data-label="Timestamp">${log.timestamp}</td>
          `;
      tb.appendChild(tr);
    }
  }
  renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
  const container = document.getElementById("paginationControls");
  container.innerHTML = "";
  if (totalPages <= 1) return;

  const prevBtn = document.createElement("button");
  prevBtn.textContent = "Previous";
  prevBtn.disabled = currentPage === 1;
  prevBtn.setAttribute("aria-label", "Previous page");
  prevBtn.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      renderActivityLogs();
      focusPaginationButton(currentPage);
    }
  });
  container.appendChild(prevBtn);

  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement("button");
    btn.textContent = i;
    btn.disabled = i === currentPage;
    btn.setAttribute("aria-label", `Page ${i}`);
    btn.addEventListener("click", () => {
      currentPage = i;
      renderActivityLogs();
      focusPaginationButton(currentPage);
    });
    container.appendChild(btn);
  }

  const nextBtn = document.createElement("button");
  nextBtn.textContent = "Next";
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.setAttribute("aria-label", "Next page");
  nextBtn.addEventListener("click", () => {
    if (currentPage < totalPages) {
      currentPage++;
      renderActivityLogs();
      focusPaginationButton(currentPage);
    }
  });
  container.appendChild(nextBtn);
}

function focusPaginationButton(pageNum) {
  const container = document.getElementById("paginationControls");
  const buttons = container.querySelectorAll("button:not([disabled])");
  buttons.forEach((btn) => {
    if (parseInt(btn.textContent, 10) === pageNum) {
      btn.focus();
    }
  });
}

document.getElementById("searchBar").addEventListener("input", () => {
  currentPage = 1;
  renderActivityLogs();
});
