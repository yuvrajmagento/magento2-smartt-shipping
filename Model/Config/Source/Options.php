<?php
namespace Smarttshipping\Shipping\Model\Config\Source;
 
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
  
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /*CONST OPT_BOXES = 11219;
    CONST OPT_BUNDLES = 11220;
    CONST OPT_CARTONS = 11221;
    CONST OPT_CRATES = 11222;
    CONST OPT_ENVELOPE = 4099;
    CONST OPT_FULL = 11226;
    CONST OPT_PALLETS = 11223;
    CONST OPT_PIECES = 11224;
    CONST OPT_PUROLATO_EXPRESS_ENVELOPE = 9207;
    CONST OPT_PUROLATO_EXPRESS_PACK = 9206;
    CONST OPT_SKIDS = 11225;*/

    /*For sandbox*/
    const OPT_BOXES = 6179;
    const OPT_BUNDLES = 6180;
    const OPT_CARTONS = 6181;
    const OPT_CRATES = 6182;
    const OPT_ENVELOPE = 4099;
    const OPT_FULL = 6186;
    const OPT_PALLETS = 6183;
    const OPT_PIECES = 6184;
    const OPT_PUROLATO_EXPRESS_ENVELOPE = 6164;
    const OPT_PUROLATO_EXPRESS_PACK = 6163;
    const OPT_SKIDS = 6185;
    
    protected $_smartapi;
    
    public function __construct(
        \Smarttshipping\Shipping\Model\Api\Smartapi $smartapi
    ) {
        $this->_smartapi = $smartapi;
    }

    public function getAllOptions()
    {
        $packData[] =  ['label'=>'Select Options', 'value'=>''];

        // $apiKey = $this->helper->getApikey();
        // $apiUrl = $this->helper->getApiBaseUrl();

        // if(!empty($apiKey) && empty(!$apiUrl)){
        //     $packages = $this->getStaticData();
        //     if(is_array($packages))
        //     {
        //         foreach ($packages as $key => $value) {
        //             $packData[] = ['label'=>$value, 'value'=>$key];
        //         }
        //     }
        // }

        
        $packages = $this->_smartapi->getAllPackages();
        if (is_array($packages)) {
            foreach ($packages['Packages'] as $key => $value) {
                $packData[] = ['label'=>$value['PackageTypeName'], 'value'=>$value['PackageID']];
            }
        }
       

        
        $this->_options = $packData;
        return $this->_options;
    }
  
    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    public function getStaticData()
    {
        $packData = [
                    '85' => 'BOXES',
                    '6180' => 'BUNDLES',
                    '6181' => 'CARTONS',
                    '6208' => 'CRATES',
                    '4099' => 'ENVELOPE',
                    '7234' => 'FULL LOAD',
                    '6206' => 'Pallets',
                    '6184' => 'PIECES',
                    '6164' => 'Purolator Express Envelope',
                    '6163' => 'Purolator Express Pack',
                    '6207' => 'SKIDS',
                ];
        return $packData;
    }
}
