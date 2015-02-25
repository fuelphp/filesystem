<?php
/**
 * @package    Fuel\FileSystem
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\FileSystem\Providers;

use Fuel\FileSystem\Finder;
use League\Container\ServiceProvider;

/**
 * Fuel ServiceProvider class for Filesystem
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * @var array
	 */
	public $provides = ['finder'];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->add('finder', function (array $paths = null, $defaultExtension = null, $root = null)
		{
			return new Finder($paths, $defaultExtension, $root);
		});
	}
}
