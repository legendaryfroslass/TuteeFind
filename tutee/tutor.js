// Function to generate a random ID
function generateRandomId() {
    return Math.random().toString(36).substring(2, 15);
}

// Get all collapsible elements
var collapsibles = document.querySelectorAll('.collapse');

// Iterate through each collapsible element
collapsibles.forEach(function(collapsible) {
    // Generate a unique ID for the collapsible
    var collapseId = generateRandomId();
    collapsible.setAttribute('id', collapseId);

    // Get the button that toggles the collapsible
    var button = collapsible.previousElementSibling.querySelector('button');

    // Update the data-target attribute of the button to point to the generated ID
    button.setAttribute('data-bs-target', '#' + collapseId);
});

document.addEventListener('show.bs.modal', function (event) {
    var modal = event.target;
    if (modal.id === 'removeTuteeModal') {
        modal.classList.add('modal-shake-rotate');
        modal.addEventListener('animationend', function () {
            modal.classList.remove('modal-shake-rotate');
        }, { once: true });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Handle removal of tutor logic (Button click)
    const removeTutorButtons = document.querySelectorAll('#removeTutorBtn');
    removeTutorButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tutorId = this.getAttribute('data-tutor-id');
            const tuteeId = this.getAttribute('data-tutee-id');

            // Set tutor_id and tutee_id in the modal form
            document.getElementById('modalTutorId').value = tutorId;
            document.getElementById('tutee_id').value = tuteeId;
        });
    });

    // Handle form submission for removing a tutor
    document.getElementById('removeTuteeForm').addEventListener('submit', function (event) {
        const removalReason = document.getElementById('removal_reason').value.trim();

        // Check if the reason is provided
        if (!removalReason) {
            event.preventDefault(); // Prevent form submission
            document.getElementById('reasonError').style.display = 'block'; // Show error message
        } else {
            document.getElementById('reasonError').style.display = 'none'; // Hide error message
            
            // Send AJAX request to remove tutor
            const tutorId = document.getElementById('modalTutorId').value;
            const tuteeId = document.getElementById('tutee_id').value;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'remove_tutor',
                    tutor_id: tutorId,
                    tutee_id: tuteeId,
                    removal_reason: removalReason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh the page to reflect the changes
                } else {
                    alert("Error: " + data.message); // Show error message
                }
            })
            .catch(error => console.error("AJAX request failed:", error));
        }
    });
});

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

// JavaScript to show the toast
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('msg-sendBtn').addEventListener('click', function () {
        var toastEl = document.getElementById('toastMsgSent');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
});