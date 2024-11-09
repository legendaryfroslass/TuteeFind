let currentTutorId = null;
let messageInterval = null;

function fetchNewMessages(tutorId) {
  if (currentTutorId === tutorId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // Preserve the current input value
        var messageInput = document.getElementById("messageInput");
        var currentMessage = messageInput ? messageInput.value : "";

        // Update the message content
        document.getElementById("messageContent").innerHTML = xhr.responseText;

        // Restore the input value
        if (messageInput) {
          messageInput.value = currentMessage;
        }
      }
    };
    xhr.send("fetch_messages=1&tutor_id=" + tutorId);
  }
}

function showMessages(tutorId, event = null, autoScroll = true) {
  if (currentTutorId !== tutorId) {
    currentTutorId = tutorId;

    // Stop previous polling if any
    if (messageInterval) {
      clearInterval(messageInterval);
    }
  }

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      document.getElementById("messageContent").innerHTML = xhr.responseText;

      // Auto-scroll to the bottom if required
      if (autoScroll) {
        var messageContent = document.getElementById("chatBody");
        messageContent.scrollTop = messageContent.scrollHeight;
      }
    }
  };
  xhr.send("fetch_messages=1&tutor_id=" + tutorId);
}

function sendMessage(event, tutorId) {
  event.preventDefault(); // Prevent the form from submitting the traditional way
  var messageInput = document.getElementById("messageInput");
  var message = messageInput.value;

  if (message.trim() === "") {
    return; // Do not send empty messages
  }

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      // Reload the messages for this tutor
      showMessages(tutorId);

      // Clear the input field after sending
      messageInput.value = "";

      // Focus back to the input field
      messageInput.focus(); // Ensure focus is on the input
    }
  };

  // Send the message to the server
  xhr.send(
    "send_message=1&tutor_id=" +
      tutorId +
      "&message=" +
      encodeURIComponent(message)
  );

  document.getElementById("sendButton").addEventListener("click", function () {
    const messageInput = document.getElementById("messageInput");
    const messageText = messageInput.value.trim();

    if (messageText) {
      const chatBody = document.getElementById("chatBody");

      // Create new message element
      const newMessage = document.createElement("div");
      newMessage.classList.add("message", "outgoing");

      const messageContent = document.createElement("p");
      messageContent.textContent = messageText;

      newMessage.appendChild(messageContent);
      chatBody.appendChild(newMessage);

      // Clear the input field
      messageInput.value = "";
    }
  });

  // // Optional: Send message on Enter key press
  // document.getElementById("messageInput").addEventListener("keypress", function(e) {
  //     if (e.key === "Enter") {
  //         e.preventDefault();
  //         document.getElementById("sendButton").click();
  //     }
  // });
}

document
  .getElementById("messageInput")
  .addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Prevents the default action of Enter key
      document.getElementById("sendButton").click(); // Triggers click on send button
    }
  });

// Get the message input field and the send button
const messageInput = document.getElementById("messageInput");
const sendButton = document.getElementById("sendButton");

// Add an event listener for the 'keydown' event on the message input
messageInput.addEventListener("keydown", function (event) {
  if (event.key === "Enter") {
    // Check if Enter key is pressed
    event.preventDefault(); // Prevent the default Enter key behavior (like form submission)
    sendButton.click(); // Trigger the send button click event
  }
});

// Long Polling
function longPollMessages(tutorId) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "message.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      const response = JSON.parse(xhr.responseText);

      if (response.messages && response.messages.length > 0) {
        const messageContent = document.getElementById("messageContent");

        // Append new messages to the chat
        response.messages.forEach((msg) => {
          const newMessage = document.createElement("div");
          newMessage.classList.add("message");
          newMessage.textContent = msg.content;
          messageContent.appendChild(newMessage);
        });

        // Auto-scroll to the bottom
        messageContent.scrollTop = messageContent.scrollHeight;
      }

      // Start the next poll immediately
      longPollMessages(tutorId);
    }
  };

  // Send request with tutorId and optionally the last message ID
  xhr.send("fetch_messages=1&tutor_id=" + tutorId);
}

// Start long polling when you open a chat
longPollMessages(currentTutorId);
