<?php
/**
 * @package    Fuel\FileSystem
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\FileSystem\Providers;

use Fuel\Dependency\ServiceProvider;

/**
 * FuelPHP ServiceProvider class for this package
 *
 * @package  Fuel\FileSystem
 *
 * @since  1.0.0
 */
class FuelServiceProvider extends ServiceProvider
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
		// \Fuel\FileSystem\Finder
		$this->register('finder', function ($dic, Array $paths = null, $defaultExtension = null, $root = null)
		{
			return $dic->resolve('Fuel\FileSystem\Finder', array($paths, $defaultExtension, $root));
		});
	}
}
