<?php
namespace Blueskytechco\CustomCatalog\Plugin;

class CustomerData {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ){
        $this->scopeConfig = $scopeConfig;
        $this->checkoutHelper = $checkoutHelper;
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        // Get the previous data
        $data = $result;

        // Append variable

        
        $enable_f_c = $this->getConfigData('themesetting/fake_time_cart/enable_f_c');
        $fake_time_cart_text = $this->getConfigData('themesetting/fake_time_cart/text');
        $fake_time_cart_date = $this->getConfigData('themesetting/fake_time_cart/date');


        $html_fake_time_cart = '';
        $data['fake_time_cart_text'] = '';
        $data['fake_time_cart_date'] = false;

        if ($enable_f_c) {
            $data['fake_time_cart_text'] = $fake_time_cart_text;
            $data['fake_time_cart_date'] = $fake_time_cart_date;
        }

        $active = $this->getConfigData('carriers/freeshipping/active');
        $shipping_subtotal = $this->getConfigData('carriers/freeshipping/free_shipping_subtotal');
        $html = '';
        $data['shipping_free'] = '';
        $data['shipping_free_canvas'] = false;
        if ($active) {
            $subtotal_amount = $data['subtotalAmount'];
            $summary_count = $data['summary_count'];
            if (!$shipping_subtotal) {
                $shipping_subtotal = 0;
            }
            $price = $this->checkoutHelper->formatPrice($shipping_subtotal);
            if ($summary_count > 0) {
                if ($shipping_subtotal > 0 && $shipping_subtotal > $subtotal_amount) {
                    $percent = ($subtotal_amount/$shipping_subtotal)*100;
                    $remaining_price = $shipping_subtotal - $subtotal_amount;
                    $remaining_price = $this->checkoutHelper->formatPrice($remaining_price);
                    $html .= '<div class="cart_shipping">';
                        $html .= '<div class="cart_thres_2">';
                            $html .= __('Buy %1 more to enjoy %2', $remaining_price, '<span class="bold">'.__('FREE SHIPPING!').'</span>');
                        $html .= '</div>';
                        $html .= '<div class="cart_bar_w">';
                            $html .= '<span style="width: '.$percent.'%;"></span>';
                        $html .= '</div>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="cart_shipping cart_shipping_free">';
                        $html .= '<div class="cart_thres_3">';
                            $html .= __("Congrats! You are eligible for %1 ", '<span>'.__('FREE SHIPPING!').'</span>');
                        $html .= '</div>';
                        $html .= '<div class="cart_bar_w free">';
                            $html .= '<span style="width: 100%;"></span>';
                        $html .= '</div>';
                    $html .= '</div>';
                    $data['shipping_free_canvas'] = 1;
                }
            } else {
                $html .= '<div class="cart_thres_1">';
                    $html .= __('Free Shipping for all orders over %1', $price);
                $html .= '</div>';
            }
            $data['shipping_free'] = $html;
        }
        return $data;
    }

    public function getConfigData($path)
    {
        $value = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }
}
