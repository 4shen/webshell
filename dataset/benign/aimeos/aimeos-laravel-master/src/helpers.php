<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @copyright Aimeos (aimeos.org), 2017
 * @package laravel
 */


if( !function_exists( 'aiconfig' ) )
{
	/**
	 * Returns the configuration setting for the given key
	 *
	 * @param string $key Configuration key
	 * @param mixed $default Default value if the configuration key isn't found
	 * @return mixed Configuration value
	 */
	function aiconfig( $key, $default = null )
	{
		return app( '\Aimeos\Shop\Base\Config' )->get()->get( $key, $default );
	}
}


if( !function_exists( 'aitrans' ) )
{
	/**
	 * Translates the given message
	 *
	 * @param string $singular Message to translate
	 * @param array $params List of paramters for replacing the placeholders in that order
	 * @param string $domain Translation domain
	 * @param string $locale ISO language code, maybe combine with ISO currency code, e.g. "en_US"
	 * @return string Translated string
	 */
	function aitrans( $singular, array $params = array(), $domain = 'client', $locale = null )
	{
		$i18n = app( '\Aimeos\Shop\Base\Context' )->get()->getI18n( $locale );

		return vsprintf( $i18n->dt( $domain, $singular ), $params );
	}
}


if( !function_exists( 'aitransplural' ) )
{
	/**
	 * Translates the given messages based on the number
	 *
	 * @param string $singular Message to translate
	 * @param string $plural Message for plural translations
	 * @param integer $number Count of items to chose the correct plural translation
	 * @param array $params List of paramters for replacing the placeholders in that order
	 * @param string $domain Translation domain
	 * @param string $locale ISO language code, maybe combine with ISO currency code, e.g. "en_US"
	 * @return string Translated string
	 */
	function aitransplural( $singular, $plural, $number, array $params = array(), $domain = 'client', $locale = null )
	{
		$i18n = app( '\Aimeos\Shop\Base\Context' )->get()->getI18n( $locale );

		return vsprintf( $i18n->dn( $domain, $singular, $plural, $number ), $params );
	}
}
