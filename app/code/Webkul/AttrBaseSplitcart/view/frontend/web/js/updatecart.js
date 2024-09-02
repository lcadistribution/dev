/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require([
  'Magento_Customer/js/customer-data'
  ], function (customerData) {
  return function () {
      var sections = ['cart'];
      //  customerData.invalidate(sections);
      customerData.reload(sections, true);
  }
});
