const setVisible = (elementOrSelector, visible) => {
  const element = document.querySelector(elementOrSelector);
  if (visible) {
    element.classList.remove("hide");
  } else {
    element.classList.add("hide");
  }
};

function onLoadingPage() {
  setVisible(".page", false);
  setVisible("#loading", true);
}

function loadPage() {
  setVisible(".page", true);
  setVisible("#loading", false);
}

onLoadingPage();
document.addEventListener("DOMContentLoaded", () => {
  loadPage();
  document.querySelectorAll(".submitFormLoading").forEach((element) => {
    element.onsubmit = onLoadingPage;
  });
});
