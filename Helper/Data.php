<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\CartQtyIncrements\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ACTIVE = 'magepal_cart_qty_increments/general/active';
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * Data constructor.
     * @param Context $context
     * @param SerializerInterface $json
     */
    public function __construct(
        Context $context,
        SerializerInterface $json
    ) {
        parent::__construct($context);
        $this->json = $json;
    }

    /**
     * If enabled
     *
     * @param $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function hasIgnoreCoreRestriction($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->isSetFlag(
            'magepal_cart_qty_increments/general/ignore_core_restriction',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isStoreQtyIncrementEnabled($storeId = null)
    {
        $isSectionEnabled = $this->scopeConfig->isSetFlag(
            'magepal_cart_qty_increments/store_qty/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $isSectionEnabled && $this->isEnabled($storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getStoreQtyIncrement($storeId = null)
    {
        return (int) $this->scopeConfig->getValue(
            'magepal_cart_qty_increments/store_qty/increment',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isCustomerGroupQtyIncrementEnabled($storeId = null)
    {
        $isSectionEnabled = $this->scopeConfig->isSetFlag(
            'magepal_cart_qty_increments/customer_group/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $isSectionEnabled && $this->isEnabled($storeId);
    }

    /**
     * @param null $storeId
     * @return array|bool|float|int|string
     */
    public function getCustomerGroupQtyIncrement($storeId = null)
    {
        $json = $this->scopeConfig->getValue(
            'magepal_cart_qty_increments/customer_group/increment',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            $result = $this->json->unserialize($json);
        } catch (Exception $e) {
            $result = [];
        }

        return $result;
    }

}
