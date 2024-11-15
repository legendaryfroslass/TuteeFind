let currentTutorId = null;
let messageInterval = null;

function fetchNewMessages(tutorId) {
    if (currentTutorId === tutorId) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "message.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Only update the message content area
                document.getElementById("messageContent").innerHTML = xhr.responseText;

                // Scroll to the bottom automatically
                const chatBody = document.getElementById("chatBody");
                chatBody.scrollTop = chatBody.scrollHeight;

                // Start the next poll after 5 seconds
                setTimeout(() => fetchNewMessages(tutorId), 5000); // 5000 milliseconds = 5 seconds
            }
        };

        xhr.send("fetch_messages=1&tutor_id=" + tutorId);
    }
}

function showMessages(tutorId) {
    currentTutorId = tutorId;
    // Stop previous polling if any
    if (messageInterval) {
        clearInterval(messageInterval);
    }

    selectChat(tutorId);

    // Start fetching messages for the selected tutor
    fetchNewMessages(tutorId);

    // Start polling every 5 seconds for new messages after opening the conversation
    messageInterval = setInterval(function () {
        fetchNewMessages(tutorId); // fetch new messages for the current tutor
    }, 5000); // 5000 milliseconds = 5 seconds
}

function sendMessage(event, tutorId) {
    event.preventDefault();
    const messageInput = document.getElementById("messageInput");
    const message = messageInput.value.trim();

    if (message === "") return; // Do not send empty messages

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "message.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            showMessages(tutorId); // Refresh messages after sending
            messageInput.value = ""; // Clear input
            messageInput.focus();    // Refocus input
        }
    };

    xhr.send("send_message=1&tutor_id=" + tutorId + "&message=" + encodeURIComponent(message));
}

// Trigger send on Enter key
document.getElementById("messageInput").addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
        event.preventDefault();
        sendMessage(event, currentTutorId);
    }
});

// Function to handle showing the message form
function selectChat(tuteeId) {
    if (tuteeId) {
      // Show the message form when a chat is selected
    document.getElementById("sendMessageForm").style.display = "block";
      currentTutorId = tuteeId; // Set the current tutee ID to send messages
    } else {
      // Hide the message form when no chat is selected
    document.getElementById("sendMessageForm").style.display = "none";
      currentTutorId = null; // Reset the current tutee ID
    }
}