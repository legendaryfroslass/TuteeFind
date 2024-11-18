// JavaScript to show the toast
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('msg-sendBtn').addEventListener('click', function () {
        var toastEl = document.getElementById('toastMsgSent');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
});

function showProfileModal(event, row) {
    // Ensure the row is only activated when clicked directly
    if (event.target.closest("button")) return;
    
    // Extract data from the row
    const name = row.dataset.name;
    const photo = row.dataset.photo;
    const brgy = row.dataset.brgy;
    const bio = row.dataset.bio;

    // Populate the profile modal with the data
    document.getElementById("profileModalName").innerText = name;
    document.getElementById("profileModalPhoto").src = photo;
    document.getElementById("profileModalBrgy").innerText = brgy;
    document.getElementById("profileModalBio").innerText = bio;

    // Show the profile modal
    const profileModal = new bootstrap.Modal(
    document.getElementById("profileModal")
    );
    profileModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    var removeButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#removeTuteeModal"]');
    removeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var tutorId = this.getAttribute('data-tutor-id'); // Get the tutor ID from button's data attribute
            var tuteeId = this.getAttribute('data-tutee-id'); // Get the tutee ID from button's data attribute

            // Set the IDs in the modal's hidden inputs
            document.getElementById('modalTutorId').value = tutorId;
            document.getElementById('tutee_id').value = tuteeId;
        });
    });
});

// function setRequestId(action, button) {
//     var requestId = button.getAttribute('data-request-id');
//     if (action === 'accept') {
//         document.getElementById('acceptRequestId').value = requestId;
//     } else if (action === 'reject') {
//         document.getElementById('rejectTutorId').value = requestId; // Set tutor_id for rejection
//         document.getElementById('tuteeId').value = button.getAttribute('data-tutee-id'); // Set tutee_id if needed
//     }
//     console.log(action.charAt(0).toUpperCase() + action.slice(1) + " Request ID:", requestId);
// }

// This code listens for when the reject modal is shown
$('#rejectTutorModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var requestId = button.data('request-id'); // Extract request_id from data attributes
    var tuteeId = button.data('tutee-id'); // Extract tutee_id from data attributes

    // Set the hidden input values
    $('#rejectTutorId').val(requestId);
    $('#tuteeId').val(tuteeId);
});
