<?php

namespace MediaWiki\ResourceLoader\Hook;

use ResourceLoaderContext;

/**
 * @stable for implementation
 * @ingroup ResourceLoaderHooks
 */
interface ResourceLoaderForeignApiModulesHook {
	/**
	 * Add dependencies to the `mediawiki.ForeignApi` module when you wish
	 * to override its behavior. See the JS docs for more information.
	 *
	 * This hook is called from ResourceLoaderForeignApiModule.
	 *
	 * @since 1.35
	 * @param string[] &$dependencies List of modules that mediawiki.ForeignApi should
	 *   depend on
	 * @param ResourceLoaderContext|null $context
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onResourceLoaderForeignApiModules( &$dependencies, $context );
}
