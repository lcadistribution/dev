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
namespace Webkul\AttrBaseSplitcart\Cookie;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 *  Webkul AttrBaseSplitcart Cookie Guestcart
 */
class Guestcart
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;
    
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieMetadataFactory;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;
    
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddressInstance;
    
    /**
     * @var \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger
     */
    protected $logger;
    
    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddressInstance
     * @param \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger $logger
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddressInstance,
        \Webkul\AttrBaseSplitcart\Logger\AttrBaseLogger $logger
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->remoteAddressInstance = $remoteAddressInstance;
        $this->logger = $logger;
    }

    /**
     * Get data from cookie
     *
     * @return string
     */
    public function get()
    {
        try {
            $remoteAddress =  $this->cookieManager->getCookie($this->getRemoteAddress());
        } catch (\Exception $e) {
            $this->logger->info("Cookie_Guestcart get : ".$e->getMessage());
        }
        return $remoteAddress;
    }

    /**
     * [set used to set virtual cart in cookie for guest user]
     *
     * @param [string] $value    [contains value of cookie]
     * @param integer  $duration [contains duration for cookie]
     *
     * @return void
     */
    public function set($value, $duration = 86400)
    {
        try {
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration($duration)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain());

            $this->cookieManager->setPublicCookie(
                $this->getRemoteAddress(),
                $value,
                $metadata
            );
        } catch (\Exception $e) {
            $this->logger->info("Cookie_Guestcart set : ".$e->getMessage());
        }
    }

    /**
     * [delete used to delete cookie]
     *
     * @return void
     */
    public function delete()
    {
        try {
            $this->cookieManager->deleteCookie(
                $this->getRemoteAddress(),
                $this->cookieMetadataFactory
                    ->createCookieMetadata()
                    ->setPath($this->sessionManager->getCookiePath())
                    ->setDomain($this->sessionManager->getCookieDomain())
            );
        } catch (\Exception $e) {
            $this->logger->info("Cookie_Guestcart delete : ".$e->getMessage());
        }
    }

    /**
     * [getRemoteAddress used to get remote address]
     *
     * @return [string] [returns modified string of remote addr]
     */
    public function getRemoteAddress()
    {
        try {
            $str = str_replace(
                ".",
                "_",
                $this->remoteAddressInstance->getRemoteAddress()
            );
            return $str;
        } catch (\Exception $e) {
            $this->logger->info("Cookie_Guestcart getRemoteAddress : ".$e->getMessage());
        }
    }
}
