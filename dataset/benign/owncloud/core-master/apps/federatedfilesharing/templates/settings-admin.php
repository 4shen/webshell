<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */
script('federatedfilesharing', 'settings-admin');
?>

<div id="fileSharingSettings" class="section">
	<h2 class="app-name has-documentation"><?php p($l->t('Federated Cloud Sharing'));?></h2>
	<a target="_blank" rel="noreferrer" class="icon-info"
		title="<?php p($l->t('Open documentation'));?>"
		href="<?php p(link_to_docs('admin-sharing-federated')); ?>"></a>

	<p>
		<input type="checkbox" name="cronjob_scan_external_enabled" id="cronjobScanExternalEnabled" class="checkbox"
			   value="1" <?php if ($_['cronjobScanExternalEnabled']) {
	print_unescaped('checked="checked"');
} ?> />
		<label for="cronjobScanExternalEnabled">
			<?php p($l->t('Periodically synchronize outdated federated shares for active users'));?>
		</label>
	</p>

	<p>
		<input type="checkbox" name="outgoing_server2server_share_enabled" id="outgoingServer2serverShareEnabled" class="checkbox"
			   value="1" <?php if ($_['outgoingServer2serverShareEnabled']) {
	print_unescaped('checked="checked"');
} ?> />
		<label for="outgoingServer2serverShareEnabled">
			<?php p($l->t('Allow users on this server to send shares to other servers'));?>
		</label>
	</p>

	<p>
		<input type="checkbox" name="incoming_server2server_share_enabled" id="incomingServer2serverShareEnabled" class="checkbox"
			   value="1" <?php if ($_['incomingServer2serverShareEnabled']) {
	print_unescaped('checked="checked"');
} ?> />
		<label for="incomingServer2serverShareEnabled">
			<?php p($l->t('Allow users on this server to receive shares from other servers'));?>
		</label><br/>
	</p>

	<p>
		<input type="checkbox" name="auto_accept_trusted" id="autoAcceptTrusted" class="checkbox"
			   value="1" <?php if ($_['autoAcceptTrusted']) {
	print_unescaped('checked="checked"');
} ?> />
		<label for="autoAcceptTrusted">
			<?php p($l->t('Automatically accept remote shares from trusted servers'));?>
		</label><br/>
	</p>
</div>
