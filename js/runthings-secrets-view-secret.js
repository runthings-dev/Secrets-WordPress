// Delete button confirmation
document.addEventListener("DOMContentLoaded", function () {
  const deleteForm = document.querySelector(".rs-delete-form");
  if (deleteForm) {
    deleteForm.addEventListener("submit", function (event) {
      if (
        !confirm(
          runthings_secrets.i18n.deleteConfirm ||
            "Are you sure you want to delete this secret? This action cannot be undone."
        )
      ) {
        event.preventDefault();
      }
    });
  }
});

