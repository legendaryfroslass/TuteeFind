// Get checkbox and button elements
const checkbox = document.getElementById("enableButton");
const button = document.getElementById("acceptBtn");

// Add event listener to checkbox
checkbox.addEventListener("change", function () {
  // If checkbox is checked, enable the button; otherwise, disable it
    button.disabled = !this.checked;
});

document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.querySelector('.password');
        const confirmPasswordInput = document.querySelector('.confirm-password');
        const passwordFeedback = document.getElementById('passwordFeedback');
        // const confirmPasswordFeedback = confirmPasswordInput.nextElementSibling;

        function validatePassword() {
            const passwordValue = passwordInput.value;
            const passwordValid = passwordValue.length >= 8 && /\d/.test(passwordValue);
            
            if (passwordValid) {
                passwordInput.classList.remove('is-invalid');
                passwordFeedback.style.display = 'none';
            } else {
                passwordInput.classList.add('is-invalid');
                passwordFeedback.style.display = 'block';
            }
            return passwordValid;
        }

// Function to validate password criteria
function validatePassword() {
    const password = passwordInput.value;
    const signUpButton = document.getElementById('signUp');
    
    // Password must be at least 8 characters long and contain at least one number
    const passwordCriteriaMet = password.length >= 8 && /\d/.test(password);
    
    if (passwordCriteriaMet) {
        passwordInput.classList.remove('is-invalid');
        passwordFeedback.style.display = 'none';
    } else {
        passwordInput.classList.add('is-invalid');
        passwordFeedback.style.display = 'block';
    }

    // Call to check confirm password after password validation
    validateConfirmPassword();
}

// Function to validate password match
function validateConfirmPassword() {
    const signUpButton = document.getElementById('signUp');
    
    // Check if passwords match
    if (confirmPasswordInput.value === passwordInput.value) {
        confirmPasswordInput.classList.remove('is-invalid');
        confirmPasswordFeedback.style.display = 'none';
    } else {
        confirmPasswordInput.classList.add('is-invalid');
        confirmPasswordFeedback.style.display = 'block';
    }

    // Disable or enable the register button based on both validations
    const passwordCriteriaMet = passwordInput.value.length >= 8 && /\d/.test(passwordInput.value);
    const passwordsMatch = confirmPasswordInput.value === passwordInput.value;

    if (passwordCriteriaMet && passwordsMatch) {
        signUpButton.disabled = false;  // Enable the Register button if both conditions are met
    } else {
        signUpButton.disabled = true;  // Disable the Register button if any condition is not met
    }
}

// Event listeners to trigger validation on input change
passwordInput.addEventListener('input', validatePassword);
confirmPasswordInput.addEventListener('input', validateConfirmPassword);


        passwordInput.addEventListener('input', function () {
            validatePassword();
            validateConfirmPassword();
        });

        confirmPasswordInput.addEventListener('input', function () {
            validateConfirmPassword();
        });
    });

    $(document).ready(function(){
        // Navigation between forms
        $('#nextButton-1').click(function(){
            $('.form1').addClass('d-none');
            $('.form2').removeClass('d-none');
        });

        $('#backButton-2').click(function(){
            $('.form2').addClass('d-none');
            $('.form1').removeClass('d-none');
        });

        $('#nextButton-2').click(function(){
            $('.form2').addClass('d-none');
            $('.form3').removeClass('d-none');
        });

        $('#backButton-3').click(function(){
            $('.form3').addClass('d-none');
            $('.form2').removeClass('d-none');
        });

        $('#backButton-4').click(function(){
            $('.form4').addClass('d-none');
            $('.form3').removeClass('d-none');
        });

        // $('#nextButton-4').click(function(){
        //     $('.form4').addClass('d-none');
        //     $('.form5').removeClass('d-none');
        // });

        $('#backButton-5').click(function(){
            $('.form5').addClass('d-none');
            $('.form4').removeClass('d-none');
        });
    });

// Function to check if any input field or dropdown is empty in a given form and apply red border
function checkInputs(form) {
    var inputs = form.find('input, select'); // Include both input fields and dropdowns
    var isEmpty = false;

    inputs.each(function() {
        var inputType = $(this).prop('tagName').toLowerCase();

        if ($(this).val() === '' || $(this).val() === null) {
            isEmpty = true;
            $(this).addClass('is-invalid'); // Add the red border to empty fields or dropdowns
        } else {
            $(this).removeClass('is-invalid'); // Remove the red border if filled
        }

        // Remove red border when user starts typing or selecting options
        if (inputType === 'select') {
            // For select dropdowns
            $(this).on('change', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        } else {
            // For input fields
            $(this).on('input', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('is-invalid');
                }
            });
        }
    });
    return isEmpty;
}

    // Ensure document is ready before executing
    $(document).ready(function(){
        // Navigation between forms
    
        // Move from Form 1 to Form 2
        $('#nextButton-1').click(function(e){
            e.preventDefault(); // Prevent default button behavior
            var currentForm = $('.form1'); // Get current form
    
            if (!checkInputs(currentForm)) {
                currentForm.addClass('d-none');
                $('.form2').removeClass('d-none');
            } else {
                $('#modalMessage').text('Please fill in all the fields in the first form before proceeding.');
                $('#resultModal').modal('show');
                currentForm.removeClass('d-none');
                $('.form2').addClass('d-none');
            }
        });
    });
    

