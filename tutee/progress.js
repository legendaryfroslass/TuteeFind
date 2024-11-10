document.addEventListener("DOMContentLoaded", function() {
    // Select all the star containers
    const starContainers = document.querySelectorAll('.stars-container');

    starContainers.forEach(container => {
        // Get all stars within the container
        const stars = container.querySelectorAll('.stars');
        const ratingValueElement = container.querySelector('p');

        // Attach event listeners to each star
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                
                // Set the rating value
                ratingValueElement.innerText = `Rating: ${value}`;
                
                // Highlight stars up to the selected one
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.style.color = '#FFD700'; // Gold color
                    } else {
                        s.style.color = '#000'; // Default black color
                    }
                });
            });
        });
    });

    // Handle submit comment button click
    const submitButtons = document.querySelectorAll('.submit-comment-btn');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tutorId = this.id.split('-')[1];
            const commentText = document.getElementById(`commentText-${tutorId}`).value;
            const ratingValue = document.getElementById(`ratingValue-${tutorId}`).innerText.split(': ')[1];

            if (commentText.trim() === "" || !ratingValue) {
                alert("Please provide a rating and a comment.");
                return;
            }

            // Example of capturing the data - in practice, you might send this to a server
            console.log(`Tutor ID: ${tutorId}`);
            console.log(`Rating: ${ratingValue}`);
            console.log(`Comment: ${commentText}`);

            // Reset the form after submission
            document.getElementById(`commentText-${tutorId}`).value = '';
            document.getElementById(`ratingValue-${tutorId}`).innerText = '';
            container.querySelectorAll('.stars').forEach(star => star.style.color = '#000'); // Reset star colors
        });
    });
});



// Change button appearance when clicked
document.querySelectorAll('.likert-btn').forEach(button => {
    button.addEventListener('click', function() {
        // Remove 'selected' class from all buttons in the same row
        let siblings = this.parentNode.parentNode.querySelectorAll('.likert-btn');
        siblings.forEach(sibling => sibling.classList.remove('selected'));

        // Add 'selected' class to the clicked button
        this.classList.add('selected');
    });
});

// Pagination
document.addEventListener('DOMContentLoaded', function() {
    const totalPages = 9; // Total number of pages
    const progressBar = document.querySelector('.progress-bar');
    const pages = document.querySelectorAll('.page');
    let currentPage = 1;

    // Manage Likert scale selections
    const selectedPages = {};

    // Update the progress bar
    function updateProgressBar() {
        const percentage = ((currentPage - 1) / (totalPages - 1)) * 100;
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
    }

    // Show the current page
    function showPage(pageNumber) {
        pages.forEach((page, index) => {
            page.style.display = index === pageNumber - 1 ? 'block' : 'none';
        });
        updateProgressBar();
        updateButtons();
    }

    // Update the state of the navigation buttons
    function updateButtons() {
        // Disable the "Prev" button if on the first page
        document.querySelectorAll('[id^="prevBtn-"]').forEach(button => {
            button.disabled = currentPage === 1;
        });

        // Check if the "Next" button should be enabled based on selection
        document.querySelectorAll('[id^="nextBtn-"]').forEach(button => {
            const nextButton = document.getElementById(`nextBtn-${currentPage}`);
            if (selectedPages[currentPage]) {
                nextButton.disabled = false;
                nextButton.classList.remove('disabled');
            } else {
                nextButton.disabled = true;
                nextButton.classList.add('disabled');
            }
        });
    }

    // Handle Likert button clicks
    document.querySelectorAll('.likert-btn').forEach(button => {
        button.addEventListener('click', function() {
            const pageNumber = this.closest('.page').id.split('-')[1];
            selectedPages[pageNumber] = true;

            // Highlight selected button
            this.classList.add('selected');
            // Deselect other buttons
            this.parentElement.parentElement.querySelectorAll('.likert-btn').forEach(btn => {
                if (btn !== this) {
                    btn.classList.remove('selected');
                }
            });

            // Update the state of the "Next" button
            updateButtons();
        });
    });

    // Event listeners for navigation buttons
    document.querySelectorAll('[id^="nextBtn-"]').forEach(button => {
        button.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
    });

    document.querySelectorAll('[id^="prevBtn-"]').forEach(button => {
        button.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
    });

    // Ensure all "Next" buttons are initially disabled
    document.querySelectorAll('[id^="nextBtn-"]').forEach(button => {
        button.disabled = true;
        button.classList.add('disabled');
    });

    // Initialize the view
    showPage(currentPage);
});


