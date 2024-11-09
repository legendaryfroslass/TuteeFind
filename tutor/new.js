//modals
// Get checkbox and button elements
const checkbox = document.getElementById("enableButton");
const button = document.getElementById("myButton");

// Add event listener to checkbox
checkbox.addEventListener("change", function () {
  // If checkbox is checked, enable the button; otherwise, disable it
  button.disabled = !this.checked;
});
