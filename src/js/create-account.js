import { html } from "@arrow-js/core";
import axios from "axios";
import {
    messageComponent,
    MessageType,
    addMessage,
} from "./components/commonComponents/messageComponent.js";
import { GlobalState } from "./utils/state.js";

const app = document.getElementById("app");

const createAccount = html`
    <div>
        <h1>Create Account</h1>
        ${messageComponent()}
        <form @submit="${handleSubmit}">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
            />
            <input type="email" name="email" placeholder="Email" required />
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            />
            <input
                type="password"
                name="confirmPassword"
                placeholder="Confirm Password"
                required
            />
            <button type="submit">Create Account</button>
            <input
                type="hidden"
                name="csrfToken"
                value="${GlobalState.csrfToken}"
            />
        </form>
    </div>
`;

//TODO: Multilanguage support
async function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);

    console.log("Form data:", data);
    console.log("CSRF Token:", GlobalState.csrfToken);

    if (data.password !== data.confirmPassword) {
        addMessage("Passwords do not match", MessageType.ERROR, 5000);
        return;
    }

    try {
        const response = await axios.post("/api/create-account", data, {
            headers: {
                "X-CSRF-TOKEN": GlobalState.csrfToken,
            },
        });
        console.log("Server response:", response.data);

        if (response.data.type === "success") {
            // Changed from success to "success"
            addMessage(
                "Account created successfully! Redirecting to login page...",
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
                response.data.message || "Error creating account",
                MessageType.ERROR,
                5000
            );
        }
    } catch (error) {
        console.error(
            "Error creating account:",
            error.response?.data || error.message
        );
        addMessage(
            error.response?.data?.message || "Error creating account",
            MessageType.ERROR,
            5000
        );
    }
}

createAccount(app);
