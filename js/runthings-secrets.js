document
  .getElementById("copy-to-clipboard")
  .addEventListener("click", function () {
    const viewingUrlInput = document.getElementById("viewing-url");

    if (navigator.clipboard && navigator.clipboard.writeText) {
      const textToCopy = viewingUrlInput.value;
      navigator.clipboard
        .writeText(textToCopy)
        .then(() => {
          console.log("Text copied to clipboard");
        })
        .catch((err) => {
          console.error("Failed to copy text: ", err);
        });
    } else {
      // Fallback for older browsers
      viewingUrlInput.select();
      viewingUrlInput.setSelectionRange(0, 99999); // For mobile devices

      try {
        const successful = document.execCommand("copy");
        if (successful) {
          console.log("Text copied to clipboard");
        } else {
          console.error("Failed to copy text");
        }
      } catch (err) {
        console.error("Failed to copy text: ", err);
      }
    }
  });

document.getElementById("viewing-url").addEventListener("click", function () {
  this.select();
});
