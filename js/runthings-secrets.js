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

const textareas = document.querySelectorAll(".rs-view-data-item");

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
  const warningMessage = document.querySelector(".expiration-warning");

  if (expirationInput && warningMessage) {
    // Function to check if date is more than 6 months in the future
    function checkExpirationDate() {
      const selectedDate = new Date(expirationInput.value);
      const sixMonthsFromNow = new Date();
      sixMonthsFromNow.setMonth(sixMonthsFromNow.getMonth() + 6);

      if (selectedDate > sixMonthsFromNow) {
        warningMessage.style.display = "block";
      } else {
        warningMessage.style.display = "none";
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
});
