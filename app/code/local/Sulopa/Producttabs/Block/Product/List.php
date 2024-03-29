<?php 
class Sulopa_Producttabs_Block_Product_List extends Mage_Catalog_Block_Product_List
{
  public function getTabCfg($cfg)
  {
    return Mage::helper('producttabs')->getTabCfg($cfg);
  }
  public function getProductCfg($cfg)
  {
    return Mage::helper('producttabs')->getProductCfg($cfg);
  }
  public function useFlatCatalogProduct()
  {
    return Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
  }
  public function getColumnCount()
  {
    return $this->getNumProduct();
  }
  public function getNumProduct()
  {
    return $this->getProductCfg('product_number');
  }
  public function getCategoryId()
  {
    $categoryId = (int) $this->getRequest()->getPost('category_id');
    return  $categoryId;
  } 
  protected function _getProductCollection()
  {
    $productType = $this->getRequest()->getParam('type');
    switch ($productType) {
      case 'bestseller':
        $Collection = $this->getBestsellerProducts();
        $this->_TitleProduct = $this->__('Bestseller');
        break;
      case 'featured':
        $Collection = $this->getFeaturedProducts();
        break;
      case 'mostviewed':
        $Collection = $this->getMostviewedProducts();
        break;
      case 'newproduct':
        $Collection = $this->getNewProducts();
        break;
      case 'random':
        $Collection = $this->getRandomProducts();
        break;
      case 'saleproduct':
        $Collection = $this->getSaleProducts();
        break;
      case 'specialproduct':
        $Collection = $this->getSpecialProducts();
        break;
      default:
        $Collection = $this->getFeaturedProducts();
        break;
    }
    return $Collection;
  }
    public function getBestsellerProducts(){
        $_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getResourceModel('producttabs/product_bestseller');
        $collection = $this->_addProductAttributesAndPrices($collection)
					->addOrderedQty()
                    ->addMinimalPrice()
                    ->setOrder('ordered_qty', 'desc');
		$collection->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
		->addAttributeToFilter('category_id', array('in' => $_rootcatID));
        $collection->setPageSize($this->getNumProduct()); // require before foreach
        if($this->useFlatCatalogProduct())
        {
            // fix error mat image vs name while Enable useFlatCatalogProduct
            foreach ($collection as $product) 
            {
                $productId = $product->_data['entity_id'];
                $_product = Mage::getModel('catalog/product')->load($productId); //Product ID
                $product->_data['name']        = $_product->getName();
                $product->_data['thumbnail']   = $_product->getThumbnail();
                $product->_data['small_image'] = $_product->getSmallImage();
            }            
        }
        return $collection;
    }
    public function getFeaturedProducts(){
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getModel('catalog/product')->getCollection()
							->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addAttributeToFilter('su_featured_product', 1, 'left')
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter()
							->addAttributeToFilter('category_id', array('in' => $_rootcatID));
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection; 
    }
    public function getMostviewedProducts(){
		//Magento get popular products by total number of views
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getResourceModel('reports/product_collection')
							->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addViewsCount()
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter()
							->addAttributeToFilter('category_id', array('in' => $_rootcatID)); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        // getNumProduct
        $collection->setPageSize($this->getNumProduct()); // require before foreach
        if($this->useFlatCatalogProduct())
        {
            // fix error mat image vs name while Enable useFlatCatalogProduct
            foreach ($collection as $product) 
            {
                $productId = $product->_data['entity_id'];
                $_product = Mage::getModel('catalog/product')->load($productId); //Product ID
                $product->_data['name']        = $_product->getName();
                $product->_data['thumbnail']   = $_product->getThumbnail();
                $product->_data['small_image'] = $_product->getSmallImage();
            }            
        }
        return $collection;
    }
    public function getNewProducts() {
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
							->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addAttributeToSelect('*') //Need this so products show up correctly in product listing
                            ->addAttributeToFilter('news_from_date', array('or'=> array(
                                0 => array('date' => true, 'to' => $todayDate),
                                1 => array('is' => new Zend_Db_Expr('null')))
                            ), 'left')
                            ->addAttributeToFilter('news_to_date', array('or'=> array(
                                0 => array('date' => true, 'from' => $todayDate),
                                1 => array('is' => new Zend_Db_Expr('null')))
                            ), 'left')
                            ->addAttributeToFilter(
                                array(
                                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                    )
                              )
                            ->addAttributeToSort('news_from_date', 'desc')
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter()
							->addAttributeToFilter('category_id', array('in' => $_rootcatID)); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }
    public function getRandomProducts() {
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $collection = Mage::getResourceModel('catalog/product_collection')
							->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter()
							->addAttributeToFilter('category_id', array('in' => $_rootcatID)); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        $collection->getSelect()->order('rand()');
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }
    public function getSaleProducts(){
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
								->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                                ->addAttributeToFilter('special_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter('special_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter(
                                    array(
                                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                        )
                                  )
                                ->addAttributeToSort('special_to_date','desc')
                                ->addTaxPercents()
                                ->addStoreFilter()
								->addAttributeToFilter('category_id', array('in' => $_rootcatID)); 
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);    
        // get Sale off
        foreach ($collection as $key => $product) {
            if($product->getSpecialPrice() == '') $collection->removeItemByKey($key); // remove product not set SpecialPrice
            if($product->getSpecialPrice() && $product->getSpecialPrice() >= $product->getPrice())
            {
               $collection->removeItemByKey($key); // remove product price increase
            }
        }
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }
    public function getSpecialProducts() {
		$_rootcatID = Mage::app()->getStore()->getRootCategoryId();
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
								->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
                                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                                ->addAttributeToFilter('special_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter('special_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter(
                                    array(
                                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                        )
                                  )
                                ->addAttributeToSort('special_to_date','desc')
                                ->addTaxPercents()
                                ->addStoreFilter()
								->addAttributeToFilter('category_id', array('in' => $_rootcatID)); 
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);    
        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }
}