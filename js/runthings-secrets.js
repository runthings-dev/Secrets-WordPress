const copyToClipboardButtons = document.querySelectorAll('.copy-to-clipboard');

copyToClipboardButtons.forEach((button) => {
  const tooltip = tippy(button, {
    content: runthings_secrets.i18n.copyToClipboard,
    trigger: 'mouseenter focus', // Trigger on hover and focus
    hideOnClick: false,
    interactive: true,
    duration: [250, 0],
    onShow(instance) {
      instance.setProps({ duration: [250, 0] });
    },
  });

  button.addEventListener('click', function (event) {
    event.preventDefault();
    const dataItemInput = button.previousElementSibling;
    dataItemInput.select();

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(dataItemInput.value)
        .then(() => handleCopySuccess())
        .catch((err) => {
          console.error('Failed to copy text: ', err);
        });
    } else {
      // Fallback for older browsers
      document.execCommand('copy')
        ? handleCopySuccess()
        : console.error('Failed to copy text');
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

const dataItemInputs = document.querySelectorAll('.rs-data-item');

dataItemInputs.forEach((input) => {
  input.addEventListener('mousedown', (event) => {
    event.preventDefault(); // Prevent focus
  });
});
