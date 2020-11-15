<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @copyright Aimeos (aimeos.org), 2019
 * @package laravel
 * @subpackage Base
 */

namespace Aimeos\Shop\Base;


/**
 * Service providing the shop object
 *
 * @package laravel
 * @subpackage Base
 */
class Shop
{
	/**
	 * @var \Aimeos\MShop\Context\Item\Iface
	 */
	private $context;

	/**
	 * @var \Aimeos\MW\View\Iface
	 */
	private $view;

	/**
	 * @var array
	 */
	private $objects = [];


	/**
	 * Initializes the object
	 *
	 * @param \Aimeos\Shop\Base\Aimeos $aimeos Aimeos object
	 * @param \Aimeos\Shop\Base\Context $context Context object
	 * @param \Aimeos\Shop\Base\View $view View object
	 */
	public function __construct( \Aimeos\Shop\Base\Aimeos $aimeos,
		\Aimeos\Shop\Base\Context $context, \Aimeos\Shop\Base\View $view )
	{
		$this->context = $context->get();

		$langid = $this->context->getLocale()->getLanguageId();
		$tmplPaths = $aimeos->get()->getCustomPaths( 'client/html/templates' );

		$this->view = $view->create( $this->context, $tmplPaths, $langid );
		$this->context->setView( $this->view );
	}


	/**
	 * Returns the HTML client for the given name
	 *
	 * @param string $name Name of the shop component
	 * @return \Aimeos\Client\Html\Iface HTML client
	 */
	public function get( string $name ) : \Aimeos\Client\Html\Iface
	{
		if( !isset( $this->objects[$name] ) )
		{
			$client = \Aimeos\Client\Html::create( $this->context, $name );
			$client->setView( clone $this->view );
			$client->process();

			$this->objects[$name] = $client;
		}

		return $this->objects[$name];
	}
}