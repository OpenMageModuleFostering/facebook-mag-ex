<?php
class Sulopa_Producttabs_Model_System_Config_Type
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'bestseller', 'label'=>Mage::helper('adminhtml')->__('Bestseller')),
            array('value' => 'featured', 'label'=>Mage::helper('adminhtml')->__('Featured Products')),
            array('value' => 'mostviewed', 'label'=>Mage::helper('adminhtml')->__('Most Viewed')),
            array('value' => 'newproduct', 'label'=>Mage::helper('adminhtml')->__('New Products')),
            array('value' => 'random', 'label'=>Mage::helper('adminhtml')->__('Random Products')),
            array('value' => 'saleproduct', 'label'=>Mage::helper('adminhtml')->__('Sale Products')),
        );
    }
}