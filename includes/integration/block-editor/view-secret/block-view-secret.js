(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement } = wp.element;
  const { __ } = wp.i18n;

  registerBlockType("runthings-secrets/view-secret", {
    title: "RunThings Secrets - View Secret",
    icon: "visibility",
    category: "widgets",

    edit: function (props) {
      return createElement(
        "div",
        null,
        createElement(
          "h3",
          null,
          __("The 'view secret' layout will be shown here.", "runthings-secrets")
        )
      );
    },

    save: function () {
      return null;
    },
  });
})(window.wp);
