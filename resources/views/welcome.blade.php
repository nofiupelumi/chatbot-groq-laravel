
{{-- <!DOCTYPE html>
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
</html> --}}



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NRI Bot</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Background */
        body {
            background: url('/images/nri-background.png') no-repeat center center fixed;
            background-size: 200px;
            font-family: Arial, sans-serif;
        }

        /* Chat button */
        .chat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #2d6a4f;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Chat container */
        .chat-container {
            display: none;
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        /* Header */
        .chat-header {
            background: #2d6a4f;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: bold;
        }

        .chat-header img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .header-icons {
            display: flex;
            gap: 10px;
        }

        /* Chat messages */
        .chat-messages {
            height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: url('/images/nri-background.png') no-repeat center center;
            background-size: 100px;
        }

        /* Message bubbles */
        .user-message {
            background: #40916c;
            color: white;
            padding: 8px;
            border-radius: 10px;
            margin-bottom: 8px;
            max-width: 75%;
            align-self: flex-end;
            text-align: right;
        }

        .bot-message-container {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
        }

        .bot-message-container img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        .bot-message {
            background: #d8f3dc;
            color: black;
            padding: 8px;
            border-radius: 10px;
            max-width: 75%;
        }

        /* Input area */
        .chat-input {
            display: flex;
            border-top: 1px solid #ddd;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
        }

        .chat-input button {
            background: #2d6a4f;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- Floating Chat Button -->
    <div class="chat-button" onclick="toggleChat()">🗨️</div>

    <!-- Chat Container -->
    <div class="chat-container" id="chatContainer">
        <div class="chat-header">
            <div style="display: flex; align-items: center;">
                <img src="/images/nri-background.png" alt="NRI Bot">
                <span>NRI Bot</span>
            </div>
            <div class="header-icons">
                <button onclick="clearChat()"
                    style="background: none; border: none; color: white; cursor: pointer;">⭮</button>
                <button onclick="toggleChat()"
                    style="background: none; border: none; color: white; cursor: pointer;">✖</button>
            </div>
        </div>
        <div id="chatbox" class="chat-messages flex flex-col">
            <div class="bot-message-container">
                <img src="/images/nri-background.png" alt="Bot">
                <div class="bot-message">Hey I'm your Digital NRI Bot. How can I help you?</div>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="message" placeholder="Type your message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        function toggleChat() {
            let chatContainer = document.getElementById("chatContainer");
            chatContainer.style.display = (chatContainer.style.display === "block") ? "none" : "block";
        }

        function clearChat() {
            document.getElementById("chatbox").innerHTML = `
                <div class="bot-message-container">
                    <img src="/images/nri-background.png" alt="Bot">
                    <div class="bot-message">Hey, I'm your Digital NRI Bot. How can I help you?</div>
                </div>
            `;
        }

        function sendMessage() {
            let message = document.getElementById("message").value.trim();
            if (message === "") return;

            let chatbox = document.getElementById("chatbox");

            // Add user message
            let userMsg = document.createElement("div");
            userMsg.classList.add("user-message");
            userMsg.textContent = message;
            chatbox.appendChild(userMsg);

            document.getElementById("message").value = "";

            // Show "typing..." message
            let typingMsg = document.createElement("div");
            typingMsg.classList.add("bot-message-container");
            typingMsg.id = "typing";
            typingMsg.innerHTML = `
                <img src="/images/nri-background.png" alt="Bot">
                <div class="bot-message">Bot is typing...</div>
            `;
            chatbox.appendChild(typingMsg);
            chatbox.scrollTop = chatbox.scrollHeight;

            // Simulate API call
            axios.post('/chat', {
                    message: message
                })
                .then(response => {
                    document.getElementById("typing").remove(); // Remove "typing..." message
                    let botMsgContainer = document.createElement("div");
                    botMsgContainer.classList.add("bot-message-container");
                    botMsgContainer.innerHTML = `
                        <img src="/images/nri-background.png" alt="Bot">
                        <div class="bot-message">${response.data.response}</div>
                    `;
                    chatbox.appendChild(botMsgContainer);
                    chatbox.scrollTop = chatbox.scrollHeight;
                })
                .catch(error => {
                    document.getElementById("typing").remove();
                    let botMsgContainer = document.createElement("div");
                    botMsgContainer.classList.add("bot-message-container");
                    botMsgContainer.innerHTML = `
                        <img src="/images/nri-background.png" alt="Bot">
                        <div class="bot-message" style="color: red;">Error: Failed to fetch response.</div>
                    `;
                    chatbox.appendChild(botMsgContainer);
                });
        }
    </script>

</body>

</html>