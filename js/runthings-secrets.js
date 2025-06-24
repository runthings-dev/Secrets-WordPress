const copyToClipboardButtons = document.querySelectorAll(".copy-to-clipboard");

copyToClipboardButtons.forEach((button) => {
  const tooltip = tippy(button, {
    content: runthings_secrets.i18n.copyToClipboard,
    trigger: "mouseenter focus", // Trigger on hover and focus
    hideOnClick: false,
    interactive: true,
    duration: [250, 0],
    onShow(instance) {
      instance.setProps({ duration: [250, 0] });
    },
  });

  button.addEventListener("click", function (event) {
    event.preventDefault();
    const dataItemInput = button.previousElementSibling;
    dataItemInput.select();

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(dataItemInput.value)
        .then(() => handleCopySuccess())
        .catch((err) => {
          console.error("Failed to copy text: ", err);
        });
    } else {
      // Fallback for older browsers
      document.execCommand("copy")
        ? handleCopySuccess()
        : console.error("Failed to copy text");
    }
  });

  function handleCopySuccess() {
    tooltip.setContent(runthings_secrets.i18n.copied);
    tooltip.show();
    setTimeout(() => {
      tooltip.hide();
      tooltip.setContent(runthings_secrets.i18n.copyToClipboard);
    }, 2000);
  }
});

const dataItemInputs = document.querySelectorAll(".rs-data-item");

dataItemInputs.forEach((input) => {
  input.addEventListener("mousedown", (event) => {
    event.preventDefault(); // Prevent focus
  });
});

const textareas = document.querySelectorAll(
  ".rs-view-data-item, .rs-data-item"
);

const resizeTextarea = (ta) => {
  ta.style.height = "auto";
  ta.style.height = `${ta.scrollHeight}px`;
};

textareas.forEach((ta) => {
  ta.style.overflow = "hidden";
  ta.addEventListener("input", () => resizeTextarea(ta));
  resizeTextarea(ta);
});

window.addEventListener("resize", () => {
  textareas.forEach(resizeTextarea);
});

// Date validation for add secret form
document.addEventListener("DOMContentLoaded", function () {
  const expirationInput = document.querySelector('input[name="expiration"]');
  const expirationWarning = document.querySelector(".expiration-warning");
  const maxViewsInput = document.querySelector('input[name="max_views"]');
  const maxViewsWarning = document.querySelector(".max-views-warning");

  // Expiration date validation
  if (expirationInput) {
    // Function to check if date exceeds the warning threshold
    function checkExpirationDate() {
      if (!expirationWarning) {
        return; // Exit if warning element doesn't exist
      }

      const selectedDate = new Date(expirationInput.value);
      const warningDate = new Date(expirationInput.dataset.warningDate);

      if (selectedDate > warningDate) {
        expirationWarning.style.display = "block";
      } else {
        expirationWarning.style.display = "none";
      }
    }

    // Check on input change
    expirationInput.addEventListener("change", checkExpirationDate);
    expirationInput.addEventListener("input", checkExpirationDate);

    // Check on page load if there's already a value
    if (expirationInput.value) {
      checkExpirationDate();
    }
  }

  // Max views validation
  if (maxViewsInput) {
    // Function to check if view count exceeds the warning threshold
    function checkMaxViews() {
      if (!maxViewsWarning) {
        return; // Exit if warning element doesn't exist
      }

      const viewCount = parseInt(maxViewsInput.value);
      const warningThreshold = parseInt(maxViewsInput.dataset.warningThreshold);

      if (viewCount > warningThreshold) {
        maxViewsWarning.style.display = "block";
      } else {
        maxViewsWarning.style.display = "none";
      }
    }

    // Check on input change
    maxViewsInput.addEventListener("change", checkMaxViews);
    maxViewsInput.addEventListener("input", checkMaxViews);

    // Check on page load if there's already a value
    if (maxViewsInput.value) {
      checkMaxViews();
    }
  }
});
