define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        var $element = $(element);
        var form_key = $('.column.main input[name="form_key"]').val();
        $element.find('input[name="form_key"]').val(form_key);
    };
});