import { html, reactive } from "@arrow-js/core";
import { GlobalState } from "../../utils/state.js";

export const NavigationBubble = () => {
    const state = reactive({
        open: false,
        navigationIndex: null,
        pulledHeight: null,
        maxPulledHeight: null,
        inTransition: false,
    });

    const data = {
        navigationItems: [
            { name: "Home", link: "/" },
            { name: "Profile", link: "/profile" },
            { name: "Settings", link: "/settings" },
        ],
    }

    return html`
        <div class="navigation-bubble">
            <button class="navigation-bubble__button" @click=${() => state.open = !state.open}>Toggle</button>
            ${state.open && html`
                <div class="navigation-bubble__content">
                    <ul>
                        <li>Home</li>
                        <li>Profile</li>
                        <li>Settings</li>
                    </ul>
                </div>
            `}
        </div>
    `;
}