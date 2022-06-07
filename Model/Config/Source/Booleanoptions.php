<?php
namespace Smarttshipping\Shipping\Model\Config\Source;
 
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
  
class Booleanoptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['label'=>'Select Option', 'value'=>''],
            ['label'=>'Yes', 'value'=>'1'],
            ['label'=>'No', 'value'=>'0']
        ];
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
}
