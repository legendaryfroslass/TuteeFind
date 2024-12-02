<!-- Spinner -->
<div class="spinner-container">
    <div class="spinner center">
        <!-- Your spinner design -->
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
        const thankYouModal = new bootstrap.Modal(document.getElementById('thankYouModal1'));
                // Show the thank you modal after the rate tutor modal has hidden
                thankYouModal.show();
          // Reload the page after the spinner disappears
    }, 6000);  // 5000 milliseconds = 5 seconds
}

function hideSpinner() {
    // Hide the spinner
    document.querySelector('.spinner-container').style.display = 'none';
}
</script>