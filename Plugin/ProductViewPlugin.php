<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */

namespace MagePal\CartQtyIncrements\Plugin;

use Magento\Catalog\Block\Product\View;
use Magento\CatalogInventory\Block\Plugin\ProductView;
use MagePal\CartQtyIncrements\Helper\Data;

class ProductViewPlugin
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * StockStateProviderPlugin constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param ProductView $subject
     * @param $result
     * @param View $block
     * @param array $validators
     */
    public function afterAfterGetQuantityValidators(ProductView $subject, $result, View $block, array $validators)
    {
        $storeId = $block->getProduct()->getStoreId();

        if ($this->helperData->hasIgnoreCoreRestriction($storeId) && is_array($result)) {
            $defaultQtyIncrements = 1;

            if ($this->helperData->isStoreQtyIncrementEnabled($storeId)
                && $this->helperData->getStoreQtyIncrement($storeId) > 0
                && !$this->helperData->isCustomerGroupQtyIncrementEnabled($storeId)
            ) {
                $defaultQtyIncrements = $this->helperData->getStoreQtyIncrement($storeId);
            }

            $result['validate-item-quantity']['qtyIncrements'] = (float) $defaultQtyIncrements;
        }

        return $result;
    }
}
