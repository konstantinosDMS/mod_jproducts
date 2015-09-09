<?php

defined('JPATH_BASE') or die;

/**
 * This is our custom registration plugin class.  It validates/verifies the email address a user 
 * entered into the email field of Joomla's registration form.
 *
 * @package     Joomla.Plugins
 * @subpackage  User.MyRegistration
 * @since       1.5.0
 */

class plgContentUpdateProductHit extends JPlugin
{		
	/**
	 * Method to handle the "onContentPrepareForm" event.
	 * 	 *
	 * @return  bool
	 * 
	 * @since   1.5.0
	 */
	public function  onContentPrepareForm($form, $data)
	{            
                $option	= JRequest::getCmd('option');              
                $view = JRequest::getCmd('view');
                $virtueMartCategoryId = JRequest::getInt('virtuemart_category_id');
                $itemId = JRequest::getInt('Itemid');
                $virtueMartProductId = JRequest::getInt('virtuemart_product_id'); 
                // Get the dbo
	        $db = JFactory::getDbo(); 
                 // Initialize the query object
                $query = $db->getQuery(true);
                
                if ($option == 'com_virtuemart')
	        {
                        if (isset($view)&&isset($virtueMartCategoryId)&&isset($itemId)&&isset($virtueMartProductId)&&($view=='productdetails') ){                     
                        $query->update($db->QuoteName('#__virtuemart_products'));
                        $query->set('hits = hits + 1');
                        $query->set('modified_on=NOW()');
                        $query->where('#__virtuemart_products.virtuemart_product_id='.(int)$virtueMartProductId);
                        //echo $query;                       
                        //var_dump($query);                       
                        $db->setQuery($query);
                        //echo $db->getAffectedRows();
                        $db->execute();
                        if ($db->getErrorNum()) {
                            echo $db->getErrorMsg();
                            return false;
                        }
                        $query->clear();
                    }
                    else if (isset($view)&&isset($virtueMartCategoryId)&&isset($itemId)&&($view=='category') ){
                        $query->update($db->quoteName('#__virtuemart_products'));
                        $query->set('hits = hits + 1');
                        $query->set('modified_on=NOW()');
                        $query->where('#__virtuemart_products.virtuemart_product_id IN '.
                                '(SELECT #__virtuemart_product_categories.virtuemart_product_id '.
                                ' FROM #__virtuemart_product_categories WHERE virtuemart_category_id= '.(int)$virtueMartCategoryId.')');
              
                        //echo $query;                       
                        //var_dump($query);                       
                        $db->setQuery($query);
                        //echo $db->getAffectedRows();
                        $db->execute();
                        if ($db->getErrorNum()) {
                            echo $db->getErrorMsg();
                            return false;
                        }
                        $query->clear();                 
                    }
                }
                
		return true;
	}	
}
