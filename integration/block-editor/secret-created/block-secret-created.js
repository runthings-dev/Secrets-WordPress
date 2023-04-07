(function (wp) {
    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { __ } = wp.i18n;
  
    registerBlockType("runthings-secrets/secret-created", {
      title: "RunThings Secrets - Secret Created",
      icon: "lock",
      category: "widgets",
  
      edit: function (props) {
        return createElement(
          "div",
          null,
          createElement(
            "h3",
            null,
            __("The 'created secret' layout will be shown here.", "runthings-secrets")
          )
        );
      },
  
      save: function () {
        return null;
      },
    });
  })(window.wp);
  