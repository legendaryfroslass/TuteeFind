const body = document.querySelector("body"),
  sidebar = document.querySelector(".sidebar"),
  toggle = document.querySelector(".toggle"),
  searchBtn = document.querySelector(".search-box"),
  modeSwitch = document.querySelector(".toggle-switch"),
  modeText = document.querySelector(".mode-text");

// Function to toggle dark mode
function toggleDarkMode() {
  body.classList.toggle("dark");

  if (body.classList.contains("dark")) {
    modeText.innerText = "Light Mode";
  } else {
    modeText.innerText = "Dark Mode";
  }

  // Store dark mode preference in localStorage
  localStorage.setItem("darkMode", body.classList.contains("dark"));
}

// Event listener for toggle button
toggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

// Event listener for mode switch
modeSwitch.addEventListener("click", toggleDarkMode);

// Check localStorage for dark mode preference on page load
window.addEventListener("load", () => {
  const darkMode = localStorage.getItem("darkMode");

  // Set dark mode according to the stored preference
  if (darkMode === "true") {
    body.classList.add("dark");
    modeText.innerText = "Light Mode";
  }
});

document.addEventListener("DOMContentLoaded", function (event) {
  const showNavbar = (toggleId, navId, bodyId, headerId) => {
    const toggle = document.getElementById(toggleId),
      nav = document.getElementById(navId),
      bodypd = document.getElementById(bodyId),
      headerpd = document.getElementById(headerId);

    // Validate that all variables exist
    if (toggle && nav && bodypd && headerpd) {
      toggle.addEventListener("click", () => {
        // show navbar
        nav.classList.toggle("show");
        // change icon
        toggle.classList.toggle("bx-x");
        // add padding to body
        bodypd.classList.toggle("body-pd");
        // add padding to header
        headerpd.classList.toggle("body-pd");
      });
    }
  };

  showNavbar("header-toggle", "nav-bar", "body-pd", "header");

  /*===== LINK ACTIVE =====*/
  const linkColor = document.querySelectorAll(".nav_link");

  function colorLink() {
    if (linkColor) {
      linkColor.forEach((l) => l.classList.remove("active"));
      this.classList.add("active");
    }
  }
  linkColor.forEach((l) => l.addEventListener("click", colorLink));

  // Your code to run since DOM is loaded and ready
});

/* SCRIPT NG FINISH BUTTON
document.getElementById('finish-btn').addEventListener('click', function() {
    // Check if all checkboxes are checked
    var allChecked = true;
    var checkboxes = document.querySelectorAll('.checkbox');
    checkboxes.forEach(function(checkbox) {
        if (!checkbox.checked) {
            allChecked = false;
        }
    });

    if (allChecked) {
        if (confirm('Are you sure you want to finish this tutor session?')) {
            // Proceed with the finish operation
            alert('Tutor session finished.');
            // Add any additional logic needed to complete the finish operation
            
            // Disable all buttons
            var buttons = document.querySelectorAll('button');
            buttons.forEach(function(button) {
                button.disabled = true;
            });
        } else {
            // If the user clicks "Cancel", do nothing
            console.log('Tutor session not finished.');
        }
    } else {
        alert('Please make sure all tasks are completed (all checkboxes are checked) before finishing the session.');
    }
});

// Function to disable buttons if checkbox is checked
function disableButtonsOnCheck() {
    var checkboxes = document.querySelectorAll('.checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var buttons = checkbox.closest('li').querySelectorAll('button');
            if (checkbox.checked) {
                buttons.forEach(function(button) {
                    button.disabled = true;
                });
            }
        });
    });
}

// Call the function to add event listeners to checkboxes
disableButtonsOnCheck();

// refresh kapag nag dwel
*/

// NOTIFICATION PAGE

let currentTuteeId = null;
let messageInterval = null;

function showMessages(tuteeId, autoScroll = true) {
  if (currentTuteeId !== tuteeId) {
    currentTuteeId = tuteeId;

    // Stop previous polling if any
    if (messageInterval) {
      clearInterval(messageInterval);
    }
    selectChat(tuteeId);
    // Fetch messages immediately when the conversation is opened
    fetchNewMessages(tuteeId);

    // Start polling every 1 second for new messages after opening the conversation
    messageInterval = setInterval(function () {
      fetchNewMessages(tuteeId);
    }, 5000); // 1000 milliseconds = 1 second
  }
}

function fetchNewMessages(tuteeId) {
  if (currentTuteeId === tuteeId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // Only update the message content section (not the entire form)
        document.getElementById("messageContent").innerHTML = xhr.responseText;

        // Optionally auto-scroll to the bottom of the messages
        if (autoScroll) {
          var messageContent = document.getElementById("messageContent");
          messageContent.scrollTop = messageContent.scrollHeight;
        }
      }
    };
    xhr.send("fetch_messages=1&tutee_id=" + tuteeId);
  }
}

function sendMessage(event, tuteeId) {
  event.preventDefault();

  var messageInput = document.getElementById("messageInput");
  var message = messageInput.value;

  if (message.trim()) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // After sending the message, we fetch the new messages to reflect the changes
        fetchNewMessages(tuteeId);
        messageInput.value = ""; // Clear the message input
      }
    };

    xhr.send(
      "send_message=1&tutee_id=" +
        tuteeId +
        "&message=" +
        encodeURIComponent(message)
    );
  }
}
function closeMessages() {
  if (messageInterval) {
    clearInterval(messageInterval);
  }
  // You can also clear the message content or perform other cleanup actions
  document.getElementById("messageContent").innerHTML = "";
}
// Function to handle showing the message form
function selectChat(tuteeId) {
  if (tuteeId) {
    // Show the message form when a chat is selected
    document.getElementById("sendMessageForm").style.display = "block";
    currentTuteeId = tuteeId; // Set the current tutee ID to send messages
  } else {
    // Hide the message form when no chat is selected
    document.getElementById("sendMessageForm").style.display = "none";
    currentTuteeId = null; // Reset the current tutee ID
  }
}

// TUTOR PAGE

document.addEventListener("DOMContentLoaded", function () {
  var messageModal = document.getElementById("messageModal");

  if (messageModal) {
    messageModal.addEventListener("show.bs.modal", function (event) {
      var button = event.relatedTarget;
      var tuteeName = button.getAttribute("data-tutee-name");
      var tuteeId = button.getAttribute("data-tutee-id");

      var recipientField = messageModal.querySelector("#recipient");
      var hiddenInput = messageModal.querySelector("#tutee_id");

      if (recipientField && hiddenInput) {
        recipientField.textContent = tuteeName; // Set recipient name
        hiddenInput.value = tuteeId; // Set hidden input value
      } else {
        console.error("Recipient field or hidden input not found.");
      }
    });
  }
  // else {
  //   console.error("Message modal not found.");
  // }
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
