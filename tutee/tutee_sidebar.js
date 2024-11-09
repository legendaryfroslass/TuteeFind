const body = document.querySelector("body"),
  sidebar = document.querySelector(".sidebar"),
  toggle = document.querySelector(".toggle"),
  searchBtn = document.querySelector(".search-box"),
  modeSwitch = document.querySelector(".toggle-switch"),
  modeText = document.querySelector(".mode-text");

// Function to toggle dark mode
function toggleDarkMode() {
  body.classList.toggle("dark");

  if (body.classList.contains("dark")) {
    modeText.innerText = "Light Mode";
  } else {
    modeText.innerText = "Dark Mode";
  }

  // Store dark mode preference in localStorage
  localStorage.setItem("darkMode", body.classList.contains("dark"));
}

// Event listener for toggle button
toggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

// Event listener for search button
// searchBtn.addEventListener("click", () => {
//   sidebar.classList.remove("close");
// });

// Event listener for mode switch
modeSwitch.addEventListener("click", toggleDarkMode);

// Check localStorage for dark mode preference on page load
window.addEventListener("load", () => {
  const darkMode = localStorage.getItem("darkMode");

  // Set dark mode according to the stored preference
  if (darkMode === "true") {
    body.classList.add("dark");
    modeText.innerText = "Light Mode";
  }
});

document.addEventListener("DOMContentLoaded", function (event) {
  const showNavbar = (toggleId, navId, bodyId, headerId) => {
    const toggle = document.getElementById(toggleId),
      nav = document.getElementById(navId),
      bodypd = document.getElementById(bodyId),
      headerpd = document.getElementById(headerId);

    // Validate that all variables exist
    if (toggle && nav && bodypd && headerpd) {
      toggle.addEventListener("click", () => {
        // show navbar
        nav.classList.toggle("show");
        // change icon
        toggle.classList.toggle("bx-x");
        // add padding to body
        bodypd.classList.toggle("body-pd");
        // add padding to header
        headerpd.classList.toggle("body-pd");
      });
    }
  };

  showNavbar("header-toggle", "nav-bar", "body-pd", "header");

  /*===== LINK ACTIVE =====*/
  const linkColor = document.querySelectorAll(".nav_link");

  function colorLink() {
    if (linkColor) {
      linkColor.forEach((l) => l.classList.remove("active"));
      this.classList.add("active");
    }
  }
  linkColor.forEach((l) => l.addEventListener("click", colorLink));

  // Your code to run since DOM is loaded and ready
});
