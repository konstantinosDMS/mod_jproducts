<?php
/**
 * @copyright	Copyright (C) 2011 Mark Dexter & Louis Landry. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Output as a list of links in a ul element
?>

        
<ul class="jproducts<?php echo $moduleclass_sfx; ?>">
<?php if (!is_array($list[0])) : ?>
<?php foreach ($list as $item) :  ?>
	<li>
            <img src="<?php echo $item->file_url; ?>" alt="<?php echo $item->productName; ?>" width="50" height="50" />
		<a href="<?php echo $item->link; ?>">
			<?php echo $item->productName; ?></a><?php //echo $item->productQnt; ?>
	</li>
<?php endforeach; ?>
<?php endif; ?>
<?php if (is_array($list[0])){
        $flag=false;
        $tmpOutput='';
        $outputString='';
        
        foreach ($list as $item){
          if (!$flag){
	    
               $outputString .= 'The customers who bought ';
               for ($i=0;$i<count($item);$i++){
                   if ($i!=count($item)-1) $tmpOutput.=$item[$i]->productName.' and ';
                   else $tmpOutput.=$item[$i]->productName;
               }
               $outputString.=$tmpOutput;
               $tmpOutput='';
               $flag=true;           
          }
          else{
            $outputString.=' also bought ';
            for ($i=0;$i<count($item);$i++){
                   if ($i!=count($item)-1) $tmpOutput.=$item[$i]->productName.' and ';
                   else $tmpOutput.=$item[$i]->productName;
               }
               $outputString.=$tmpOutput.'<br />';
               $tmpOutput='';
               $flag=false;
          }  
        }
}
echo '<b>'.$outputString.'</b>';
?>
          



</ul>