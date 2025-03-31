import { html, reactive } from "@arrow-js/core";
import axios from "axios";
import {
    messageComponent,
    MessageType,
    addMessage,
} from "./components/commonComponents/messageComponent.js";
import { GlobalState } from "./utils/state.js";

const state = reactive({
    showVerifyEmail: false,
    email: null,
});

const app = document.getElementById("app");

const login = html`
    <div>
        <h1>Login</h1>
        <form @submit="${handleSubmit}">
            <input type="email" name="email" placeholder="Email" required />
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            />
            <button type="submit">Login</button>
            <input
                type="hidden"
                name="csrfToken"
                value="${GlobalState.csrfToken}"
            />
        </form>
    </div>
`;

const verifyEmail = html`
    <div>
        <h1>Verify Email</h1>
        <form @submit="${handleVerifyEmail}">
            <input type="text" name="code" placeholder="Code" required />
            <button type="submit">Send Verification Email</button>
            <input
                type="hidden"
                name="csrfToken"
                value="${GlobalState.csrfToken}"
            />
        </form>
    </div>
`;

async function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    state.email = data.email;

    try {
        const response = await axios.post("/api/login", data, {
            headers: {
                "X-CSRF-TOKEN": GlobalState.csrfToken,
            },
        });
        console.log("Server response:", response.data);

        if (response.data.type === "success") {
            // User is already verified
            addMessage(
                "Login successful. Redirecting to home page...",
                MessageType.SUCCESS,
                5000
            );
            if (response.data.redirect) {
                setTimeout(() => {
                    window.location.href = response.data.redirect;
                }, 1000);
            }
        } else if (response.data.has_to_verify) {
            // User needs to verify email
            state.showVerifyEmail = true;
            addMessage(
                "Please verify your email to continue",
                MessageType.INFO,
                5000
            );
        } else {
            // Handle other error cases
            addMessage(
                response.data.message || "Error logging in",
                MessageType.ERROR,
                5000
            );
        }
    } catch (error) {
        console.error("Error logging in:", error.response?.data || error.message);
        addMessage(
            error.response?.data?.message || "Error logging in",
            MessageType.ERROR,
            5000
        );
    }
}

async function handleVerifyEmail(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    try {
        const response = await axios.post(`/api/verify-email/${data.code}`, {
            code: data.code,
            csrfToken: GlobalState.csrfToken,
            email: state.email,
        }, {
            headers: {
                "X-CSRF-TOKEN": GlobalState.csrfToken,
            },
        });

        if (response.data.type === "success") {
            addMessage(
                "Email verification successful. Redirecting...",
                MessageType.SUCCESS,
                5000
            );
            if (response.data.redirect) {
                setTimeout(() => {
                    window.location.href = response.data.redirect;
                }, 5000);
            }
        } else {
            addMessage(
                response.data.message || "Error verifying email",
                MessageType.ERROR,
                5000
            );
        }
    } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        addMessage(
            error.response?.data?.message || "Error verifying email",
            MessageType.ERROR,
            5000
        );
    }
}

// Modify template to conditionally show verify email
const template = html`
    <div>
        ${messageComponent()}
        ${login}
        ${() => state.showVerifyEmail ? verifyEmail : ''}
    </div>
`;

template(app);