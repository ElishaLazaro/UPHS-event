let questionsData = [];
let totalQuestions = 0;
let currentPage = 1;
const perPage = 10;

const tbody = document.getElementById("questionsTableBody");
const paginationDiv = document.getElementById("pagination");

function createBadge(type) {
  switch (type) {
    case "MCQ":
      return '<span class="badge badge-mcq">Multiple Choice</span>';
    case "Identification":
      return '<span class="badge badge-identification">Identification</span>';
    case "TrueFalse":
      return '<span class="badge badge-truefalse">True or False</span>';
    default:
      return "";
  }
}

function renderQuestions(data) {
  tbody.innerHTML = "";
  if (!data.length) {
    tbody.innerHTML =
      '<tr><td colspan="4" class="text-center text-muted py-4">No questions found.</td></tr>';
    return;
  }
  data.forEach((q, i) => {
    const tr = document.createElement("tr");

    const tdNum = document.createElement("td");
    tdNum.textContent = (currentPage - 1) * perPage + i + 1;
    tr.appendChild(tdNum);

    const tdQuestion = document.createElement("td");
    tdQuestion.innerHTML = `<span class="question-text" title="${q.question.replace(
      /"/g,
      "&quot;"
    )}">${q.question}</span>`;
    tr.appendChild(tdQuestion);

    const tdType = document.createElement("td");
    tdType.innerHTML = createBadge(q.type);
    tr.appendChild(tdType);

    const tdActions = document.createElement("td");
    tdActions.className = "table-actions";

    const btnEdit = document.createElement("button");
    btnEdit.className = "icon-btn";
    btnEdit.setAttribute(
      "aria-label",
      `Edit question ${(currentPage - 1) * perPage + i + 1}`
    );
    btnEdit.innerHTML = '<i class="bi bi-pencil"></i>';
    btnEdit.type = "button";
    btnEdit.addEventListener("click", () => openEditModal(q));
    tdActions.appendChild(btnEdit);
    const btnDelete = document.createElement("button");
    btnDelete.className = "icon-btn";
    btnDelete.setAttribute(
      "aria-label",
      `Delete question ${(currentPage - 1) * perPage + i + 1}`
    );
    btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
    btnDelete.type = "button";
    btnDelete.setAttribute("data-bs-toggle", "modal");
    btnDelete.setAttribute("data-bs-target", "#modalQuestionDelete");
    btnDelete.dataset.questionId = q.question_id;
    tdActions.appendChild(btnDelete);

    tr.appendChild(tdActions);

    tbody.appendChild(tr);
  });
}

function renderPagination(total, page, perPage) {
  const totalPages = Math.ceil(total / perPage);
  if (totalPages <= 1) {
    paginationDiv.innerHTML = "";
    return;
  }
  let html = '<nav aria-label="Pagination"><div class="pagination">';
  html += `<div class="page-item${
    page <= 1 ? " disabled" : ""
  }"><a class="page-link" href="#" data-page="${page - 1}">Previous</a></div>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<div class="page-item${
      i === page ? " active" : ""
    }"><a class="page-link" href="#" data-page="${i}">${i}</a></div>`;
  }
  html += `<div class="page-item${
    page >= totalPages ? " disabled" : ""
  }"><a class="page-link" href="#" data-page="${page + 1}">Next</a></div>`;
  html += "</div></nav>";
  paginationDiv.innerHTML = html;
  // Add event listeners
  paginationDiv.querySelectorAll("a.page-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const p = parseInt(this.getAttribute("data-page"));
      if (!isNaN(p) && p >= 1 && p <= totalPages && p !== currentPage) {
        loadQuestions(p);
      }
    });
  });
}

async function loadQuestions(page = 1) {
  try {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    const response = await fetch(
      `php/Get_Question_Paginated.php?id=${id}&page=${page}&per_page=${perPage}`
    );
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    const result = await response.json();
    questionsData = result.data;
    totalQuestions = result.total;
    currentPage = page;
    renderQuestions(questionsData);
    renderPagination(totalQuestions, currentPage, perPage);
  } catch (error) {
    console.error("Failed to load questions:", error);
    renderQuestions([]);
    renderPagination(0, 1, perPage);
  }
}

function filterQuestions() {
  const query = document.getElementById("searchBar").value.trim().toLowerCase();
  if (!query) {
    renderQuestions(questionsData);
    return;
  }
  const filtered = questionsData.filter((q) =>
    q.question.toLowerCase().includes(query)
  );
  renderQuestions(filtered);
}

