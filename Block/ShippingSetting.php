<?php
namespace Smarttshipping\Shipping\Block;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class ShippingSetting extends \Magento\Directory\Block\Data
{

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_address;

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
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Registry $coreRegistry,
        \Smarttshipping\Shipping\Helper\Shipping $shippinghelper,
        \Smarttshipping\Shipping\Model\AddresssettingFactory $addresssettingFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_shippingHelper = $shippinghelper;
        $this->_addresssettingFactory = $addresssettingFactory;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare global layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_address = '';
        if ($this->_customerSession->isLoggedIn()) {
            $customerid = $this->_customerSession->getCustomer()->getId();
            $addresssetting = $this->_addresssettingFactory->create();
            $addresssetting->load($customerid, "customer_id");
            if ($addresssetting->getId()) {
                $this->_address = $addresssetting;
            }
        }
        if ($this->_address) {
            $this->setData('country_id', $this->_address->getCountry());
        } else {
            $this->setData('country_id', 'CA');
        }
    }


     /**
      * Return the associated address.
      *
      * @return \Webkul\MarketplaceBaseShipping\Model\ShippingSetting
      */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        if ($this->getAddress()) {
            $region = $this->getAddress()->getRegion();
            return $region;
        } else {
            return '';
        }
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegionId()
    {
        ///return '66';
        if ($this->getAddress()) {
            return $regionId = $this->getAddress()->getRegionId();
        } else {
            return '';
        }
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }
}
