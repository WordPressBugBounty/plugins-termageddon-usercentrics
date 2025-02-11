// Create a new style tag in the head of the document.
const style = document.createElement("style");
style.id = "termageddon-usercentrics-integration-uabb-video-style";
style.textContent = `
.uabb-video__outer-wrap:before {
    z-index: 0 !important;
}
`;

// Append the style tag to the head of the document.
document.head.appendChild(style);
