<?php
/**
 * @copyright	Copyright (C) 2011 Mark Dexter and Louis Landry. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE.'/components/com_content/helpers/route.php');

abstract class modJProductsHelper
{
	public static function getList(&$params)
	{
		$option	= JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
                $virtueMartCategoryId = JRequest::getInt('virtuemart_category_id');
                $itemId = JRequest::getInt('Itemid');
                $virtueMartProductId = JRequest::getInt('virtuemart_product_id');
                $productArray = array();
             
		if (($option == 'com_virtuemart')||($option == 'com_content'))
		{
                   
                    if ($option == 'com_content'){
                        $option = 'com_virtuemart';
                        $view = 'virtuemart';
                    }    
                    
			// Get the dbo
			$db = JFactory::getDbo();
			                       
                        // Initialize the query object
                        $query = $db->getQuery(true);
                        $query->select('id');
                        $query->from('#__menu AS menu');
                        $query->leftJoin('#__extensions AS ext ON ext.extension_id=menu.component_id');
                        $query->where(' LOWER(ext.name) like LOWER(\'%'.substr($db->Quote($option),5,strlen($db->Quote($option))-6).'%\')');
                        $query->where('menu.title LIKE \'%Shop%\'');
                        
                            
                        $db->setQuery($query);
                        
                        $itemId = $db->loadObject()->id;
                        
                        $query->clear();                        
                       
                        switch (htmlspecialchars($params->get('products_by_retrieve'))){
                            case 'most.buy':                         
                            $productArray=array();
                                
                            // Query the database to get ten products with the biggest quantity field.
                            //not included in the order_items table in defferent orders.
                            $query->select('s3.virtuemart_product_id AS sameProducts,'
                                    . 's3.product_quantity AS productQnt,'
                                    . 's3.order_item_name AS productName,'
                                    . 'vm.file_url,'
                                    . 'vcm.virtuemart_category_id AS categoryId');
                            $query->from('#__virtuemart_order_items AS s3');
                            $query->leftJoin('#__virtuemart_products  AS vp ON vp.virtuemart_product_id = s3.virtuemart_product_id');
                            $query->leftJoin('#__virtuemart_product_categories AS vpc  ON vpc.virtuemart_product_id = vp.virtuemart_product_id');
                            $query->leftJoin('#__virtuemart_category_medias AS vcm ON vcm.virtuemart_category_id = vpc.virtuemart_category_id');
                            $query->leftJoin('#__virtuemart_medias  AS vm ON vm.virtuemart_media_id=vcm.virtuemart_media_id');                            
                            $query->where('s3.virtuemart_product_id NOT IN '
                                    . '(SELECT  s1.virtuemart_product_id   FROM '
                                    . ' #__virtuemart_order_items AS s1 ,'
                                    . ' #__virtuemart_order_items AS s2 WHERE '
                                    . ' s1.virtuemart_product_id=s2.virtuemart_product_id AND '
                                    . ' s1.virtuemart_order_id!=s2.virtuemart_order_id)');
                            $query->group('productQnt');
                            $query->order('productQnt DESC');
                            
                            //Set Query
                            $db->setQuery($query,0,10);
                            $products=array();
                           
                            if ($db->getErrorNum()) {
                               echo $db->getErrorMsg();
                               return false;
                            }
                            
                            $products = $db->loadObjectList();              
                            
                            if (count($products)==0){
                                echo 'No products have been purchased';
                                return false;
                            }
                            
                            // Create the link field for each product using the content router class
                            foreach ($products as &$product) {
                               $product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->sameProducts.'&virtuemart_category_id='.$product->categoryId.'&ItemId='.$itemId);
                               $productArray[]=$product;
                            }    
                                
                            $query->clear();
                                                                              
                            // Query the database to get the same products purchased in defferent orders 
                            $query->select('s1.virtuemart_product_id AS sameProducts'
                                    . ',s1.product_quantity AS productQnt'
                                    . ',s1.order_item_name AS productName'
                                    . ',vm.file_url'
                                    . ',vcm.virtuemart_category_id AS categoryId');
                            $query->from('#__virtuemart_order_items AS s1 ');
                            $query->leftJoin('#__virtuemart_order_items AS s2 ON s1.virtuemart_product_id=s2.virtuemart_product_id');
                            $query->leftJoin('#__virtuemart_products  AS vp ON vp.virtuemart_product_id = s1.virtuemart_product_id');
                            $query->leftJoin('#__virtuemart_product_categories AS vpc  ON vpc.virtuemart_product_id = vp.virtuemart_product_id');
                            $query->leftJoin('#__virtuemart_category_medias AS vcm ON vcm.virtuemart_category_id = vpc.virtuemart_category_id');
                            $query->leftJoin('#__virtuemart_medias  AS vm ON vm.virtuemart_media_id=vcm.virtuemart_media_id');                  
                            $query->where('s1.virtuemart_order_id!=s2.virtuemart_order_id');
                            
                            //Set Query
                            $db->setQuery($query);
                            $products=array();       
                            
                            if ($db->getErrorNum()) {
                               echo $db->getErrorMsg();
                               return false;
                            }
                            
                            // Get list of rows
                            $products = $db->loadObjectList();                    
                            
                            if (count($products)==0){
                                echo 'No products have been purchased';
                                return false;
                            }
                            
                            // Create the link field for each product using the content router class
                            foreach ($products as &$product) {
                               $product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->sameProducts.'&virtuemart_category_id='.$product->categoryId.'&ItemId='.$itemId);
                               $productArray[]=$product;
                            }
                            
                            //Add the Quantity field of the same products.
                            for ($i=0;$i<count($productArray);$i++){
                                for ($j=0;$j<count($productArray);$j++){
                                    if (($productArray[$i]->productName==$productArray[$j]->productName) && ($i!=$j)){
                                        $productArray[$i]->productQnt+=$productArray[$j]->productQnt;
                                        unset($productArray[$j]);
                                        $productArray = array_reverse($productArray);
                                        $productArray =  array_reverse($productArray);
                                    }
                                }
                            }
                            
                            usort($productArray,'self::compare');               
                            //var_dump($productArray);                                            
                            break;
                      
                            default:
                            case 'rec.buy':
                                $query->clear();
                                $productArray=array();
                                
                                // Query the database to get ten products with the biggest quantity field.
                                //not included in the order_items table in defferent orders.
                                $query->select('s3.virtuemart_product_id AS sameProducts'
                                    . ',s3.product_quantity AS productQnt'
                                    . ',s3.order_item_name AS productName'
                                    . ',s3.created_on AS dateOrder'
                                    . ',vm.file_url'
                                    . ',vcm.virtuemart_category_id AS categoryId');
                                $query->from('#__virtuemart_order_items AS s3');
                                $query->leftJoin('#__virtuemart_products  AS vp ON vp.virtuemart_product_id = s3.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_product_categories AS vpc  ON vpc.virtuemart_product_id = vp.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_category_medias AS vcm ON vcm.virtuemart_category_id = vpc.virtuemart_category_id');
                                $query->leftJoin('#__virtuemart_medias  AS vm ON vm.virtuemart_media_id=vcm.virtuemart_media_id');                   
                                $query->group('dateOrder');
                                $query->order('dateOrder DESC');

                                //Set Query
                                $db->setQuery($query,0,10);
                                $products=array();
                                
                                if ($db->getErrorNum()) {
                                  echo $db->getErrorMsg();
                                  return false;
                                }
                                
                                // Get list of rows
                                $products = $db->loadObjectList();                                
                               
                                
                                if (count($products)==0){
                                   echo 'No products have been purchased';
                                   return false;
                                }
                            
                                 // Create the link field for each product using the content router class
                                foreach ($products as &$product) {
                                   $product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->sameProducts.'&virtuemart_category_id='.$product->categoryId.'&ItemId='.$itemId);
                                   $productArray[]=$product;
                                }
                                break;
                            
                            case 'most.view':
                                $query->clear();
                                $productArray=array();
                                
                                // Query the database to get ten products with the biggest hits field.
                                
                                $query->select('s3.virtuemart_product_id AS sameProducts'
                                    . ',s3.product_quantity AS productQnt'
                                    . ',s3.order_item_name AS productName'
                                    . ',s3.created_on AS dateOrder'
                                    . ',vm.file_url'
                                    . ',vcm.virtuemart_category_id AS categoryId');
                                $query->from('#__virtuemart_order_items AS s3');
                                $query->leftJoin('#__virtuemart_products  AS vp ON vp.virtuemart_product_id = s3.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_product_categories AS vpc  ON vpc.virtuemart_product_id = vp.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_category_medias AS vcm ON vcm.virtuemart_category_id = vpc.virtuemart_category_id');
                                $query->leftJoin('#__virtuemart_medias  AS vm ON vm.virtuemart_media_id=vcm.virtuemart_media_id');                   
                                $query->group('vp.hits');
                                $query->order('vp.hits DESC');

                                //Set Query
                                $db->setQuery($query,0,10);
                                $products=array();
                                
                                if ($db->getErrorNum()) {
                                  echo $db->getErrorMsg();
                                  return false;
                                }
                                
                                // Get list of rows
                                $products = $db->loadObjectList();                               
                                
                                if (count($products)==0){
                                   echo 'No products have been purchased';
                                   return false;
                                }
                            
                                 // Create the link field for each product using the content router class
                                foreach ($products as &$product) {
                                   $product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->sameProducts.'&virtuemart_category_id='.$product->categoryId.'&ItemId='.$itemId);
                                   $productArray[]=$product;
                                }
                                break;
                            
                            case 'rec.view':
                                $query->clear();
                                $productArray=array();
                                
                                // Query the database to get ten products with the recently modified_on field.
                                
                                $query->select('s3.virtuemart_product_id AS sameProducts'
                                    . ',s3.product_quantity AS productQnt'
                                    . ',s3.order_item_name AS productName'
                                    . ',s3.created_on AS dateOrder'
                                    . ',vm.file_url'
                                    . ',vcm.virtuemart_category_id AS categoryId');
                                $query->from('#__virtuemart_order_items AS s3');
                                $query->leftJoin('#__virtuemart_products  AS vp ON vp.virtuemart_product_id = s3.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_product_categories AS vpc  ON vpc.virtuemart_product_id = vp.virtuemart_product_id');
                                $query->leftJoin('#__virtuemart_category_medias AS vcm ON vcm.virtuemart_category_id = vpc.virtuemart_category_id');
                                $query->leftJoin('#__virtuemart_medias  AS vm ON vm.virtuemart_media_id=vcm.virtuemart_media_id');                   
                                $query->group('vp.modified_on');
                                $query->order('vp.modified_on DESC');

                                //Set Query
                                $db->setQuery($query,0,10);
                                $products=array();
                                
                                if ($db->getErrorNum()) {
                                  echo $db->getErrorMsg();
                                  return false;
                                }
                                
                                // Get list of rows
                                $products = $db->loadObjectList();                                
                                
                                if (count($products)==0){
                                   echo 'No products have been purchased';
                                   return false;
                                }
                            
                                 // Create the link field for each product using the content router class
                                foreach ($products as &$product) {
                                   $product->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->sameProducts.'&virtuemart_category_id='.$product->categoryId.'&ItemId='.$itemId);
                                   $productArray[]=$product;
                                }
                                break;
                            case 'assoc.rules':
                                $flag=false;
                                $query->clear();
                                $productArray=array();
                                $products=array();
                                $orders=array();
                                $orderItems=array();
                                $tmpProductArray=array();
                                                                                           
                                $query->select('vp.*');
                                $query->from('#__virtuemart_products AS vp');
                                
                                //Set Query
                                $db->setQuery($query);
                                
                                if ($db->getErrorNum()) {
                                  echo $db->getErrorMsg();
                                  return false;
                                }
                                
                                $products = $db->loadObjectList();                               
                                
                                if (count($products)==0){
                                   echo 'No products have been purchased';
                                   return false;
                                }
                            
                                foreach ($products as &$product) {
                                   $productArray[]=$product;
                                }             
                                
                                $query->clear();
                                $query->select('ord.*');
                                $query->from('#__virtuemart_orders AS ord');
                                
                                //Set Query
                                $db->setQuery($query);
                                
                                if ($db->getErrorNum()) {
                                  echo $db->getErrorMsg();
                                  return false;
                                }
                                
                                $orders = $db->loadObjectList(); 
                                
                                if (count($orders)==0){
                                   echo 'No orders have been found';
                                   return false;
                                }
                            
                                for ($i=0;$i<count($orders);$i++){
                                    
                                    $query->clear();
                                    $query->select('oi.*');
                                    $query->from('#__virtuemart_order_items AS oi');
                                    $query->where('oi.virtuemart_order_id = '.$orders[$i]->virtuemart_order_id);
                                    
                                    //Set Query
                                    $db->setQuery($query);

                                    if ($db->getErrorNum()) {
                                      echo $db->getErrorMsg();
                                      return false;
                                    }

                                    $orderItems = $db->loadObjectList(); 
                                    
                                    if (count($orderItems)==0){
                                        echo 'No products have been purchased';
                                        return false;
                                    }
                            
                                    foreach($orderItems as $order){
                                        $tmpOrdersArray[]=$order;
                                    }
                                    
                                    $ordersArray[]=$tmpOrdersArray;
                                    $tmpOrdersArray=array();
                                    $orderItems=array();
                                }            
                                
                                if (JFile::exists(JPATH_ROOT.'/modules/mod_jproducts/jProducts.arff')) JFile::delete(JPATH_ROOT.'/modules/mod_jproducts/jProducts.arff');
                               
                                $outputstring ='@relation jProducts'."\n";
                                
                                for ($i=0;$i<count($productArray);$i++){
                                    $outputstring.='@attribute ' . $productArray[$i]->virtuemart_product_id  . ' { t}'."\n";
                                }
         
                                $outputstring.='@data' . "\n";
                               
                                foreach ($ordersArray as $orderArray){
                                    $stringLength=strlen($outputstring);                          
                                    for ($i=0;$i<count($orderArray);$i++){                           
                                        for ($j=0;$j<count($productArray);$j++){
                                            if ($orderArray[$i]->virtuemart_product_id==$productArray[$j]->virtuemart_product_id){
                                                $finalArray[]=$j;
                                            }
                                        }
                                    }                                   
                                    //var_dump($finalArray);                           
                                    for ($k=0;$k<count($productArray);$k++){
                                        for ($l=0;$l<count($finalArray);$l++){
                                            if ($k==$finalArray[$l]){
                                                $outputstring.='t,';
                                                $flag=true;
                                                break;                            
                                            }                         
                                        }
                                        if (!$flag) $outputstring.= '?,';
                                        $flag=false;
                                    }                          
                                    $outputstring=substr($outputstring,0,-1)."\n";                         
                                    $finalArray=array();          
                                }   
                                
                                JFile::write(JPATH_ROOT.'/modules/mod_jproducts/jProducts.arff', $outputstring);
                                if (JFile::exists(JPATH_ROOT.'/modules/mod_jproducts/outjProducts.arff')) JFile::delete(JPATH_ROOT.'/modules/mod_jproducts/outjProducts.arff'); 
                                exec('java -cp '.JPATH_ROOT.'/modules/mod_jproducts/weka.jar weka.associations.Apriori -N 10 -T 0 -C 0.9 -D 0.05 -U 1.0 -M 0.1 -S -1.0 -t '.JPATH_ROOT.'/modules/mod_jproducts/jproducts.arff > '.JPATH_ROOT.'/modules/mod_jproducts/outjProducts.arff');
                                $readFile = JFile::read(JPATH_ROOT.'/modules/mod_jproducts/outjproducts.arff',false,filesize(JPATH_ROOT.'/modules/mod_jproducts/outjproducts.arff'));                                
                                
                                if ($readFile){ 
                                    $indx=strpos($readFile,'Minimum support:');                            
                                    $minSupport = substr($readFile,$indx+16,5);                  
                                    $indx=strpos($readFile,'Minimum metric <confidence>:');
                                    $minConfidence = substr($readFile,$indx+28,5);                               
                                
                                for ($i=1;$i<=10;$i++){
                                    $indx1 = strpos($readFile,$i.'.');
                                    
                                    if ($i!=10) {
                                        $indx2 = strpos($readFile,($i+1).'.');
                                        $rules1=substr($readFile,$indx1+2,($indx2-$indx1-2)); 
                                    }
                                    else{
                                        $indx2=strpos($readFile,'=== Evaluation ===');
                                        $rules1=substr($readFile,$indx1+3,($indx2-$indx1-3)); 
                                    }                                                                  
                                    $rules[]=$rules1;
                                } 
                                
                                for ($i=0;$i<count($rules);$i++){
                                    $token1=strpos($rules[$i],'==>');
                                    $token[]=substr($rules[$i],0,$token1);
                                    $token[]=substr($rules[$i],$token1+3);
                                }
                                
                                for ($i=0;$i<count($token);$i++){
                                    $subToken=strtok($token[$i],' ');
                                    $indx3=strpos($subToken,'=');                           
                                    if ($indx3!='') {
                                        $prods[]=substr($subToken,0,strpos($subToken,'='));
                                    }                           
                                    while($subToken!=''){
                                        $subToken=strtok(' ');
                                        $indx3=strpos($subToken,'=');
                                        
                                        if ($indx3!='') {
                                            $prods[]=substr($subToken,0,strpos($subToken,'='));
                                        }
                                    }          
                                    $finalProds[]=$prods;
                                    $prods=array();
                                } 
                                
                                $productArray=array();      
                                for ($i=0;$i<count($finalProds);$i++){
                                     for ($j=0;$j<count($finalProds[$i]);$j++){
                                                $query->clear();                            
                            
                                                // Query the database to get the products with the specified product id.

                                                $query->select('DISTINCT s3.virtuemart_product_id AS sameProducts'
                                                            . ',s3.order_item_name AS productName');
                                                $query->from('#__virtuemart_order_items AS s3');
                                                $query->where('s3.virtuemart_product_id='.(int)$finalProds[$i][$j]);
                                                //echo $query.'<br />'.'<br />';
                            
                                                //Set Query
                                                $db->setQuery($query,0,1);
                                                
                                                $products=array();
                                                
                                                if ($db->getErrorNum()) {
                                                  echo $db->getErrorMsg();
                                                  return false;
                                                }                                               
                                                // Get list of rows
                                                $products = $db->loadObjectList();                                
                                                
                                                if (count($products)==0){
                                                    echo 'No products have been purchased';
                                                    return false;
                                                }
                                                
                                                 // Create the link field for each product using the content router class
                                                foreach ($products as &$product) {
                                                   $tmpProductArray[]=$product;
                                                }
                                    }                           
                                     $productArray[]=$tmpProductArray;
                                     $tmpProductArray=array();
                                 }   
                                }                                
                                break;
                        }                        
		}
		return $productArray;                
	}
        
        public function compare($product1,$product2){         
            if ($product1->productQnt == $product2->productQnt) {
                 return 0;
            }
             return ($product1->productQnt < $product2->productQnt) ? 1 : -1;
        }
}
