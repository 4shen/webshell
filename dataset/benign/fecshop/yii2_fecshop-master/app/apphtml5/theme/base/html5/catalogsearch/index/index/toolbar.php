<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
?>
<?php   
	$query_item 	= $parentThis['query_item'];
	$product_page 	= $parentThis['product_page'];
?>
<div class="toolbar">
	<div class="tb_le">
		<?php  $frontNumPerPage = $query_item['frontNumPerPage']; ?>
		<?php if(is_array($frontNumPerPage) && !empty($frontNumPerPage)): ?>
			<b><?= Yii::$service->page->translate->__('Show Per Page:') ?></b>
			<select class="product_num_per_page">	
				<?php foreach($frontNumPerPage as $np):   ?>
					<?php $selected = $np['selected'] ? 'selected="selected"' : ''; ?>
					<?php $url 		= $np['url'];  ?>
					<option <?= $selected; ?> url="<?= $url; ?>" value="<?= $np['value']; ?>"><?= $np['value']; ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
	</div>
	<?= $product_page ?>
	<div class="clear"></div>
</div>
