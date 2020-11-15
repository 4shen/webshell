<?php

namespace MediaWiki\Hook;

use MediaWiki\Block\AbstractBlock;
use User;

/**
 * @stable for implementation
 * @ingroup Hooks
 */
interface BlockIpCompleteHook {
	/**
	 * This hook is called after an IP address or user is blocked.
	 *
	 * @since 1.35
	 *
	 * @param AbstractBlock $block the Block object that was saved
	 * @param User $user the user who did the block (not the one being blocked)
	 * @param ?AbstractBlock $priorBlock the Block object for the prior block, if there was one
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onBlockIpComplete( $block, $user, $priorBlock );
}
