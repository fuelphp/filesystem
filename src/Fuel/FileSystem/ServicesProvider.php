<?php
/**
 * @package    Fuel\FileSystem
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\FileSystem;

use Fuel\Dependency\ServiceProvider;

/**
 * ServicesProvider class
 *
 * Defines the services published by this namespace to the DiC
 *
 * @package  Fuel\FileSystem
 *
 * @since  1.0.0
 */
class ServicesProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array('finder');

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Display\ViewManager
		$this->register('finder', function ($dic, array $paths = null, $defaultExtension = null, $root = null)
		{
			return new Finder($paths, $defaultExtension, $root);
		});
	}
}
