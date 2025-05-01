
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



{{-- <!DOCTYPE html>
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

</html> --}}

{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NRI Bot</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        /* Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            max-height: 600px;
            background-color: #fff;
        }

        .chat-widget.collapsed {
            height: 60px;
            width: 200px;
        }

        /* Welcome Screen */
        .welcome-screen {
            background-color: #0e3262; /* Nigeria Risk Index dark blue */
            color: white;
            padding: 25px;
            height: 150px;
            text-align: left;
            display: flex;
            flex-direction: column;
        }

        .welcome-text {
            margin-bottom: 15px;
        }

        .welcome-text h2 {
            font-size: 22px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .welcome-text h2 .wave-icon {
            margin-left: 10px;
            font-size: 22px;
        }

        .welcome-text p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Ask Question Button */
        .ask-button {
            background-color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            color: #333;
            width: 100%;
            text-align: left;
            transition: all 0.2s ease;
            margin-top: auto;
        }

        .ask-button:hover {
            background-color: #f0f0f0;
        }

        .ask-button i {
            margin-right: 8px;
            color: #1abc9c; /* Nigeria Risk Index teal */
        }

        .question-mark {
            margin-left: auto;
            width: 25px;
            height: 25px;
            background-color: #1abc9c; /* Nigeria Risk Index teal */
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Header Styles */
        .chat-header {
            background-color: #0e3262; /* Nigeria Risk Index dark blue */
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-header.collapsed {
            cursor: pointer;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .header-title {
            display: flex;
            flex-direction: column;
        }

        .header-title h3 {
            font-size: 16px;
            font-weight: bold;
        }

        .header-title p {
            font-size: 12px;
            opacity: 0.8;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .header-button:hover {
            opacity: 1;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            height: 350px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        .chat-messages .no-messages {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #666;
            text-align: center;
            padding: 20px;
        }

        .no-messages i {
            font-size: 40px;
            margin-bottom: 15px;
            color: #ccc;
        }

        .no-messages p {
            font-size: 14px;
            max-width: 80%;
        }

        /* Message bubbles */
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            max-width: 85%;
        }

        .message.bot {
            align-self: flex-start;
        }

        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 8px;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .message-bubble {
            padding: 12px 15px;
            border-radius: 18px;
            max-width: 100%;
            word-break: break-word;
        }

        .message.bot .message-bubble {
            background-color: #eee;
            color: #333;
            border-top-left-radius: 5px;
        }

        .message.user .message-bubble {
            background-color: #1abc9c; /* Nigeria Risk Index teal */
            color: white;
            border-top-right-radius: 5px;
        }

        .message.typing .message-bubble {
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #888;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        /* Chat Input Area */
        .chat-input {
            border-top: 1px solid #eee;
            padding: 10px;
            display: flex;
            align-items: center;
            background-color: white;
        }

        .chat-input-wrapper {
            flex-grow: 1;
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
        }

        .chat-input input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            outline: none;
            font-size: 14px;
            background: none;
        }

        .chat-input-wrapper::placeholder {
            color: #999;
        }

        .chat-input-actions {
            display: flex;
            align-items: center;
            padding-right: 15px;
        }

        .input-action-button {
            background: none;
            border: none;
            color: #1abc9c; /* Nigeria Risk Index teal */
            font-size: 18px;
            cursor: pointer;
            margin-left: 5px;
            transition: all 0.2s;
        }

        .input-action-button:hover {
            transform: scale(1.1);
        }

        /* Bottom navigation */
        .chat-nav {
            display: flex;
            border-top: 1px solid #eee;
            background-color: white;
        }

        .nav-item {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.2s;
        }

        .nav-item i {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .nav-item.active {
            color: #1abc9c; /* Nigeria Risk Index teal */
        }

        .nav-item:hover {
            background-color: #f9f9f9;
        }

        /* Button to expand/collapse chat */
        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #1abc9c; /* Nigeria Risk Index teal */
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
        }

        .chat-toggle:hover {
            transform: scale(1.05);
            background-color: #16a085; /* Darker teal */
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .chat-widget {
                width: 100%;
                height: 100%;
                right: 0;
                bottom: 0;
                border-radius: 0;
                max-height: 100%;
            }
            
            .chat-toggle {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Chat Toggle Button (Initially Visible) -->
    <div class="chat-toggle" id="chatToggle" onclick="toggleChatWidget()">
        <i class="fas fa-comments"></i>
    </div>

    <!-- Chat Widget (Initially Hidden) -->
    <div class="chat-widget" id="chatWidget" style="display: none;">
        <!-- Welcome Screen (Default View) -->
        <div class="welcome-screen" id="welcomeScreen">
            <div class="welcome-text">
                <h2>Hi there <span class="wave-icon">👋</span></h2>
                <p>How can we help?</p>
            </div>
            <button class="ask-button" onclick="showChatInterface()">
                <i class="fas fa-comment"></i>
                Ask a question
                <div class="question-mark">?</div>
            </button>
        </div>

        <!-- Chat Interface (Hidden Initially) -->
        <div id="chatInterface" style="display: none; flex-direction: column; height: 100%;">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="header-left">
                    <div class="header-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="header-title">
                        <h3>NRI Bot™</h3>
                        <p>The team can also help</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-button" onclick="expandCollapseChat()">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>
                    <button class="header-button" onclick="showWelcomeScreen()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-messages" id="chatMessages">
                <!-- Initial empty state -->
                <div class="no-messages" id="noMessages">
                    <i class="fas fa-comment-slash"></i>
                    <p>No messages</p>
                    <p>Messages from the team will be shown here</p>
                </div>

                <!-- Bot welcome message (will be shown when chat starts) -->
                <div class="message bot" id="welcomeMessage" style="display: none;">
                    <div class="message-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="message-bubble">
                        <p>Hi, I'm NRI Bot. I'm here to help with anything you need - whether that's discovering our research or answering questions about security risks in Nigeria.</p>
                        <p style="margin-top: 8px; font-style: italic; font-size: 0.9em;">Please note: NRI Bot is in beta testing and responses may not always be accurate. For official information, please refer to our published research and websites.</p>
                        <p style="margin-top: 8px;">What would you like to know?</p>
                    </div>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="chat-input">
                <div class="chat-input-wrapper">
                    <input type="text" id="messageInput" placeholder="Ask a question..." onkeypress="if(event.key === 'Enter') sendMessage()">
                    <div class="chat-input-actions">
                        <button class="input-action-button" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation -->
            <div class="chat-nav">
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initial state
        let isChatOpen = false;
        let isFullscreen = false;
        let isChatInterfaceVisible = false;

        // Toggle Chat Widget Visibility
        function toggleChatWidget() {
            const widget = document.getElementById('chatWidget');
            const toggleBtn = document.getElementById('chatToggle');
            
            isChatOpen = !isChatOpen;
            
            if (isChatOpen) {
                widget.style.display = 'flex';
                toggleBtn.style.display = 'none';
                
                // If chat was previously in chat interface, show it again
                if (isChatInterfaceVisible) {
                    showChatInterface();
                } else {
                    showWelcomeScreen();
                }
            } else {
                widget.style.display = 'none';
                toggleBtn.style.display = 'flex';
            }
        }

        // Show Welcome Screen
        function showWelcomeScreen() {
            document.getElementById('welcomeScreen').style.display = 'flex';
            document.getElementById('chatInterface').style.display = 'none';
            isChatInterfaceVisible = false;
        }

        // Show Chat Interface
        function showChatInterface() {
            document.getElementById('welcomeScreen').style.display = 'none';
            document.getElementById('chatInterface').style.display = 'flex';
            document.getElementById('noMessages').style.display = 'none';
            document.getElementById('welcomeMessage').style.display = 'flex';
            isChatInterfaceVisible = true;
            
            // Focus on message input
            setTimeout(() => {
                document.getElementById('messageInput').focus();
            }, 100);
        }

        // Toggle between expanded/collapsed view
        function expandCollapseChat() {
            const widget = document.getElementById('chatWidget');
            isFullscreen = !isFullscreen;
            
            if (isFullscreen) {
                widget.style.width = '100%';
                widget.style.height = '100%';
                widget.style.top = '0';
                widget.style.right = '0';
                widget.style.bottom = '0';
                widget.style.borderRadius = '0';
            } else {
                widget.style.width = '350px';
                widget.style.height = '';
                widget.style.top = '';
                widget.style.right = '20px';
                widget.style.bottom = '20px';
                widget.style.borderRadius = '12px';
            }
        }

        // Send Message Function
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (message === '') return;
            
            // Clear input field
            messageInput.value = '';
            
            // Add user message to chat
            addUserMessage(message);
            
            // Show typing indicator
            showTypingIndicator();
            
            // Make API call
            makeApiCall(message);
        }

        // Add User Message to Chat
        function addUserMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'user');
            
            messageElement.innerHTML = `
                <div class="message-bubble">${message}</div>
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Add Bot Message to Chat
        function addBotMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'bot');
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">${message}</div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create typing indicator
            const typingElement = document.createElement('div');
            typingElement.classList.add('message', 'bot', 'typing');
            typingElement.id = 'typingIndicator';
            
            typingElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // API Call to Backend
        function makeApiCall(message) {
            // Simulate API call delay
            setTimeout(() => {
                axios.post('/chat', {
                    message: message
                })
                .then(response => {
                    hideTypingIndicator();
                    addBotMessage(response.data.response);
                })
                .catch(error => {
                    hideTypingIndicator();
                    addBotMessage('Sorry, I encountered an error processing your request. Please try again later.');
                });
            }, 1500); // Simulated delay for typing effect
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Initially show the toggle button
            document.getElementById('chatToggle').style.display = 'flex';
        });
    </script>

</body>
</html> --}}

{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NRI Bot</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        /* Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 500px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        /* Welcome Screen (Home) */
        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to bottom, #2d6a4f 0%, #2d6a4f 30%, white 70%);
        }

        .welcome-text {
            padding: 25px;
            margin-bottom: 15px;
            color: white;
        }

        .welcome-text h2 {
            font-size: 22px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .welcome-text h2 .wave-icon {
            margin-left: 10px;
            font-size: 22px;
        }

        .welcome-text p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Messages Screen */
        .messages-screen {
            flex: 1;
            display: none;
            flex-direction: column;
            background: #fff;
            padding-top: 15px;
        }

        .messages-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .no-messages-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
            text-align: center;
            padding: 20px;
        }

        .no-messages-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #333;
        }

        .no-messages-text {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .no-messages-subtext {
            font-size: 14px;
            color: #777;
        }

        /* Ask Question Button */
        .ask-button-container {
            padding: 15px;
            margin-top: auto;
        }

        .ask-button {
            background-color: #3498db;
            border: none;
            border-radius: 20px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            width: 100%;
            transition: all 0.2s ease;
        }

        .ask-button:hover {
            background-color: #2980b9;
        }

        .ask-button i {
            margin-right: 8px;
        }

        /* Chat Interface */
        .chat-interface {
            display: none;
            flex-direction: column;
            height: 100%;
        }

        /* Header Styles */
        .chat-header {
            background-color: #2d6a4f; /* NRI green */
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .header-title {
            display: flex;
            flex-direction: column;
        }

        .header-title h3 {
            font-size: 16px;
            font-weight: bold;
        }

        .header-title p {
            font-size: 12px;
            opacity: 0.8;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .header-button:hover {
            opacity: 1;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        /* Message bubbles */
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            max-width: 85%;
        }

        .message.bot {
            align-self: flex-start;
        }

        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 8px;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .message-bubble {
            padding: 12px 15px;
            border-radius: 18px;
            max-width: 100%;
            word-break: break-word;
        }

        .message.bot .message-bubble {
            background-color: #eee;
            color: #333;
            border-top-left-radius: 5px;
        }

        .message.user .message-bubble {
            background-color: #2d6a4f; /* NRI green */
            color: white;
            border-top-right-radius: 5px;
        }

        .message.typing .message-bubble {
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #888;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        /* Chat Input Area */
        .chat-input {
            border-top: 1px solid #eee;
            padding: 10px;
            display: flex;
            align-items: center;
            background-color: white;
        }

        .chat-input-wrapper {
            flex-grow: 1;
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
        }

        .chat-input input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            outline: none;
            font-size: 14px;
            background: none;
        }

        .chat-input-actions {
            display: flex;
            align-items: center;
            padding-right: 15px;
        }

        .input-action-button {
            background: none;
            border: none;
            color: #2d6a4f; /* NRI green */
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .input-action-button:hover {
            transform: scale(1.1);
        }

        /* Bottom navigation */
        .chat-nav {
            display: flex;
            border-top: 1px solid #eee;
            background-color: white;
        }

        .nav-item {
            flex: 1;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.2s;
        }

        .nav-item i {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .nav-item.active {
            color: #2d6a4f; /* NRI green */
        }

        .nav-item:hover {
            background-color: #f9f9f9;
        }

        /* Button to expand/collapse chat */
        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #2d6a4f; /* NRI green */
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
        }

        .chat-toggle:hover {
            transform: scale(1.05);
            background-color: #225740; /* Darker green */
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .chat-widget {
                width: 100%;
                height: 100%;
                right: 0;
                bottom: 0;
                border-radius: 0;
            }
            
            .chat-toggle {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Chat Toggle Button -->
    <div class="chat-toggle" id="chatToggle" onclick="toggleChatWidget()">
        <i class="fas fa-comments"></i>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatWidget" style="display: none;">
        
        <!-- Home Screen (Welcome Screen) -->
        <div class="welcome-screen" id="homeScreen">
            <div class="welcome-text">
                <h2>Hi there <span class="wave-icon">👋</span></h2>
                <p>How can we help?</p>
            </div>
            <div class="ask-button-container">
                <button class="ask-button" onclick="showChatInterface()">
                    <i class="fas fa-question-circle"></i>
                    Ask a question
                </button>
            </div>
        </div>

        <!-- Messages Screen -->
        <div class="messages-screen" id="messagesScreen">
            <div class="messages-title">Messages</div>
            <div class="no-messages-container">
                <div class="no-messages-icon">
                    <i class="fas fa-comment-slash"></i>
                </div>
                <div class="no-messages-text">No messages</div>
                <div class="no-messages-subtext">Messages from the team will be shown here</div>
            </div>
            <div class="ask-button-container">
                <button class="ask-button" onclick="showChatInterface()">
                    <i class="fas fa-question-circle"></i>
                    Ask a question
                </button>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="chat-interface" id="chatInterface">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="header-left">
                    <div class="header-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="header-title">
                        <h3>NRI Bot™</h3>
                        <p>The team can also help</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-button" onclick="expandCollapseChat()">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>
                    <button class="header-button" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-messages" id="chatMessages">
                <!-- Bot welcome message -->
                <div class="message bot">
                    <div class="message-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="message-bubble">
                        <p>Hi, I'm NRI Bot. I'm here to help with anything you need - whether that's discovering our research or answering questions about security risks in Nigeria.</p>
                        <p style="margin-top: 8px; font-style: italic; font-size: 0.9em;">Please note: NRI Bot is in beta testing and responses may not always be accurate. For official information, please refer to our published research and websites.</p>
                        <p style="margin-top: 8px;">What would you like to know?</p>
                    </div>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="chat-input">
                <div class="chat-input-wrapper">
                    <input type="text" id="messageInput" placeholder="Ask a question..." onkeypress="if(event.key === 'Enter') sendMessage()">
                    <div class="chat-input-actions">
                        <button class="input-action-button" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation -->
            <div class="chat-nav">
                <div class="nav-item" id="homeNavItem" onclick="showHomeScreen()">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </div>
                <div class="nav-item" id="messagesNavItem" onclick="showMessagesScreen()">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize variables
        let isChatOpen = false;
        let isFullscreen = false;
        let currentScreen = 'home'; // can be 'home', 'messages', or 'chat'
        let lastScreen = 'home'; // track the last screen before chat
        
        // Toggle Chat Widget Visibility
        function toggleChatWidget() {
            const widget = document.getElementById('chatWidget');
            const toggleBtn = document.getElementById('chatToggle');
            
            isChatOpen = !isChatOpen;
            
            if (isChatOpen) {
                widget.style.display = 'flex';
                toggleBtn.innerHTML = '<i class="fas fa-times"></i>'; // Change to X icon
            } else {
                widget.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-comments"></i>'; // Change back to comments icon
            }
        }

        // Show Home Screen
        function showHomeScreen() {
            document.getElementById('homeScreen').style.display = 'flex';
            document.getElementById('messagesScreen').style.display = 'none';
            document.getElementById('chatInterface').style.display = 'none';
            
            document.getElementById('homeNavItem').classList.add('active');
            document.getElementById('messagesNavItem').classList.remove('active');
            
            currentScreen = 'home';
            updateNavigation();
        }

        // Show Messages Screen
        function showMessagesScreen() {
            document.getElementById('homeScreen').style.display = 'none';
            document.getElementById('messagesScreen').style.display = 'flex';
            document.getElementById('chatInterface').style.display = 'none';
            
            document.getElementById('homeNavItem').classList.remove('active');
            document.getElementById('messagesNavItem').classList.add('active');
            
            currentScreen = 'messages';
            updateNavigation();
        }

        // Show Chat Interface
        function showChatInterface() {
            document.getElementById('homeScreen').style.display = 'none';
            document.getElementById('messagesScreen').style.display = 'none';
            document.getElementById('chatInterface').style.display = 'flex';
            
            document.getElementById('homeNavItem').classList.remove('active');
            document.getElementById('messagesNavItem').classList.remove('active');
            
            lastScreen = currentScreen;
            currentScreen = 'chat';
            
            // Focus on message input
            setTimeout(() => {
                document.getElementById('messageInput').focus();
            }, 100);
        }

        // Go back to previous screen
        function goBack() {
            if (lastScreen === 'home') {
                showHomeScreen();
            } else {
                showMessagesScreen();
            }
        }

        // Update bottom navigation based on current screen
        function updateNavigation() {
            if (currentScreen === 'home') {
                document.getElementById('homeNavItem').classList.add('active');
                document.getElementById('messagesNavItem').classList.remove('active');
            } else if (currentScreen === 'messages') {
                document.getElementById('homeNavItem').classList.remove('active');
                document.getElementById('messagesNavItem').classList.add('active');
            }
        }

        // Toggle between expanded/collapsed view
        function expandCollapseChat() {
            const widget = document.getElementById('chatWidget');
            isFullscreen = !isFullscreen;
            
            if (isFullscreen) {
                widget.style.width = '100%';
                widget.style.height = '100%';
                widget.style.top = '0';
                widget.style.right = '0';
                widget.style.bottom = '0';
                widget.style.borderRadius = '0';
            } else {
                widget.style.width = '350px';
                widget.style.height = '500px';
                widget.style.top = '';
                widget.style.right = '20px';
                widget.style.bottom = '20px';
                widget.style.borderRadius = '12px';
            }
        }

        // Send Message Function
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (message === '') return;
            
            // Clear input field
            messageInput.value = '';
            
            // Add user message to chat
            addUserMessage(message);
            
            // Show typing indicator
            showTypingIndicator();
            
            // Make API call
            makeApiCall(message);
        }

        // Add User Message to Chat
        function addUserMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'user');
            
            messageElement.innerHTML = `
                <div class="message-bubble">${message}</div>
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Add Bot Message to Chat
        function addBotMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'bot');
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">${message}</div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create typing indicator
            const typingElement = document.createElement('div');
            typingElement.classList.add('message', 'bot', 'typing');
            typingElement.id = 'typingIndicator';
            
            typingElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // API Call to Backend
        function makeApiCall(message) {
            // Simulate API call delay
            setTimeout(() => {
                axios.post('/chat', {
                    message: message
                })
                .then(response => {
                    hideTypingIndicator();
                    addBotMessage(response.data.response);
                })
                .catch(error => {
                    hideTypingIndicator();
                    addBotMessage('Sorry, I encountered an error processing your request. Please try again later.');
                });
            }, 1500); // Simulated delay for typing effect
        }

        // Initialize - show Home screen by default
        document.addEventListener('DOMContentLoaded', function() {
            showHomeScreen();
            // When the chat toggle is clicked, always show Home screen first
            document.getElementById('chatToggle').addEventListener('click', function() {
                if (!isChatOpen) {
                    showHomeScreen();
                }
            });
        });
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        /* Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 90px; /* Moved up to avoid overlapping with the chat icon */
            right: 20px;
            width: 400px;
            height: 593px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        /* Welcome Screen (Home) */
        .welcome-screen {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(to bottom, #2d6a4f 0%, #2d6a4f 40%, white 100%);
            position: relative;
        }

        .logo-container {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .logo-container img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .welcome-text {
            padding: 25px;
            margin-top: 60px;
            color: white;
        }

        .welcome-text h2 {
            font-size: 22px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .welcome-text h2 .wave-icon {
            margin-left: 10px;
            font-size: 22px;
        }

        .welcome-text p {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Ask Question Button - Repositioned */
        .ask-button-container {
            padding: 20px;
            margin-top: 30px;
        }

        .ask-button {
            background-color: white;
            border: none;
            border-radius: 20px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            color: #333;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }

        .ask-button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .ask-button i {
            margin-right: 8px;
            color: #2d6a4f;
        }

        .ask-button .question-mark {
            margin-left: auto;
            width: 24px;
            height: 24px;
            background-color: #2d6a4f;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Messages Screen */
        .messages-screen {
            flex: 1;
            display: none;
            flex-direction: column;
            background: #fff;
            padding-top: 15px;
        }

        .messages-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .no-messages-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
            text-align: center;
            padding: 20px;
            margin-bottom: 60px; /* Space for the button */
        }

        .no-messages-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #333;
        }

        .no-messages-text {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .no-messages-subtext {
            font-size: 14px;
            color: #777;
        }

        /* Message Screen Ask Button */
        .messages-ask-button-container {
            padding: 20px;
            margin-top: auto;
            margin-bottom: 70px; /* Space for the bottom nav */
        }

        .messages-ask-button {
            background-color: #2d6a4f; /* Green button */
            border: none;
            border-radius: 20px;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .messages-ask-button:hover {
            background-color: #225740; /* Darker green */
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .messages-ask-button i {
            margin-right: 8px;
        }

        /* Chat Interface */
        .chat-interface {
            display: none;
            flex-direction: column;
            height: 100%;
        }

        /* Header Styles */
        .chat-header {
            background-color: #2d6a4f; /* NRI green */
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
            overflow: hidden;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .header-title {
            display: flex;
            flex-direction: column;
        }

        .header-title h3 {
            font-size: 16px;
            font-weight: bold;
        }

        .header-title p {
            font-size: 12px;
            opacity: 0.8;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .header-button:hover {
            opacity: 1;
        }

        /* Chat Messages Area */
        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        /* Message bubbles */
        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            max-width: 85%;
        }

        .message.bot {
            align-self: flex-start;
        }

        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 8px;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            flex-shrink: 0;
        }

        .message-avatar img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .message-bubble {
            padding: 12px 15px;
            border-radius: 18px;
            max-width: 100%;
            word-break: break-word;
        }

        .message.bot .message-bubble {
            background-color: #eee;
            color: #333;
            border-top-left-radius: 5px;
        }

        .message.user .message-bubble {
            background-color: #2d6a4f; /* NRI green */
            color: white;
            border-top-right-radius: 5px;
        }

        .message.typing .message-bubble {
            display: flex;
            align-items: center;
            padding: 15px;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background-color: #888;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        /* Chat Input Area */
        .chat-input {
            border-top: 1px solid #eee;
            padding: 10px;
            display: flex;
            align-items: center;
            background-color: white;
            margin-bottom: 60px; /* Space for the bottom nav */
        }

        .chat-input-wrapper {
            flex-grow: 1;
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
        }

        .chat-input input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            outline: none;
            font-size: 14px;
            background: none;
        }

        .chat-input-actions {
            display: flex;
            align-items: center;
            padding-right: 15px;
        }

        .input-action-button {
            background: none;
            border: none;
            color: #2d6a4f; /* NRI green */
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .input-action-button:hover {
            transform: scale(1.1);
        }

        /* Bottom navigation - Consistent across all interfaces */
        .chat-nav {
            display: flex;
            border-top: 1px solid #eee;
            background-color: white;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            z-index: 10;
        }

        .nav-item {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #999;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-item i {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .nav-item.active {
            color: #2d6a4f; /* NRI green */
        }

        .nav-item:hover {
            background-color: #f9f9f9;
            color: #2d6a4f; /* NRI green on hover */
        }

        /* Button to expand/collapse chat */
        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #2d6a4f; /* NRI green */
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
        }

        .chat-toggle:hover {
            transform: scale(1.05);
            background-color: #225740; /* Darker green */
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .chat-widget {
                width: 100%;
                height: 100%;
                right: 0;
                bottom: 0;
                border-radius: 0;
            }
            
            .chat-toggle {
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Chat Toggle Button -->
    <div class="chat-toggle" id="chatToggle">
        <i class="fas fa-comments"></i>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatWidget" style="display: none;">
        
        <!-- Home Screen (Welcome Screen) -->
        <div class="welcome-screen" id="homeScreen">
            <div class="logo-container">
                <img src="/images/nri-background.png" alt="NRI Logo">
            </div>
            <div class="welcome-text">
                <h2>Hi there <span class="wave-icon">👋</span></h2>
                <p>How can we help?</p>
            </div>
            <div class="ask-button-container">
                <button class="ask-button" onclick="showChatInterface()">
                    <i class="fas fa-comment"></i>
                    Ask a question
                    <div class="question-mark">?</div>
                </button>
            </div>
            
            <!-- Bottom Navigation for Home Screen -->
            <div class="chat-nav">
                <div class="nav-item active" id="homeNavItem" onclick="showHomeScreen()">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </div>
                <div class="nav-item" id="messagesNavItem" onclick="showMessagesScreen()">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </div>
            </div>
        </div>

        <!-- Messages Screen -->
        <div class="messages-screen" id="messagesScreen">
            <div class="messages-title">Messages</div>
            <div class="no-messages-container">
                <div class="no-messages-icon">
                    <i class="fas fa-comment-slash"></i>
                </div>
                <div class="no-messages-text">No messages</div>
                <div class="no-messages-subtext">Messages from the team will be shown here</div>
            </div>
            <div class="messages-ask-button-container">
                <button class="messages-ask-button" onclick="showChatInterface()">
                    <i class="fas fa-question-circle"></i>
                    Ask a question
                </button>
            </div>
            
            <!-- Bottom Navigation for Messages Screen -->
            <div class="chat-nav">
                <div class="nav-item" id="homeNavItem2" onclick="showHomeScreen()">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </div>
                <div class="nav-item active" id="messagesNavItem2" onclick="showMessagesScreen()">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="chat-interface" id="chatInterface">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="header-left">
                    <div class="header-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="header-title">
                        <h3>NRI Bot™</h3>
                        <p>The team can also help</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="header-button" onclick="expandCollapseChat()">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>
                    <button class="header-button" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="chat-messages" id="chatMessages">
                <!-- Bot welcome message -->
                <div class="message bot">
                    <div class="message-avatar">
                        <img src="/images/nri-background.png" alt="NRI Bot">
                    </div>
                    <div class="message-bubble">
                        <p>Hi, I'm NRI Bot. I'm here to help with anything you need - whether that's discovering our research or answering questions about security risks in Nigeria.</p>
                        <p style="margin-top: 8px; font-style: italic; font-size: 0.9em;">Please note: NRI Bot is in beta testing and responses may not always be accurate. For official information, please refer to our published research and websites.</p>
                        <p style="margin-top: 8px;">What would you like to know?</p>
                    </div>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="chat-input">
                <div class="chat-input-wrapper">
                    <input type="text" id="messageInput" placeholder="Ask a question..." onkeypress="if(event.key === 'Enter') sendMessage()">
                    <div class="chat-input-actions">
                        <button class="input-action-button" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation for Chat Interface -->
            <div class="chat-nav">
                <div class="nav-item" id="homeNavItem3" onclick="showHomeScreen()">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </div>
                <div class="nav-item" id="messagesNavItem3" onclick="showMessagesScreen()">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize variables
        let isChatOpen = false;
        let isFullscreen = false;
        let currentScreen = 'home'; // can be 'home', 'messages', or 'chat'
        let lastScreen = 'home'; // track the last screen before chat
        
        // Toggle Chat Widget Visibility
        function toggleChatWidget() {
            const widget = document.getElementById('chatWidget');
            const toggleBtn = document.getElementById('chatToggle');
            
            isChatOpen = !isChatOpen;
            
            if (isChatOpen) {
                widget.style.display = 'flex';
            } else {
                widget.style.display = 'none';
            }
        }

        // Show Home Screen
        function showHomeScreen() {
            document.getElementById('homeScreen').style.display = 'flex';
            document.getElementById('messagesScreen').style.display = 'none';
            document.getElementById('chatInterface').style.display = 'none';
            
            // Update active states on ALL navigation bars
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.id.startsWith('homeNav')) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            
            currentScreen = 'home';
        }

        // Show Messages Screen
        function showMessagesScreen() {
            document.getElementById('homeScreen').style.display = 'none';
            document.getElementById('messagesScreen').style.display = 'flex';
            document.getElementById('chatInterface').style.display = 'none';
            
            // Update active states on ALL navigation bars
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.id.startsWith('messagesNav')) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            
            currentScreen = 'messages';
        }

        // Show Chat Interface
        function showChatInterface() {
            document.getElementById('homeScreen').style.display = 'none';
            document.getElementById('messagesScreen').style.display = 'none';
            document.getElementById('chatInterface').style.display = 'flex';
            
            // Remove active state from all nav items in chat interface
            document.querySelectorAll('#chatInterface .nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            lastScreen = currentScreen;
            currentScreen = 'chat';
            
            // Focus on message input
            setTimeout(() => {
                document.getElementById('messageInput').focus();
            }, 100);
        }

        // Go back to previous screen
        function goBack() {
            if (lastScreen === 'home') {
                showHomeScreen();
            } else {
                showMessagesScreen();
            }
        }

        // Toggle between expanded/collapsed view
        function expandCollapseChat() {
            const widget = document.getElementById('chatWidget');
            isFullscreen = !isFullscreen;
            
            if (isFullscreen) {
                widget.style.width = '100%';
                widget.style.height = '100%';
                widget.style.top = '0';
                widget.style.right = '0';
                widget.style.bottom = '0';
                widget.style.borderRadius = '0';
            } else {
                widget.style.width = '400px';
                widget.style.height = '593px';
                widget.style.top = '';
                widget.style.right = '20px';
                widget.style.bottom = '90px'; // Ensure it doesn't overlap with the chat icon
                widget.style.borderRadius = '12px';
            }
        }

        // Send Message Function
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (message === '') return;
            
            // Clear input field
            messageInput.value = '';
            
            // Add user message to chat
            addUserMessage(message);
            
            // Show typing indicator
            showTypingIndicator();
            
            // Make API call
            makeApiCall(message);
        }

        // Add User Message to Chat
        function addUserMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'user');
            
            messageElement.innerHTML = `
                <div class="message-bubble">${message}</div>
                <div class="message-avatar">
                    <i class="fas fa-user"></i>
                </div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Add Bot Message to Chat
        function addBotMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create message element
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', 'bot');
            
            messageElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">${message}</div>
            `;
            
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            
            // Create typing indicator
            const typingElement = document.createElement('div');
            typingElement.classList.add('message', 'bot', 'typing');
            typingElement.id = 'typingIndicator';
            
            typingElement.innerHTML = `
                <div class="message-avatar">
                    <img src="/images/nri-background.png" alt="NRI Bot">
                </div>
                <div class="message-bubble">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // API Call to Backend
        function makeApiCall(message) {
            // Simulate API call delay
            setTimeout(() => {
                axios.post('/chat', {
                    message: message
                })
                .then(response => {
                    hideTypingIndicator();
                    addBotMessage(response.data.response);
                })
                .catch(error => {
                    hideTypingIndicator();
                    addBotMessage('Sorry, I encountered an error processing your request. Please try again later.');
                });
            }, 1500); // Simulated delay for typing effect
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Set up chat toggle button
            document.getElementById('chatToggle').addEventListener('click', toggleChatWidget);
            
            // Show home screen by default
            showHomeScreen();
        });
    </script>

</body>
</html>
