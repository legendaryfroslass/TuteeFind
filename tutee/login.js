document.addEventListener("DOMContentLoaded", function () {
  // Get elements
  const birthdayInput = document.getElementById("userBirthday");
  const submitAgeButton = document.getElementById("submitAge");
  const ageModal = new bootstrap.Modal(document.getElementById("ageModal"), {});
  const resultModal = new bootstrap.Modal(
    document.getElementById("resultModal"),
    {}
  );
  const invalidFeedback = document.querySelector(".invalid-feedback");
  const modalMessage = document.getElementById("modalMessage"); // Get the message element
});

// Toggle Password
const togglePassword = document.querySelector("#togglePassword");
const passwordInput = document.querySelector("#passwordInput");
const toggleIcon = document.querySelector("#toggleIcon");

togglePassword.addEventListener("click", function () {
  const type =
    passwordInput.getAttribute("type") === "password" ? "text" : "password";
  passwordInput.setAttribute("type", type);
  toggleIcon.classList.toggle("bi-eye");
  toggleIcon.classList.toggle("bi-eye-slash");
});

// Calculate tutee birthday
document.getElementById("submitAge").addEventListener("click", function () {
  var birthdayInput = document.getElementById("userBirthday").value;

  if (!birthdayInput) {
    showErrorMessage("Please enter your child's birthday.");
    return;
  }

  var today = new Date();
  var birthday = new Date(birthdayInput);
  var age = today.getFullYear() - birthday.getFullYear();
  var monthDiff = today.getMonth() - birthday.getMonth();

  if (
    monthDiff < 0 ||
    (monthDiff === 0 && today.getDate() < birthday.getDate())
  ) {
    age--;
  }

  if (age < 6 || age > 11) {
    showErrorMessage(
      "Sorry! You must be between <b>6</b> and <b>11</b> years old to register."
    );
  } else {
    // Send birthday and age via AJAX to a PHP script to store in session
    fetch("set_session.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ birthday: birthdayInput, age: age }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          window.location.href = "../registration/register";
        } else {
          showErrorMessage("There was an issue setting the session data.");
        }
      });
  }
});

function showErrorMessage(message) {
  var feedbackElement = document.getElementById("bdayInvalid");
  feedbackElement.innerHTML = message;
  feedbackElement.style.display = "block";
  document.getElementById("userBirthday").classList.add("is-invalid");
}

// Event listener for keydown when user presses "Enter" button
document
  .getElementById("userBirthday")
  .addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Prevent form from submitting if Enter is pressed
      document.getElementById("submitAge").click(); // Simulate a click on the Proceed button
    }
  });

document.getElementById("userBirthday").addEventListener("input", function (e) {
  const value = this.value;
  const year = value.split("-")[0]; // Extract the year part

  // Prevent entering more than 4 digits for the year
  if (year.length > 4) {
    this.value = value.slice(0, 4) + value.slice(5); // Trim the extra digits
  }
});

window.onload = function () {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("passwordChanged") === "true") {
    const successModal = new bootstrap.Modal(
      document.getElementById("passwordChangedModal")
    );
    successModal.show();
  }
};
