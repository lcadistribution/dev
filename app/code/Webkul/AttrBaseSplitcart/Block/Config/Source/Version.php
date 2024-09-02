<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AttrBaseSplitcart
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AttrBaseSplitcart\Block\Config\Source;

use Magento\Backend\Block\Context;
use Magento\Framework\Module\PackageInfoFactory;

class Version extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var PackageInfoFactory
     */
    protected $_packageInfoFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PackageInfoFactory $packageInfoFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->_packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $packageInfo = $this->_packageInfoFactory->create();
        $moduleCode = 'Webkul_AttrBaseSplitcart';
        $version = $packageInfo->getVersion($moduleCode);

        $html = '<div class="translation"><p>';
        $html.=__("Author:");
        $html.=' <a target="_blank" title="Webkul Software Private Limited" href="https://webkul.com/">Webkul</a>
        </p><p>';
        $html.=__("Version:").' '. $version.'</p><p>';
        $html.=__("User Guide:").
        ' <a target="_blank" href="https://webkul.com/blog/magento2-cart-split-based-attribute/">';
        $html.=__("Click Here").'</a></p>
        <p>'.__("Store Extension:").
        ' <a target="_blank" href="https://store.webkul.com/magento2-split-cart.html">';
        $html.=__("Click Here").'</a></p>
        <p>'.__("Ticket/Customisations:").
        ' <a target="_blank" href="https://webkul.uvdesk.com/en/customer/create-ticket/">'.__("Click Here").'</a></p>
        <p>'.__("Services:").
        ' <a target="_blank" href="https://webkul.com/magento-development/">'.__("Click Here").'</a></p></div>';
        return $html;
    }
}
