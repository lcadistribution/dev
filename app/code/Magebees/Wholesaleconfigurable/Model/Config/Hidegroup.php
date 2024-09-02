<?php
namespace Magebees\Wholesaleconfigurable\Model\Config;

class Hidegroup implements \Magento\Framework\Option\ArrayInterface
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
        $group = $this->customerGroupModel;
        $allgroups = [
            [
                'value' => '',
                'label' => __('None')
            ], [
                'value' => 'all',
                'label' => __('All Groups')
            ] ];

        return array_merge(
            $allgroups,
            $group->getCollection()->toOptionArray()
        );
    }
}
