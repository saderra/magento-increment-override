<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

namespace MagePal\CartQtyIncrements\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use MagePal\CartQtyIncrements\Helper\Data;

class StockStateProviderPlugin
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * StockStateProviderPlugin constructor.
     * @param Data $helperData
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Data $helperData,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->helperData = $helperData;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param StockStateProvider $subject
     * @param callable $proceed
     * @param StockItemInterface $stockItem
     * @param float|int $qty
     */
    public function aroundCheckQtyIncrements(
        StockStateProvider $subject,
        callable $proceed,
        StockItemInterface $stockItem,
        $qty
    ) {
        $result = $proceed($stockItem, $qty);

        if ($result->getHasError() && $this->helperData->hasIgnoreCoreRestriction($stockItem->getStoreId())) {
            $result = $this->dataObjectFactory->create();
        }

        if (!$result->getHasError()) {
            $result = $this->processStoreQtyRestriction($stockItem, $qty);
            if ($result->getHasError()) {
                return $result;
            }

            $result = $this->processCustomerGroupQtyRestriction($stockItem, $qty);
            if ($result->getHasError()) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * @param $stockItem
     * @param $qty
     * @return DataObject
     */
    public function processStoreQtyRestriction($stockItem, $qty)
    {
        $result = $this->dataObjectFactory->create();
        $storeId = $stockItem->getStoreId();
        $qtyIncrements = $this->helperData->getStoreQtyIncrement($storeId);

        if ($this->helperData->isStoreQtyIncrementEnabled($storeId) && ($qty % $qtyIncrements) != 0) {
            $result->setHasError(true)
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setErrorCode('qty_increments')
                ->setMessage(__('You can only buy this product in increments of %1.', $qtyIncrements));
        }

        return $result;
    }

    public function processCustomerGroupQtyRestriction($stockItem, $qty)
    {
        $result = $this->dataObjectFactory->create();
        $storeId = $stockItem->getStoreId();
        $groupId = $stockItem->getCustomerGroupId();

        if ($this->helperData->isCustomerGroupQtyIncrementEnabled($storeId)) {
            $groups = $this->helperData->getCustomerGroupQtyIncrement($storeId);
            $allGroupId = GroupInterface::CUST_GROUP_ALL;
            $hasError = false;
            $qtyIncrements = 1;

            if (array_key_exists($groupId, $groups) && ($qty % $groups[$groupId] != 0)) {
                $qtyIncrements = $groups[$groupId];
                $hasError = true;
            } elseif (!array_key_exists($groupId, $groups)
                && array_key_exists($allGroupId, $groups) && ($qty % $groups[$allGroupId] != 0)
            ) {
                $qtyIncrements = $groups[$allGroupId];
                $hasError = true;
            }

            if ($hasError) {
                $result->setHasError(true)
                    ->setQuoteMessage(__('Please correct the quantity for some products.'))
                    ->setErrorCode('qty_increments')
                    ->setMessage(__('You can only buy this product in increments of %1.', $qtyIncrements));
            }
        }

        return $result;
    }

    /**
     * @param StockStateProvider $subject
     * @param $result
     * @param StockItemInterface $stockItem
     * @param float|int $qty
     */
    public function afterSuggestQty(
        StockStateProvider $subject,
        $result,
        StockItemInterface $stockItem,
        $qty
    ) {
        if ($this->helperData->hasIgnoreCoreRestriction($stockItem->getStoreId())) {
            return $qty;
        } else {
            return $result;
        }
    }
}
