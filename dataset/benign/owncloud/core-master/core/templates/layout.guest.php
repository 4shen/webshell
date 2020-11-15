<!DOCTYPE html>
<html class="ng-csp" data-placeholder-focus="false" lang="<?php p($_['language']); ?>" >
	<head data-requesttoken="<?php p($_['requesttoken']); ?>">
		<meta charset="utf-8">
		<title>
		<?php p($theme->getTitle()); ?>
		</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="referrer" content="never">
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
		<?php if ($theme->getiTunesAppId() !== '') {
	?>
			<meta name="apple-itunes-app" content="app-id=<?php p($theme->getiTunesAppId()); ?>">
		<?php
} ?>
		<meta name="theme-color" content="<?php p($theme->getMailHeaderColor()); ?>">
		<link rel="icon" href="<?php print_unescaped(image_path('', 'favicon.ico')); /* IE11+ supports png */ ?>">
		<link rel="apple-touch-icon-precomposed" href="<?php print_unescaped(image_path('', 'favicon-touch.png')); ?>">
		<link rel="mask-icon" sizes="any" href="<?php print_unescaped(image_path('', 'favicon-mask.svg')); ?>" color="#1d2d44">
		<?php foreach ($_['cssfiles'] as $cssfile): ?>
			<link rel="stylesheet" href="<?php print_unescaped($cssfile); ?>">
		<?php endforeach; ?>
		<?php foreach ($_['printcssfiles'] as $cssfile): ?>
			<link rel="stylesheet" href="<?php print_unescaped($cssfile); ?>" media="print">
		<?php endforeach; ?>
		<?php foreach ($_['jsfiles'] as $jsfile): ?>
			<script src="<?php print_unescaped($jsfile); ?>"></script>
		<?php endforeach; ?>
		<?php print_unescaped($_['headers']); ?>
	</head>
	<body id="<?php p($_['bodyid']);?>">
		<?php include('layout.noscript.warning.php'); ?>
		<div class="wrapper">
			<div class="v-align">
				<?php if ($_['bodyid'] === 'body-login'): ?>
					<header role="banner">
						<div id="header">
							<div class="logo">
								<h1 class="hidden-visually">
									<?php print_unescaped($theme->getHTMLName()); ?>
								</h1>
							</div>
							<div id="logo-claim" style="display:none;"><?php print_unescaped($theme->getLogoClaim()); ?></div>
						</div>
					</header>
				<?php endif; ?>
				<?php print_unescaped($_['content']); ?>
				<div class="push"></div><!-- for sticky footer -->
			</div>
		</div>
		<footer role="contentinfo">
			<p class="info">
				<?php print_unescaped($theme->getLongFooter()); ?>
			</p>
		</footer>
	</body>
</html>
