// Dark mode toggle
document.getElementById("toggle-dark").addEventListener("click", () => {
  document.body.classList.toggle("dark-mode");
  localStorage.setItem("darkMode", document.body.classList.contains("dark-mode"));
});

// Restore preference
if (localStorage.getItem("darkMode") === "true") {
  document.body.classList.add("dark-mode");
}
