<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel AI Chatbot</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-4">
        <h2 class="text-lg font-semibold text-center">Laravel Chatbot (Groq API)</h2>

        <!-- Chat Display Area -->
        <div id="chatbox" class="h-80 overflow-y-auto p-2 border border-gray-300 rounded mt-2 bg-gray-50">
            <div class="text-center text-gray-500">Start chatting...</div>
        </div>

        <!-- Input Box -->
        <div class="flex mt-2">
            <input type="text" id="message" class="flex-1 p-2 border rounded-l outline-none" placeholder="Type a message...">
            <button onclick="sendMessage()" class="bg-blue-500 text-white px-4 py-2 rounded-r">Send</button>
        </div>
    </div>

    <script>
        function sendMessage() {
            let message = document.getElementById("message").value;
            if (message.trim() === "") return;

            let chatbox = document.getElementById("chatbox");

            // Add user message to chatbox
            chatbox.innerHTML += `<div class="text-right text-blue-600 my-1"><strong>You:</strong> ${message}</div>`;
            document.getElementById("message").value = "";

            // Show "typing..." message
            chatbox.innerHTML += `<div id="typing" class="text-gray-500 my-1">Bot is typing...</div>`;
            chatbox.scrollTop = chatbox.scrollHeight;

            // Send request to backend
            axios.post('/chat', { message: message })
                .then(response => {
                    document.getElementById("typing").remove(); // Remove "typing..." text
                    chatbox.innerHTML += `<div class="text-left text-gray-700 my-1"><strong>Bot:</strong> ${response.data.response}</div>`;
                    chatbox.scrollTop = chatbox.scrollHeight;
                })
                .catch(error => {
                    document.getElementById("typing").remove();
                    chatbox.innerHTML += `<div class="text-left text-red-600 my-1"><strong>Error:</strong> Failed to fetch response</div>`;
                    console.error(error);
                });
        }
    </script>
</body>
</html>