document.addEventListener('DOMContentLoaded', function () {
    const ageSelect = document.querySelector('select[name="age"]');
    const birthdayInput = document.getElementById('tuteeBirthday');
    const birthdayFeedback = document.getElementById('birthday-feedback');
    // Attach the validation function to the change events
    ageSelect.addEventListener('change', validateAgeAndBirthday);
    birthdayInput.addEventListener('change', validateAgeAndBirthday);
});


//Eye Toggle for password
const togglePassword = document.querySelector('#togglePassword');
const passwordInput = document.querySelector('#password');
const toggleIcon = document.querySelector('#toggleIcon');

togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    toggleIcon.classList.toggle('bi-eye');
    toggleIcon.classList.toggle('bi-eye-slash');
});

//Eye Toggle for password confirmation
const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
const confirmPasswordInput = document.querySelector('#confirm-password');
const toggleConfirmIcon = document.querySelector('#toggleConfirmIcon');

toggleConfirmPassword.addEventListener('click', function () {
    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordInput.setAttribute('type', type);
    toggleConfirmIcon.classList.toggle('bi-eye');
    toggleConfirmIcon.classList.toggle('bi-eye-slash');
});

document.addEventListener('DOMContentLoaded', function() {
    var modalMessage = document.getElementById('modalMessage');
    var resultModal = document.getElementById('resultModal');
    
    if (modalMessage && resultModal) {
        if (modalMessage.innerText !== '') {
            var myModal = new bootstrap.Modal(resultModal);
            myModal.show();
        }
    }
});

function checkInput(event) {
    const inputValue = event.target.value;
    if (isNaN(inputValue) || inputValue > 11) {
        event.target.value = inputValue.slice(0, -1); // Remove the last character
    }
}

// Calculate Birthday
document.addEventListener('DOMContentLoaded', function () {
    const ageInput = document.getElementById('tuteeCalculatedAge');
    const birthdayInput = document.getElementById('tuteeBirthday');
    const birthdayFeedback = document.getElementById('birthday-feedback');

    // Add event listener for form submission or field change
    function validateAgeAndBirthday() {
        const birthdayValue = new Date(birthdayInput.value);
        const today = new Date();
        let calculatedAge = today.getFullYear() - birthdayValue.getFullYear();
        const monthDifference = today.getMonth() - birthdayValue.getMonth();
        
        // Adjust age if necessary
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthdayValue.getDate())) {
            calculatedAge--;
        }

        // Validate age based on birthday
        if (parseInt(ageInput.value) !== calculatedAge) {
            birthdayInput.classList.add('is-invalid');
            birthdayFeedback.style.display = 'block';
        } else {
            birthdayInput.classList.remove('is-invalid');
            birthdayFeedback.style.display = 'none';
        }
    }

    // Validate when form ibirthday-feedback submitted or birthday is changed
    birthdayInput.addEventListener('change', validateAgeAndBirthday);
});

document.addEventListener('DOMContentLoaded', function () {
    const ageSelect = document.querySelector('select[name="age"]');
    const birthdayInput = document.getElementById('tuteeBirthday');
    const birthdayFeedback = document.getElementById('birthday-feedback');

    function validateAgeAndBirthday() {
        const age = parseInt(ageSelect.value);
        const birthdayValue = new Date(birthdayInput.value);
        const today = new Date();

        // Calculate age based on birthday
        let calculatedAge = today.getFullYear() - birthdayValue.getFullYear();
        const monthDifference = today.getMonth() - birthdayValue.getMonth();
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthdayValue.getDate())) {
            calculatedAge--;
        }

        // Check if age matches the selected age
        if (age !== calculatedAge) {
            birthdayInput.classList.add('is-invalid');
            birthdayFeedback.style.display = 'block';
        } else {
            birthdayInput.classList.remove('is-invalid');
            birthdayFeedback.style.display = 'none';
        }
    }

    // Attach the validation function to the change events
    ageSelect.addEventListener('change', validateAgeAndBirthday);
    birthdayInput.addEventListener('change', validateAgeAndBirthday);
});

// Select all elements with the class 'numOnly'
document.querySelectorAll('.numOnly').forEach(function (element) {
    element.addEventListener('input', function () {
        // Only allow numeric input by filtering out non-numeric characters
        this.value = this.value.replace(/\D/g, '');

        // Enforce maxlength manually
        if (this.value.length > this.maxLength) {
            this.value = this.value.slice(0, this.maxLength);
        }
    });

    // Prevent non-numeric keys (like letters) from being typed
    element.addEventListener('keydown', function (e) {
        // Allow: Backspace, Delete, Tab, Escape, Enter, Arrow keys, etc.
        if (
            e.key === "Backspace" ||
            e.key === "Delete" ||
            e.key === "Tab" ||
            e.key === "Escape" ||
            e.key === "Enter" ||
            e.key === "ArrowLeft" ||
            e.key === "ArrowRight"
        ) {
            return; // Allow these keys
        }

        // If the key is not a number (0-9), prevent the default behavior
        if ((e.key < '0' || e.key > '9')) {
            e.preventDefault();
        }
    });
});

// When user press "enter", simulates as though clicking the button for efficiency
function setupEnterKeyListener(inputElementId, buttonElementId) {
    document.getElementById(inputElementId).addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault(); // Prevent form from submitting
            document.getElementById(buttonElementId).click(); // Simulate a click on the button
        }
    });
}

setupEnterKeyListener("contactNo", "nextButton-1");
setupEnterKeyListener("barangay", "nextButton-2");
setupEnterKeyListener("nextButton-3");
setupEnterKeyListener("nextButton-4");
setupEnterKeyListener("confirm-password", "signUp");

