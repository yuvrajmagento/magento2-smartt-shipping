<?php
namespace Smarttshipping\Shipping\Helper;

class Shipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var string
     */
    protected $_code = 'smartshipping';
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    protected $_shipment;
    protected $_dispatch;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Framework\Locale\CurrencyInterface  $localeCurrency
     * @param \Magento\Customer\Model\Session             $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipment,
        \Smarttshipping\Shipping\Model\Dispatchdetail $dispatch
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_shipment = $shipment;
        $this->_dispatch = $dispatch;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = $field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    public function getTrackingInfo($shipId)
    {

        $trackingInfo = [];
        $shipment = $this->_shipment->create()
                        ->addFieldToFilter("increment_id", $shipId)
                        ->getFirstItem();

        if ($shipment->getOrderId()) {
            $trackingInfo = $this->_dispatch->getCollection()->addFieldToFilter("order_id", $shipment->getOrderId())->getFirstItem();
        }

        return $trackingInfo;
    }
}
