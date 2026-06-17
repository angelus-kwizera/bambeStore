(function () {
    const STORAGE_KEY = 'bambe_chat_history';

    function createWidget() {
        const widget = document.createElement('div');
        widget.className = 'chatbot';
        widget.innerHTML = `
            <button class="chatbot__toggle" id="chatbotToggle" aria-label="Open chat assistant">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span class="chatbot__badge">AI</span>
            </button>
            <div class="chatbot__panel" id="chatbotPanel" hidden>
                <div class="chatbot__header">
                    <div>
                        <strong>Bambe Assistant</strong>
                        <span class="chatbot__status">AI Shopping Helper</span>
                    </div>
                    <button class="chatbot__close" id="chatbotClose" aria-label="Close chat">&times;</button>
                </div>
                <div class="chatbot__messages" id="chatbotMessages"></div>
                <form class="chatbot__input" id="chatbotForm">
                    <input type="text" id="chatbotInput" placeholder="Ask about products, delivery..." autocomplete="off" maxlength="500">
                    <button type="submit" aria-label="Send message">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </form>
            </div>
        `;
        document.body.appendChild(widget);
        return widget;
    }

    function loadHistory() {
        try {
            return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveHistory(history) {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(-20)));
    }

    function appendMessage(container, role, text) {
        const msg = document.createElement('div');
        msg.className = `chatbot__message chatbot__message--${role}`;
        msg.textContent = text;
        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;
    }

    function appendTyping(container) {
        const el = document.createElement('div');
        el.className = 'chatbot__message chatbot__message--bot chatbot__typing';
        el.id = 'chatbotTyping';
        el.textContent = 'Thinking...';
        container.appendChild(el);
        container.scrollTop = container.scrollHeight;
        return el;
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.body.classList.contains('admin-body')) return;

        createWidget();

        const toggle = document.getElementById('chatbotToggle');
        const panel = document.getElementById('chatbotPanel');
        const close = document.getElementById('chatbotClose');
        const form = document.getElementById('chatbotForm');
        const input = document.getElementById('chatbotInput');
        const messages = document.getElementById('chatbotMessages');

        let history = loadHistory();
        let opened = false;

        if (history.length === 0) {
            appendMessage(messages, 'bot', "Muraho! 👋 I'm Bambe Assistant. Ask me about products, delivery, returns, or payment options.");
        } else {
            history.forEach((entry) => {
                if (entry.role === 'user' || entry.role === 'assistant') {
                    appendMessage(messages, entry.role === 'user' ? 'user' : 'bot', entry.content);
                }
            });
        }

        toggle.addEventListener('click', () => {
            panel.hidden = !panel.hidden;
            if (!panel.hidden && !opened) {
                opened = true;
                input.focus();
            }
        });

        close.addEventListener('click', () => {
            panel.hidden = true;
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            input.value = '';
            input.disabled = true;
            appendMessage(messages, 'user', text);

            const apiHistory = history.map((h) => ({
                role: h.role === 'assistant' ? 'assistant' : 'user',
                content: h.content,
            }));

            const typing = appendTyping(messages);

            try {
                const response = await fetch('api/chatbot.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, history: apiHistory }),
                });
                const data = await response.json();
                typing.remove();

                const reply = data.reply || 'Sorry, I could not process that. Please try again.';
                appendMessage(messages, 'bot', reply);

                history.push({ role: 'user', content: text });
                history.push({ role: 'assistant', content: reply });
                saveHistory(history);
            } catch {
                typing.remove();
                appendMessage(messages, 'bot', 'Connection error. Please check your internet and try again.');
            }

            input.disabled = false;
            input.focus();
        });
    });
})();
