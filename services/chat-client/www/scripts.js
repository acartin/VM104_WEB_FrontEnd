const chatBody = document.querySelector(".chat-body");
const messageInput = document.querySelector(".message-input");
const sendMessageButton = document.querySelector("#send-message");
const fileInput = document.querySelector("#file-input");
const fileUploadWrapper = document.querySelector("#file-upload-wrapper");
const fileCancelButton = document.querySelector("#file-cancel");
const chatbotToggler = document.querySelector("#chatbot-toggler");
const closeChatbot = document.querySelector("#close-chatbot");

// Altura inicial para el auto-resize
const initialInputHeight = messageInput.scrollHeight;

const userData = {
    message: null,
    file: {
        data: null,
        mime_type: null,
    },
};
// Integración con core-api.js y config.js
// Asegúrate de que core-api.js y config.js estén incluidos en index.html antes de este script
if (typeof initializeSession === 'function') {
    initializeSession();
}

// Create message element with dynamic classes and return it
const createMessageElement = (content, ...classes) => {
    const div = document.createElement("div");
    div.classList.add("message", ...classes);
    div.innerHTML = content;
    return div;
};

// Cargar historial al iniciar
const loadChatHistory = async () => {
    if (typeof fetchChatHistory !== 'function') return;

    const history = await fetchChatHistory();
    if (history.length > 0) {
        // Limpiar mensaje de bienvenida si hay historial
        chatBody.innerHTML = "";

        history.forEach(msg => {
            const isBot = msg.role === 'assistant';
            const messageContent = `<div class="message-text">${msg.content}</div>`;
            const messageDiv = createMessageElement(
                messageContent,
                isBot ? "bot-message" : "user-message"
            );
            chatBody.appendChild(messageDiv);
        });
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "instant" });
    }
};

// Integración con core-api.js y config.js
if (typeof initializeSession === 'function') {
    initializeSession();
    loadChatHistory();
}

// Handle outgoing user messages
const handleOutgoingMessage = async (e) => {
    e.preventDefault();
    userData.message = messageInput.value.trim();
    if (!userData.message) return;
    messageInput.value = "";
    messageInput.dispatchEvent(new Event("input"));

    // Mostrar mensaje del usuario
    const outgoingMessageDiv = createMessageElement(
        `<div class="message-text"></div>`,
        "user-message"
    );
    outgoingMessageDiv.querySelector(".message-text").textContent = userData.message;
    chatBody.appendChild(outgoingMessageDiv);
    chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });

    // Mostrar indicador de "pensando" del bot
    const botThinkingContent = `<svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024"><path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path></svg>
          <div class="message-text">
            <div class="thinking-indicator">
              <div class="dot"></div>
              <div class="dot"></div>
              <div class="dot"></div>
            </div>
          </div>`;
    const incomingMessageDiv = createMessageElement(botThinkingContent, "bot-message", "thinking");
    chatBody.appendChild(incomingMessageDiv);
    chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });

    // Llamar a core-api.js para obtener la respuesta del bot
    try {
        if (typeof sendMessageToAI === 'function') {
            const result = await sendMessageToAI(userData.message);
            const messageElement = incomingMessageDiv.querySelector(".message-text");

            // Renderizar respuesta + fuentes si existen
            let responseHTML = result.response;
            if (result.sources && result.sources.length > 0) {
                responseHTML += '<div class="sources-container"><hr><small>Fuentes:</small><ul>';
                result.sources.forEach(source => {
                    const sourceText = source.title || source.original_filename || 'Fuente desconocida';
                    if (source.url) {
                        responseHTML += `<li><a href="${source.url}" target="_blank">${sourceText}</a></li>`;
                    } else {
                        responseHTML += `<li>${sourceText}</li>`;
                    }
                });
                responseHTML += '</ul></div>';
            }
            messageElement.innerHTML = responseHTML;
        } else {
            throw new Error('No se encontró la función de conexión con IA');
        }
    } catch (error) {
        const messageElement = incomingMessageDiv.querySelector(".message-text");
        messageElement.innerText = error.message;
        messageElement.style.color = "#ff4444";
    } finally {
        incomingMessageDiv.classList.remove("thinking");
        chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
    }
};

// Handle Enter key press for sending messages
messageInput.addEventListener("keydown", (e) => {
    const userMessage = e.target.value.trim();
    if (e.key === "Enter" && userMessage && !e.shiftKey && window.innerWidth > 768) {
        handleOutgoingMessage(e);
    }
});

// Auto resize message input
messageInput.addEventListener("input", (e) => {
    messageInput.style.height = `${initialInputHeight}px`;
    messageInput.style.height = `${messageInput.scrollHeight}px`;
    document.querySelector(".chat-form").style.borderRadius = messageInput.scrollHeight > initialInputHeight ? "15px" : "32px";
});

// Handle file input change (se mantiene la estructura aunque el servidor actual no lo pida explícitamente en el manual)
fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        userData.file = {
            data: e.target.result.split(",")[1],
            mime_type: file.type,
        };
        fileInput.value = "";
    };
    reader.readAsDataURL(file);
});

// Initialize Emoji Picker
const picker = new EmojiMart.Picker({
    theme: "light",
    skinTonePosition: "none",
    preview: "none",
    onEmojiSelect: (emoji) => {
        const { selectionStart: start, selectionEnd: end } = messageInput;
        messageInput.setRangeText(emoji.native, start, end, "end");
        messageInput.focus();
    },
    onClickOutside: (e) => {
        if (e.target.id === "emoji-picker") {
            document.body.classList.toggle("show-emoji-picker");
        } else {
            document.body.classList.remove("show-emoji-picker");
        }
    }
});
document.querySelector(".chat-form").appendChild(picker);

sendMessageButton.addEventListener("click", (e) => handleOutgoingMessage(e));
document.querySelector("#file-upload").addEventListener("click", () => fileInput.click());
chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chatbot"));
closeChatbot.addEventListener("click", () => document.body.classList.remove("show-chatbot"));
