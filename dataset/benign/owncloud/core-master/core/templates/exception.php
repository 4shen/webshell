<?php
	/** @var array $_ */
	/** @var \OCP\IL10N $l */

style('core', ['styles', 'header']);
?>
<span class="error error-wide">
	<h2><strong><?php p($l->t('Internal Server Error')) ?></strong></h2>
		<p><?php p($l->t('The server encountered an internal error and was unable to complete your request.')) ?></p>
		<p><?php p($l->t('Please contact the server administrator if this error reappears multiple times and include the technical details below in your report.')) ?></p>
		<p><?php print_unescaped($l->t('More details can be found in the <a target="_blank" rel="noreferrer" href="%s">server log</a>.', [link_to_docs('admin-logfiles')])); ?></p>
	<br>

	<h2><strong><?php p($l->t('Technical details')) ?></strong></h2>
	<ul>
		<li><?php p($l->t('Remote Address: %s', $_['remoteAddr'])) ?></li>
		<li><?php p($l->t('Request ID: %s', $_['requestID'])) ?></li>
		<?php if ($_['debugMode']): ?>
			<li><?php p($l->t('Type: %s', $_['errorClass'])) ?></li>
			<li><?php p($l->t('Code: %s', $_['errorCode'])) ?></li>
			<li><?php p($l->t('Message: %s', $_['errorMsg'])) ?></li>
			<li><?php p($l->t('File: %s', $_['file'])) ?></li>
			<li><?php p($l->t('Line: %s', $_['line'])) ?></li>
		<?php endif; ?>
	</ul>

	<?php if ($_['debugMode']): ?>
		<br />
		<h2><strong><?php p($l->t('Trace')) ?></strong></h2>
		<pre><?php p($_['trace']) ?></pre>
	<?php endif; ?>
</span>
