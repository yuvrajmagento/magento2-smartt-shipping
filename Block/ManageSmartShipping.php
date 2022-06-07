<?php
namespace Smarttshipping\Shipping\Block;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class ManageSmartShipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpSmartShippingShipping\Helper\Data
     */
    protected $_currentHelper;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesNo;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context             $context
     * @param \Webkul\MpFedexShipping\Helper\Data                $currentHelper
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Config\Model\Config\Source\Yesno          $yesNo
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Smarttshipping\Shipping\Helper\Shipping $currentHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_currentHelper = $currentHelper;
        $this->_customerSession = $customerSession;
        $this->_yesNo = $yesNo;
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    /**
     * Prepare global layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->_customerSession->getCustomer();
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
        return $this->getHelper()->getConfigData($field);
    }
    /**
     * get current module helper.
     *
     * @return \Webkul\MpDHLShipping\Helper\Data
     */
    public function getHelper()
    {
        return $this->_currentHelper;
    }

    public function yesNoData()
    {
        return $this->_yesNo->toOptionArray();
    }

    /**
     * Retrieve current order model instance
     *
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }
    /**
     * Get Currency Code for Custom Value
     *
     * @return string
     */
    public function getCustomValueCurrencyCode()
    {
        $orderInfo = $this->getOrder();
        return $orderInfo->getBaseCurrency()->getCurrencyCode();
    }
}
