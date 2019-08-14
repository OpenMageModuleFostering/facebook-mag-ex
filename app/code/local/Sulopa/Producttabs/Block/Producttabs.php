<?php 
class Sulopa_Producttabs_Block_Producttabs extends Mage_Core_Block_Template
{
    public function getProducttabsCfg($cfg)
    {
        return Mage::helper('producttabs')->getProducttabsCfg($cfg);
    }
    public function getProductCfg($cfg)
    {
        return Mage::helper('producttabs')->getProductCfg($cfg);
    }
    public function getTypeDefault()
    {
        $cfg   = $this->getProductCfg('product_type');
        $cfg   = explode(',', $cfg);
        return $cfg[0];
    }
    public function sortTabs()
    {
        return $this->getProducttabsCfg('sort_name');
    }
	public function getTabs()
	{
        $types = Mage::getSingleton("producttabs/system_config_type")->toOptionArray();
        $cfg = $this->getProductCfg('product_type');
        $cfg = explode(',', $cfg);
        $tabs = array();
        foreach ($types as $type) {
            if(in_array($type['value'], $cfg)){
                $tabs[$type['value']] = $type['label'];
            }
        }
        return $tabs;
	}
    public function setBxslider()
    {
        $auto           = (int) $this->getProducttabsCfg('auto');
        $speed          = (int) $this->getProducttabsCfg('speed');
        $video          = (int) $this->getProducttabsCfg('video');
        $pager          = (int) $this->getProducttabsCfg('pager');
        $vertical       = (int) $this->getProducttabsCfg('vertical');
        $minSlides      = (int) $this->getProducttabsCfg('minslides');
        $maxSlides      = (int) $this->getProducttabsCfg('maxslides');
        $moveSlides     = (int) $this->getProducttabsCfg('moveslides');
        $startSlide     = (int) $this->getProducttabsCfg('startslide');
        $slideWidth     = (int) $this->getProducttabsCfg('slidewidth');
        $slideMargin    = (int) $this->getProducttabsCfg('slidemargin');
		$script =  'infiniteLoop:false,';
        if($auto)           $script    .= "auto: $auto,";
        if($speed)          $script    .= "speed: $speed,";
        if(!$pager)          $script    .= "pager: $pager,";
        if($vertical)       $script    .= "mode: 'vertical',";
        if($minSlides)      $script    .= "minSlides: $minSlides,";
        if($maxSlides)      $script    .= "maxSlides: $maxSlides,";
        if($moveSlides)     $script    .= "moveSlides: $moveSlides,";
        if($startSlide)     $script    .= "startSlide: $startSlide,";
        if($slideWidth)     $script    .= "slideWidth: $slideWidth,";
        if($slideMargin)    $script    .= "slideMargin: $slideMargin,";
        return $script;
    }
}