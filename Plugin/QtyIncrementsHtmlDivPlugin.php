<?php

namespace MagePal\CartQtyIncrements\Plugin;

use Magento\CatalogInventory\Block\Qtyincrements;
use MagePal\CartQtyIncrements\Helper\Data;

class QtyIncrementsHtmlDivPlugin
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
     * @param Qtyincrements $subject
     * @param float $result
     * @return float|false
     */
    public function afterGetProductQtyIncrements($subject, $result)
    {
        if ($this->helperData->hasIgnoreCoreRestriction($subject->getProduct()->getStore()->getId())) {
            return false;
        } else {
            return $result;
        }
    }
}
