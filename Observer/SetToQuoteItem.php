<?php
namespace Smarttshipping\Shipping\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class SetToQuoteItem implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setShippingHeight($product->getShippingHeight());
        $quoteItem->setShippingLength($product->getShippingLength());
        $quoteItem->setShippingWidth($product->getShippingWidth());
        $quoteItem->setShippingWeight($quoteItem->getWeight()*$quoteItem->getQty());
        $quoteItem->setPackageId($product->getPackageId());
        $quoteItem->setRecalculateQty($quoteItem->getQty());
    }
}
