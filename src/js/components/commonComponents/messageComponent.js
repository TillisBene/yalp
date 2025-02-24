import { html, reactive } from "@arrow-js/core";

const messages = reactive([]);

const MessageType = {
    INFO: 'info',
    ERROR: 'error',
    WARNING: 'warning',
    SUCCESS: 'success'
};

const addMessage = (text, type = MessageType.INFO, duration = null) => {
    const message = { 
        text, 
        type, 
        id: Date.now() 
    };
    
    messages.push(message);
    console.log('Added message:', message);
    console.log('Current messages:', messages);
    
    if (duration) {
        setTimeout(() => {
            const index = messages.indexOf(message);
            if (index > -1) {
                messages.splice(index, 1);
            }
        }, duration);
    }
};

const messageComponent = () => {
    return html`
    <div class="message-stack">
        ${messages.length 
            ? messages.map(msg => html`
                <div class="message message-${msg.type}" key="${msg.id}">
                    ${msg.text}
                </div>
            `)
            : html`<div style="display: none" data-empty-message>No messages</div>`
        }
    </div>
`;}

export { messageComponent, addMessage, MessageType };