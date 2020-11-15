<?php

namespace MediaWiki\ResourceLoader\Hook;

use ResourceLoaderContext;

/**
 * @stable for implementation
 * @ingroup ResourceLoaderHooks
 */
interface ResourceLoaderJqueryMsgModuleMagicWordsHook {
	/**
	 * Add magic words to the `mediawiki.jqueryMsg` module. The values should be a string,
	 * and they may only vary by what's in the ResourceLoaderContext.
	 *
	 * This hook is called from ResourceLoaderJqueryMsgModule.
	 *
	 * @since 1.35
	 * @param ResourceLoaderContext $context
	 * @param string[] &$magicWords Associative array mapping all-caps magic word to a string value
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onResourceLoaderJqueryMsgModuleMagicWords(
		ResourceLoaderContext $context,
		array &$magicWords
	);
}
