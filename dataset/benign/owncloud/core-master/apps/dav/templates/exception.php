<?php
	/** @var array $_ */
	/** @var \OCP\IL10N $l */

style('core', ['styles', 'header']);
?>
<span class="error error-wide">
	<h2><strong><?php p($_['title']) ?></strong></h2>
	<?php if (isset($_['hint']) && $_['hint']): ?>
		<p class='hint'><?php p($_['hint']) ?></p>
	<?php endif;?>
	<br>

	<h2><strong><?php p($l->t('Technical details')) ?></strong></h2>
	<ul>
		<li><?php p($l->t('Remote Address: %s', $_['remoteAddr'])) ?></li>
		<li><?php p($l->t('Request ID: %s', $_['requestID'])) ?></li>
	</ul>
</span>
