/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Customerprice
 * @copyright Copyright (c) 2017 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

define([
    'Magento_Ui/js/modal/modal-component',
    'uiRegistry',
    'underscore'
], function (Modal, registry, _) {
    'use strict';

    return Modal.extend({
        defaults: {
            stepWizard: '',
            modules: {
                form: '${ $.formName }'
            }
        },

        /**
         * Open modal
         */
        openModal: function () {
            var stepWizard = {};

            this.form().validate();

            if (this.form().source.get('params.invalid') === false) {
                stepWizard = registry.get('index = ' + this.stepWizard);

                if (!_.isUndefined(stepWizard)) {
                    stepWizard.open();
                }

                this._super();
            }
        }
    });
});
