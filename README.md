# mod_jproducts
This is a module for virtuemart which performs several tasks.
# mod_jProducts.
A module for virtuemart 3.0.9 - joomla 2.5.28 stable version which performs several tasks such as display 
the most viewed,buyed products, the recently viewed,buyed products and final performs association rules on products. 
To run you have to install and activate the plugin first and then give the following instructions on mysql 
client: ALTER TABLE #__virtuemart_products ALTER COLUMN hits SET DEFAULT 0; UPDATE #__virtuemart_products SET hits=0; 
where '#_'  is virtuemart's default name - db installation, and also type the following code : 
JPluginHelper::importPlugin('content'); JFactory::getApplication()->triggerEvent('onContentPrepareForm',array('','')); 
on com_virtuemart/virtuemart.php at the begining of the file (before other plugins are called). Then install the module.
If module's installation failed, install module without weka.jar and then install weka.jar manually under mod_jProducts.  
