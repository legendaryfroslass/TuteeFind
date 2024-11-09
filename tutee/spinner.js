function showSpinner() {
    document.querySelector('.spinner-container').style.display = 'flex';
}

function hideSpinner() {
    document.querySelector('.spinner-container').style.display = 'none';
}

// include('spinner.php');
// <link rel="stylesheet" href="spinner.css">
// <script src="spinner.js"></script>

// Showing spinner on form submission
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    showSpinner();
    document.getElementById('submitButton').disabled = false;
});