<?php
namespace Magebees\Wholesaleconfigurable\Model\Config;

class Displayattributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $customerGroupModel;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Group $customerGroupModel,
        array $data = []
    ) {
        $this->customerGroupModel = $customerGroupModel;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $allgroups = [
            ['value' => 'image','label' => __('Image')], 
            ['value' => 'sku','label' => __('SKU')], 
            ['value' => 'availability','label' => __('Availability')], 
            ['value' => 'unitprice','label' => __('Unit Price')], 
            ['value' => 'subtotal','label' => __('Subtotal')], 

		];

        return $allgroups;
    }
}
