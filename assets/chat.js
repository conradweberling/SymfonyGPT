window.addEventListener('DOMContentLoaded', () => {
    const chatWindow = document.getElementById('chat-window');
    const chatInput = document.getElementById('chat-input');
    const sendButton = document.getElementById('send-button');

    let chatName = null;
    let chatWelcomePrompt = null;

    // Array to store the conversation history
    let conversation = [];

    function fetchParameters() {
        fetch('/chat/api/parameters', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                chatName = data.chatName;
                chatWelcomePrompt = data.chatWelcomePrompt;

                conversation = [{role: 'user', content: chatWelcomePrompt}];

                sendRequest(); // Welcome Message
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    function sendRequest() {
        const chatbotMessageElement = document.createElement('p');
        chatbotMessageElement.classList.add('loading-message')
        chatbotMessageElement.textContent = `${chatName}: ...`;
        chatWindow.appendChild(chatbotMessageElement);
        chatWindow.scrollTop = chatWindow.scrollHeight;

        fetch('/chat/api/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                conversation: conversation
            }),
        })
            .then(response => response.json())
            .then(data => {
                chatWindow.lastChild.parentNode.removeChild(chatWindow.lastChild);

                // Append the chatbot's message to the chat window
                const chatbotMessageElement = document.createElement('p');
                chatbotMessageElement.textContent = `${chatName}: ${data.content}`;
                chatWindow.appendChild(chatbotMessageElement);
                chatWindow.scrollTop = chatWindow.scrollHeight;

                // Update the conversation history
                conversation.push({
                    role: 'assistant',
                    content: data.content
                });
            })
            .catch((error) => {
                chatWindow.lastChild.parentNode.removeChild(chatWindow.lastChild);

                console.error('Error:', error);
            });
    }

    function sendMessage() {
        const message = chatInput.value;
        chatInput.value = '';

        // Append the user's message to the chat window
        const userMessageElement = document.createElement('p');
        userMessageElement.textContent = `You: ${message}`;
        chatWindow.appendChild(userMessageElement);
        chatWindow.scrollTop = chatWindow.scrollHeight;

        // Update the conversation history
        conversation.push({
            role: 'user',
            content: message
        });

        sendRequest();
    }

    chatInput.addEventListener('keyup', (event) => {
        if (event.key === 'Enter') {
            sendMessage();
        }
    });

    sendButton.addEventListener('click', () => {
        sendMessage();
    });

    fetchParameters();
});

