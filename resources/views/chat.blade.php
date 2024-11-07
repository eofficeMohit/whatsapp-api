<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Real-Time Chat</title>
    <!-- Include Pusher's JS SDK from CDN -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>

<body>
    <h1>Real-Time Chat</h1>

    <!-- Display messages -->
    <div id="messages" style="margin-bottom: 20px;"></div>

    <!-- Message input form -->
    <form id="message-form">
        <input type="text" id="message" placeholder="Type a message" required />
        <button type="submit">Send</button>
    </form>
    <script>
        // Pusher Setup - Initialize with your credentials from the .env file
        Pusher.logToConsole = true; // This is for debugging
        // Initialize Pusher with your app credentials
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}', // Replace with your cluster if different
            forceTLS: true,
        });

        // Replace with the chatroom ID you want to listen to
        const chatroomId = 1; // Example chatroom ID (you should dynamically set this)

        // Subscribe to the chatroom channel
        const channel = pusher.subscribe('chatroom.' + chatroomId);

        // Listen for new messages in the chatroom
        channel.bind('App\\Events\\MessageSent', function(data) {
            const messageContainer = document.getElementById('messages');
            const newMessage = document.createElement('div');
            newMessage.textContent = `${data.message.user_id}: ${data.message.content}`;
            messageContainer.appendChild(newMessage);
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Handle sending a message
        document.getElementById('message-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const message = document.getElementById('message').value;
            if (!message) return;

            // Send the message to the Laravel backend (API)
            fetch('/api/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken, // Include CSRF token here
                    },
                    body: JSON.stringify({
                        chatroom_id: chatroomId,
                        user_id: 1, // Replace with the actual user ID from your authentication system
                        message_text: message,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Message sent:', data);
                    document.getElementById('message').value = ''; // Clear the message input
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                });
        });
    </script>
</body>

</html>
