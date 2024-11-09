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