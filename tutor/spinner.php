<head>
    <link rel="icon" href="../assets/TuteeFindLogo.png" type="image/png">
</head>

<!-- Spinner -->
<div class="spinner-container" style="display: none;">
    <div class="spinner center">
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
        <div class="spinner-blade"></div>
    </div>
</div>

<script>
function showSpinner() {
    // Show the spinner
    document.querySelector('.spinner-container').style.display = 'flex';

    // Set a 5-second timer to hide the spinner and then reload the page
    setTimeout(function() {
        hideSpinner();  // Hide the spinner after 5 seconds
        location.reload();  // Reload the page after the spinner disappears
    }, 2000);  // 5000 milliseconds = 5 seconds
}

function hideSpinner() {
    // Hide the spinner
    document.querySelector('.spinner-container').style.display = 'none';
}
</script>