document.addEventListener('DOMContentLoaded', function () {
    const likertButtons = document.querySelectorAll('.likert-btn');
    const nextButtons = document.querySelectorAll('[id^="nextBtn-"]');
    const selectedPages = {}; // Object to track pages where a selection was made

    // Handle Likert button click
    likertButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Get the current page number
            const currentPage = this.closest('.page').id.split('-')[1];
            
            // Update selection state for the current page
            selectedPages[currentPage] = true;

            // Highlight selected button
            this.classList.add('selected');
            // Deselect other buttons
            this.parentElement.parentElement.querySelectorAll('.likert-btn').forEach(btn => {
                if (btn !== this) {
                    btn.classList.remove('selected');
                }
            });

            // Enable the next button for the current page
            document.getElementById(`nextBtn-${currentPage}`).disabled = false;
        });
    });

    // Ensure "Next" button state is correct when navigating back
    function updateButtonStates() {
        document.querySelectorAll('.page').forEach(page => {
            const pageId = page.id.split('-')[1];
            const nextButton = document.getElementById(`nextBtn-${pageId}`);
            
            // Check if there was a selection made on this page
            if (selectedPages[pageId]) {
                nextButton.disabled = false;
            } else {
                nextButton.disabled = true;
            }
        });
    }

    // Update button states when the document is loaded
    updateButtonStates();
});



// "Rate Tutor" button script
// document.getElementById('rateTutorBtn-1').addEventListener('click', function() {
//     this.disabled = true;
// });


// Submit message popup
document.addEventListener('DOMContentLoaded', function() {
    var submitButton = document.getElementById('submitCommentButton-1');
    var modal = document.getElementById('rateTutorModal-1');
    var buttonContainer = document.getElementById('buttonContainer'); // Make sure this ID is correct

    // Hide the button when the submit button is clicked
    submitButton.addEventListener('click', function() {
        // Hide the modal
        var bootstrapModal = bootstrap.Modal.getInstance(modal);
        bootstrapModal.hide();

        // Hide the button container and save state in localStorage
        if (buttonContainer) {
            buttonContainer.style.display = 'none';  // Hide the button container
            localStorage.setItem('buttonContainerHidden', 'true');  // Save the hidden state
        }

        // Display thank you message
        alert('Thank you!');
    });

    // Check localStorage on page load to hide the button container
    if (localStorage.getItem('buttonContainerHidden') === 'true') {
        if (buttonContainer) {
            buttonContainer.style.display = 'none';  // Keep the button hidden after page reload
        }
    }
});

localStorage.setItem('testKey', 'testValue');
console.log(localStorage.getItem('testKey'));  // It should log "testValue"

// Progress Bar
document.addEventListener('DOMContentLoaded', function() {
    const totalPages = 8; // Total number of pages
    const progressBar = document.querySelector('.progress-bar');
    const pages = document.querySelectorAll('.page');
    let currentPage = 1;

    function updateProgressBar() {
        const percentage = ((currentPage - 1) / (totalPages - 1)) * 100;
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
    }

    function showPage(pageNumber) {
        pages.forEach((page, index) => {
            page.style.display = index === pageNumber - 1 ? 'block' : 'none';
        });
        updateProgressBar();
        updateButtons();
    }

    function updateButtons() {
        document.querySelectorAll('[id^="prevBtn-"]').forEach(button => {
            button.disabled = currentPage === 1;
        });
        document.querySelectorAll('[id^="nextBtn-"]').forEach(button => {
            button.disabled = currentPage === totalPages;
        });
    }

    document.querySelectorAll('[id^="nextBtn-"]').forEach(button => {
        button.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
    });

    document.querySelectorAll('[id^="prevBtn-"]').forEach(button => {
        button.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
    });

    // Initialize the view
    showPage(currentPage);
});