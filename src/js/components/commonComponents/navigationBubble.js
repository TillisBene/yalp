import { html, reactive } from "@arrow-js/core";
import { pageController } from "../../controller/pageController.js";

const MAX_WORDS = 100; // Maximum words allowed
const MAX_CHARS = 500;

const controller = pageController();

function calcPullHeight() {
    return window.innerHeight * 0.5; // 30% of screen height as threshold
}

export const NavigationBubble = () => {
    const state = reactive({
        open: false,
        navigationIndex: null,
        pulledHeight: 0,
        maxPulledHeight: calcPullHeight(),
        inTransition: false,
        isDragging: false,
        startY: 0,
        expandHeight: 0,
        textareaContent: "",
        wordCount: 0,
        charCount: 0,
        isLimitReached: false,
    });

    const handleTextareaInput = (e) => {
        const textarea = e.target;
        const text = textarea.value;

        // Count words and characters
        const words = text.trim() ? text.trim().split(/\s+/) : [];
        state.wordCount = words.length;
        state.charCount = text.length;

        // Check if limit is reached
        state.isLimitReached =
            state.wordCount >= MAX_WORDS || state.charCount >= MAX_CHARS;

        // Only modify the text if limits are exceeded
        let updatedText = text;
        if (state.isLimitReached) {
            if (state.charCount > MAX_CHARS) {
                updatedText = text.slice(0, MAX_CHARS);
            }
            if (state.wordCount > MAX_WORDS && words.length > 0) {
                updatedText = words.slice(0, MAX_WORDS).join(" ");
            }

            // Only update the value if it needed truncation
            if (updatedText !== text) {
                textarea.value = updatedText;
                // Recalculate counts after truncation
                state.wordCount = updatedText.trim()
                    ? updatedText.trim().split(/\s+/).length
                    : 0;
                state.charCount = updatedText.length;
            }
        }

        // Always update content at the end
        state.textareaContent = textarea.value;

        // Auto-resize textarea (after value is finalized)
        setTimeout(() => {
            textarea.style.height = "auto";
            textarea.style.height = textarea.scrollHeight + "px";
        }, 0);
    };

    // Handle drag start
    const handleDragStart = (e) => {
        if (!e) return;
        console.log("Drag start", e);
        state.isDragging = true;
        document
            .querySelector(".navigation-bubble")
            .classList.add("is-dragging");
        state.startY = e.type.includes("touch")
            ? e.touches[0].clientY
            : e.clientY;

        // Add move and end listeners only when dragging starts
        if (e.type.includes("touch")) {
            document.addEventListener("touchmove", handleDragMove, {
                passive: false,
            });
            document.addEventListener("touchend", handleDragEnd);
        } else {
            document.addEventListener("mousemove", handleDragMove);
            document.addEventListener("mouseup", handleDragEnd);
        }
    };

    // Update the handleDragEnd function to reset all size-related state
    const handleDragEnd = () => {
        state.isDragging = false;
        document
            .querySelector(".navigation-bubble")
            .classList.remove("is-dragging");
        state.pulledHeight = 0;

        // Reset the expandHeight so the bubble returns to its original size
        if (!state.open) {
            state.expandHeight = 0;
        }

        // Remove event listeners when dragging ends
        document.removeEventListener("mousemove", handleDragMove);
        document.removeEventListener("touchmove", handleDragMove);
        document.removeEventListener("mouseup", handleDragEnd);
        document.removeEventListener("touchend", handleDragEnd);
    };

    // Define closeNav function for use within handleDragMove
    const closeNav = () => {
        state.open = false;
        state.inTransition = true;
        state.expandHeight = 0; // Reset expand height
        setTimeout(() => {
            state.inTransition = false;
        }, 300);
    };

    // Handle drag movement
    const handleDragMove = (e) => {
        if (!state.isDragging || !e) return;

        const currentY = e.type.includes("touch")
            ? e.touches[0].clientY
            : e.clientY;
        const deltaY = state.startY - currentY;

        // Calculate a scale factor for visual feedback during drag
        const scaleFactor = Math.min(1 + Math.abs(deltaY) / 1000, 1.1);
        state.scaleFactor = scaleFactor;

        if (!state.open) {
            // When closed, only allow upward dragging to open
            if (deltaY > 0) {
                state.pulledHeight = Math.min(deltaY, state.maxPulledHeight);

                // Update expanding height during pull
                state.expandHeight = Math.min(deltaY / 2, 100);

                // If pulled past threshold, open overlay
                if (
                    state.pulledHeight > state.maxPulledHeight * 0.6 &&
                    !state.open
                ) {
                    state.open = true;
                    state.inTransition = true;
                    setTimeout(() => (state.inTransition = false), 300);
                }
            }
        } else {
            // When open, allow downward dragging to close
            if (deltaY < 0) {
                const pullDownDistance = Math.abs(deltaY);
                state.pulledHeight = -pullDownDistance;

                // If pulled down past threshold, close overlay
                if (pullDownDistance > state.maxPulledHeight * 0.3) {
                    closeNav(); // Use closeNav function instead of duplicating logic
                }
            }
        }

        e.preventDefault();
    };

    const randomText = [
        "Write something...",
        "Share your thoughts...",
        "Drop a comment...",
        "Type something here...",
        "Write a message...",
        "Share your opinion...",
        "Leave a note...",
        "Express yourself...",
        "Write a review...",
        "Share your feedback...",
        "Leave a message...",
        "Write a story...",
        "Spread misinformation...",
        "Share your memories...",
        "Tell a tale!",
        "Spit some facts...",
        "Insert text here!",
        "Commit something...",
        "Spill a secret!",
        "Share a bad take...",
        "Write something fun!",
    ];

    const template = html`
        <div
            class="${() =>
                `navigation-bubble ${state.open ? "open" : ""} ${
                    state.inTransition ? "in-transition" : ""
                } ${state.isDragging ? "is-dragging" : ""}`}"
        >
            <div
                class="${() =>
                    `navigation-bubble__handle ${state.open ? "hidden" : ""}`}"
                style="${() =>
                    !state.open
                        ? `height: ${25 + state.pulledHeight * 0.5}px`
                        : ""}"
                @mousedown="${handleDragStart}"
                @touchstart="${handleDragStart}"
            >
                <div class="handle-indicator"></div>
            </div>

            ${() =>
                state.open
                    ? html`
                          <div class="navigation-bubble__overlay">
                              <div
                                  class="navigation-bubble__overlay-handle"
                                  @mousedown="${handleDragStart}"
                                  @touchstart="${handleDragStart}"
                              >
                                  <div class="handle-indicator"></div>
                              </div>

                              <div class="navigation-bubble__content">
                                  <ul>
                                      <li>Profile</li>
                                      <li>
                                          ${controller.createPageLink(
                                              "settings",
                                              "settings"
                                          )}
                                      </li>
                                  </ul>
                              </div>
                          </div>
                      `
                    : html`
                          ${() =>
                              !state.open
                                  ? html`
                                    <div
                                        class="navigation-bubble__content-closed"
                                        style="${() => `height: ${80 + (state.expandHeight || 0)}px`}"
                                    >
                                        <textarea
                                            cols="10"
                                            rows="1"
                                            placeholder="${() => randomText[Math.floor(Math.random() * randomText.length)]}"
                                            @input="${handleTextareaInput}"
                                            .value="${state.textareaContent}"
                                            style="min-height: 40px; overflow: hidden;"
                                        ></textarea>

                                        <div class="${() => state.isLimitReached ? 'textarea-counter limit-reached' : 'textarea-counter'}">
                                            <span>${() => state.wordCount}</span>/${MAX_WORDS} words |
                                            <span>${() => state.charCount}</span>/${MAX_CHARS} chars
                                        </div>
                                    </div>
                                    `
                                  : ""}
                      `}
        </div>
    `;

    return template;
};