function toggleQuestionInputs(formType) {
  let typeSelect, mcqDiv, idDiv, tfDiv;
  if (formType === "create") {
    typeSelect = document.getElementById("questionTypeCreate");
    mcqDiv = document.getElementById("mcqInputsCreate");
    idDiv = document.getElementById("identificationInputCreate");
    tfDiv = document.getElementById("trueFalseInputsCreate");
  } else {
    typeSelect = document.getElementById("questionTypeEdit");
    mcqDiv = document.getElementById("mcqInputsEdit");
    idDiv = document.getElementById("identificationInputEdit");
    tfDiv = document.getElementById("trueFalseInputsEdit");
  }
  const val = typeSelect.value;

  mcqDiv.classList.toggle("d-none", val !== "MCQ");
  idDiv.classList.toggle("d-none", val !== "Identification");
  tfDiv.classList.toggle("d-none", val !== "TrueFalse");

  const mcqInputs = mcqDiv.querySelectorAll("input[type='text']");
  const mcqRadios = mcqDiv.querySelectorAll(
    "input[type='radio'][name^='correctAnswer']"
  );
  mcqInputs.forEach((input) => {
    if (val === "MCQ") {
      input.setAttribute("required", "required");
    } else {
      input.removeAttribute("required");
    }
  });
  mcqRadios.forEach((radio) => {
    if (val === "MCQ") {
      radio.setAttribute("required", "required");
    } else {
      radio.removeAttribute("required");
      radio.checked = false;
    }
  });

  const idInput = idDiv.querySelector("input[type='text']");
  if (val === "Identification") {
    idInput.setAttribute("required", "required");
  } else {
    idInput.removeAttribute("required");
    idInput.value = "";
  }

  const tfRadios = tfDiv.querySelectorAll(
    "input[type='radio'][name^='trueFalse']"
  );
  tfRadios.forEach((radio) => {
    if (val === "TrueFalse") {
      radio.setAttribute("required", "required");
    } else {
      radio.removeAttribute("required");
      radio.checked = false;
    }
  });
}

function openEditModal(question) {
  const modal = document.getElementById("modalQuestionEdit");
  const txt = document.getElementById("editQuestionText");
  const typeSelect = document.getElementById("questionTypeEdit");
  txt.value = question.question;
  typeSelect.value = question.type;
  document.getElementById("QuesID").value = question.question_id;
  ["choiceAEdit", "choiceBEdit", "choiceCEdit", "choiceDEdit"].forEach(
    (id) => (document.getElementById(id).value = "")
  );
  document.getElementById("identificationAnswerEdit").value = "";
  document.querySelectorAll('input[name="correctAnswerEdit"]').forEach((el) => {
    el.checked = false;
  });
  document.getElementById("trueOptionEdit").checked = false;
  document.getElementById("falseOptionEdit").checked = false;

  toggleQuestionInputs("edit");

  if (question.type === "MCQ" && question.choices) {
    document.getElementById("choiceAEdit").value = question.choices[0] || "";
    document.getElementById("choiceBEdit").value = question.choices[1] || "";
    document.getElementById("choiceCEdit").value = question.choices[2] || "";
    document.getElementById("choiceDEdit").value = question.choices[3] || "";
    if (question.correct) {
      const correctRadio = document.querySelector(
        `input[name="correctAnswerEdit"][value="${question.correct}"]`
      );
      if (correctRadio) correctRadio.checked = true;
    }
  } else if (question.type === "Identification" && question.answer) {
    document.getElementById("identificationAnswerEdit").value = question.answer;
  } else if (question.type === "TrueFalse" && question.answer) {
    if (question.answer.toLowerCase() === "true") {
      document.getElementById("trueOptionEdit").checked = true;
    } else if (question.answer.toLowerCase() === "false") {
      document.getElementById("falseOptionEdit").checked = true;
    }
  }

  const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
  bsModal.show();
}

document
  .getElementById("modalQuestionCreate")
  .addEventListener("show.bs.modal", () => {
    document.getElementById("formQuestionCreate").reset();
    toggleQuestionInputs("create");
  });
document
  .getElementById("modalQuestionDelete")
  .addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const questionId = button.getAttribute("data-question-id");
    const inputQuesId = this.querySelector("input#QuesIDDEL");
    if (inputQuesId) {
      inputQuesId.value = questionId;
    }
  });

loadQuestions();
