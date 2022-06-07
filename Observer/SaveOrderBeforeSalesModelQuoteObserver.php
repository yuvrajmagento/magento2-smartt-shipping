<?php
namespace Smarttshipping\Shipping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{

    /**
     * List of attributes that should be added to an order.
     *
     * @var array
     */
    private $attributes = [
        'smart_shipping_carrier_estimate'
    ];
    /**

     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($this->attributes as $attribute) {
            if ($quote->hasData($attribute)) {
                $order->setData($attribute, $quote->getData($attribute));
            }
        }

        return $this;
    }
}
