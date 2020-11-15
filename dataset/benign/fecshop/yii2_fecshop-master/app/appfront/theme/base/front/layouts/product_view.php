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
$jsOptions = [
	# js config 1
	[
		'options' => [
			'position' =>  'POS_END',
		],
		'js'	=>[
			'js/jquery-3.0.0.min.js',
			'js/jquery.lazyload.min.js',
			'js/owl.carousel.min.js',     
			'elevatezoom/jquery.elevatezoom.js',
			'fancybox/jquery.fancybox.pack.js',
			'js/js.js',
		],
	],
];

# css config
$cssOptions = [
	# css config 1.
	[
		'css'	=>[
			'css/style.css',
			'css/owl.carousel.css',
			'fancybox/jquery.fancybox.css',
		],
	],
];
\Yii::$service->page->asset->jsOptions 	= \yii\helpers\ArrayHelper::merge($jsOptions, \Yii::$service->page->asset->jsOptions);
\Yii::$service->page->asset->cssOptions = \yii\helpers\ArrayHelper::merge($cssOptions, \Yii::$service->page->asset->cssOptions);				
\Yii::$service->page->asset->register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $currentLangCode = Yii::$service->store->currentLangCode; ?>" lang="<?= $currentLangCode ?>">
<head>
<?= Yii::$service->page->widget->render('base/head',$this); ?>
</head>
<body>
<?= Yii::$service->page->widget->render('base/beforeContent',$this); ?>
<?php $this->beginBody() ?>
	<header id="header">
		<?= Yii::$service->page->widget->render('base/header',$this); ?>
		<?= Yii::$service->page->widget->render('base/menu',$this); ?>
	</header>
	
	<div class="main-container">
		<?= $content; ?>
	</div>
	<div class="footer-container">
		<?= Yii::$service->page->widget->render('base/footer',$this); ?>
	</div>
    <?= Yii::$service->page->widget->render('base/trace',$this); ?>
	<?= Yii::$service->page->widget->render('base/scroll',$this); ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

