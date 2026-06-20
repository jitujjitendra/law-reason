const body = document.body;
const menuToggle = document.querySelector(".menu-toggle");
const primaryNav = document.querySelector(".primary-nav");
const queryModal = document.querySelector("#query-modal");
const queryForm = document.querySelector("#query-form");
const closeModalButton = document.querySelector(".modal-close");
const toast = document.querySelector(".toast");
let toastTimer;

function setMenu(open) {
  primaryNav.classList.toggle("open", open);
  menuToggle.setAttribute("aria-expanded", String(open));
  body.classList.toggle("menu-open", open);
  menuToggle.querySelector("use").setAttribute("href", open ? "#icon-close" : "#icon-menu");
}

function showToast(message) {
  window.clearTimeout(toastTimer);
  toast.textContent = message;
  toast.classList.add("visible");
  toastTimer = window.setTimeout(() => toast.classList.remove("visible"), 3200);
}

menuToggle.addEventListener("click", () => {
  setMenu(!primaryNav.classList.contains("open"));
});

primaryNav.addEventListener("click", (event) => {
  if (event.target.closest("a")) {
    setMenu(false);
  }
});

document.querySelectorAll(".js-open-query").forEach((button) => {
  button.addEventListener("click", () => {
    setMenu(false);
    queryModal.showModal();
    body.classList.add("modal-open");
  });
});

function closeQueryModal() {
  queryModal.close();
  body.classList.remove("modal-open");
}

closeModalButton.addEventListener("click", closeQueryModal);

queryModal.addEventListener("click", (event) => {
  if (event.target === queryModal) {
    closeQueryModal();
  }
});

queryModal.addEventListener("close", () => {
  body.classList.remove("modal-open");
});

queryForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const status = queryForm.querySelector(".modal-status");
  status.textContent = "Thank you. Your query has been recorded for review.";
  queryForm.reset();
  window.setTimeout(closeQueryModal, 1600);
});

document.querySelectorAll(".js-newsletter").forEach((form) => {
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    showToast("You are subscribed to Law & Reason Weekly.");
    form.reset();
  });
});

window.addEventListener("resize", () => {
  if (window.innerWidth > 930) {
    setMenu(false);
  }
});
