/**
 *  Amasty Qty widget
 *
 *  @desc Qty Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    $.widget('mage.amwishlistQty', {
        options: {
            qty: 1,
            min: 1,
            max: null,
            selectors: {
                incrButton: '[data-amwishlist-js="incr"]',
                decrButton: '[data-amwishlist-js="decr"]',
                input: '[data-amwishlist-js="input"]'
            },
        },
        classes: {
            error: '-error'
        },
        nodes: {
            error: $('<span>', {
                'class': 'amwishlist-msg -error'
            }),
            decrButton: $('<button>', {
                'class': 'amwishlist-button -clear -decr',
                'type': 'button',
                'data-amwishlist-js': 'decr'
            }),
            incrButton: $('<button>', {
                'class': 'amwishlist-button -clear -incr',
                'type': 'button',
                'data-amwishlist-js': 'incr'
            })
        },

        _create: function () {
            var self = this,
                options = this.options;

            self.incrButton = self.nodes.incrButton.clone();
            self.input = self.element.find(options.selectors.input);
            self.decrButton = self.nodes.decrButton.clone();

            self.element
                .prepend(
                    self.decrButton,
                )
                .append(
                    self.incrButton
                );

            options.qty = parseInt(self.input.val(), 10);
            options.min = parseInt(self.input.attr('min'), 10);
            options.max = parseInt(self.input.attr('max'), 10);

            self.element.attr({
                'qty': options.qty
            });

            self.setInput(false);

            self.incrButton.click(function () {
                ++options.qty

                self.setInput(true);
            });

            self.decrButton.click(function () {
                --options.qty

                self.setInput(true);
            });

            self.input.on('input', function () {
                options.qty = parseInt(this.value);

                self.setInput(true);
            });
        },

        /**
         * Subscribe for qty input disabled changing.
         * If qty input disabled - increment&decrement controls must be disabled.
         */
        resolveDisabledState: function () {
            var self = this,
                observer = new MutationObserver(subscriber);

            function subscriber(mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                        if (mutation.target.disabled) {
                            self.element.addClass('-disabled');
                        } else {
                            self.element.removeClass('-disabled');
                        }
                    }
                });
            }

            observer.observe(self.input[0], {
                attributes: true
            });
        },

        /**
         * Set target qty for input
         * @param {boolean} trigger
         */
        setInput: function (trigger) {
            var options = this.options;

            this.clearError();

            if (options.max && options.qty > options.max) {
                options.qty = options.max;

                this.setError($t('Max value is') + ' ' + options.qty);
            }

            if (options.qty < options.min) {
                options.qty = options.min;

                this.setError($t('Min value is') + ' ' + options.qty);
            }

            this.input.val(options.qty);
            this.element.attr({
                'qty': options.qty
            });

            if (trigger) {
                this.input.trigger('change');
            }
        },

        /**
         * Set error message input
         */
        setError: function (msg) {
            if (!this.errorMsg) {
                this.errorMsg = this.nodes.error.clone();
            }

            this.input.addClass(this.classes.error);
            this.errorMsg.text(msg).show();
            this.element.append(this.errorMsg);
        },

        /**
         * Clear error message
         */
        clearError: function () {
            if (this.errorMsg) {
                this.errorMsg.text('').hide();
                this.input.removeClass(this.classes.error);
            }
        }
    });

    return $.mage.amwishlistQty
});
