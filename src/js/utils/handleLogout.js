import axios from "axios";
import { GlobalState } from "./state";
import { addMessage, MessageType } from "../components/commonComponents/messageComponent";

export async function handleLogout() {

    try {
        const response = await axios.get(`/api/logout`, {
            csrfToken: GlobalState.csrfToken,
        }, {
            headers: {
                "X-CSRF-TOKEN": GlobalState.csrfToken,
            },
        });
        setTimeout(() => {
            window.location.href = '/';
        }, 1000);
    } catch (error) {
        console.error("Error:", error.response?.data || error.message);
        addMessage(
            error.response?.data?.message || "Logout-Error",
            MessageType.ERROR,
            5000
        );
    }
}