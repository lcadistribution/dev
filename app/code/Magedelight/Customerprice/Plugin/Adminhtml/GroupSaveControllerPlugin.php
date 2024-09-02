<?php
namespace Magedelight\Customerprice\Plugin\Adminhtml;


/**
 * @package Magedelight_Customerprice for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

class GroupSaveControllerPlugin
{
	/**
     * @var Magedelight\Customerprice\Model\CustomerGroupPrice
     */
    private $customerGroupPrice;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $helper;

    /**
     * @var \Magedelight\Customerprice\Helper\Data
     */
    private $group;

	public function __construct(\Magedelight\Customerprice\Helper\Data $helper,
	\Magedelight\Customerprice\Api\Data\CustomerGroupPriceInterface $customerGroupPrice,
	\Magento\Customer\Model\Group $group){
		$this->helper = $helper;
		$this->customerGroupPrice = $customerGroupPrice;
		$this->group = $group;
	}

    public function afterExecute(
        \Magento\Customer\Controller\Adminhtml\Group\Save $subject,
        $result
    ) {
        if($this->helper->isEnabled() && $this->helper->getConfig('customerprice/general/enable_customer_groupprice'))
        {
        	$data = $subject->getRequest()->getPostValue();
        	$groupId = NULL;
        	if (isset($data['value']) && isset($data['price_type'])) { 
        		
        		if (isset($data['id'])){
        		 	$groupId = $data['id'];
        		}else{
        			$groupModel = $this->group->load($data['code'],'customer_group_code');
        			if ($groupModel) {
        				$groupId = $groupModel->getCustomerGroupId();
        			}
        		} 
        		if($groupId!=NULL){
                	$this->saveCustomerGroupPrice($data,$groupId);
                }
            }
        }

        return $result;
    }

    /**
    * @param array|null
    * @param int
    * @return boolean 
    */
    private function saveCustomerGroupPrice($data,$groupId){

        $firstCharacter = mb_substr($data['value'] ?? "", 0, 1);
        $model = $this->customerGroupPrice->load($groupId, 'group_id');

        if(!$model->getCustomergrouppriceId()){
            
            // If the model does not exist, create a new one only if the first character is '+' or '-'
            if ($firstCharacter === '+' || $firstCharacter === '-') {
                $model = $this->customerGroupPrice;
                $model->setCustomergrouppriceId(null);
                $model->setGroupId($groupId);
            } else {
                // No need to save an empty or invalid entry
                return;
            }
        }

        // Set common attributes regardless of whether it's a new or existing model
        $model->setValue($firstCharacter === '+' || $firstCharacter === '-' ? $data['value'] : "");
        $model->setPriceType($data['price_type']);

        $model->save();

        return true;
    }
}
