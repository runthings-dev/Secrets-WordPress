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

