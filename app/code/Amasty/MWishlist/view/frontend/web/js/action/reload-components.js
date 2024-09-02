define([
    'jquery',
    'uiRegistry'
], function ($, registry) {

    /**
     * Reloading target components blocks
     *
     * @param {object} components - full component data
     * @return {void}
     */
    return function (components) {
        $.each(components, function (component, componentData) {
            $.each(componentData, function (property, value) {
                registry.get(component)[property](value);
            });
        });
    }
});
