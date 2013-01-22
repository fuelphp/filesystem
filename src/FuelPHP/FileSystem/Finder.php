<?php

namespace FuelPHP\FileSystem;

use Closure;
use Exception;

class Finder
{
	/**
	 * @var  string  $defaultExtension  default extension
	 */
	protected $defaultExtension = 'php';

	/**
	 * @var  array  $paths  paths to look in
	 */
	protected $paths = array();

	/**
	 * @var  string  $root  root restriction
	 */
	protected $root;

	/**
	 * Constructor.
	 *
	 * @param  array   $path  paths
	 * @param  string  $defaultExtension  default file extension
	 * @param  string  $root              root restriction
	 */
	public function __construct(array $paths = null, $defaultExtension = null, $root = null)
	{
		if ($paths)
		{
			$this->addPaths((array) $paths);
		}

		if ($defaultExtension)
		{
			$this->setDefaultExtension($defaultExtension);
		}

		$this->root = $root;
	}

	/**
	 * Set a root restriction
	 *
	 * @param   string  $root  root restriction
	 * @return  $this
	 */
	public function setRoot($root)
	{
		if ( ! $path = realpath($root))
		{
			throw new Exception('Location does not exist: '.$root);
		}

		$this->root = $path;

		return $this;
	}

	/**
	 * Get the root
	 *
	 * @return  string  root path
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Adds paths to look in.
	 *
	 * @param   array  $paths  paths
	 * @param   boolean  $clearCache  wether to clear the cache
	 * @return  $this
	 */
	public function addPaths(array $paths, $clearCache = true)
	{
		array_map(array($this, 'addPath'), $paths, array($clearCache));

		return $this;
	}

	/**
	 * Add a path
	 *
	 * @param   string   $path        path
	 * @param   boolean  $clearCache  wether to clear the cache
	 * @return  $this
	 */
	public function addPath($path, $clearCache = true)
	{
		$path = $this->normalizePath($path);

		// This is done for easy reference and
		// eliminates the need to check for doubles
		$this->paths[$path] = $path;

		if ($clearCache)
		{
			$this->cache = array();
		}

		return $this;
	}

	/**
	 * Remove paths to look in
	 *
	 * @param   array  $paths  paths to remove
	 * @return  $this
	 */
	public function removePaths(array $paths)
	{
		array_map(array($this, 'removePath'), $paths);

		return $this;
	}

	/**
	 * Remove a path
	 *
	 * @param   string  $path  path
	 * @return  $this
	 */
	public function removePath($path)
	{
		$path = $this->normalizePath($path);

		if ($path and isset($this->paths[$path]))
		{
			unset($this->paths[$path]);

			$this->removePathCache($path);
		}

		return $this;
	}

	/**
	 * Remove path cache
	 *
	 * @param  string  $path  path
	 */
	public function removePathCache($path)
	{
		foreach ($this->cache as $key => $cache)
		{
			if (in_array($path, $cache[1]))
			{
				unset($this->cache[$key]);
			}
		}
	}

	/**
	 * Normalize a path
	 *
	 * @param   string  $path  path
	 * @return  string  normalized path
	 */
	public function normalizePath($path)
	{
		$path = rtrim($path, '/\\').'/';
		$path = realpath($path).'/';

		if ($this->root and strpos($path, $this->root) !== 0)
		{
			throw new Exception('Cannot access path outside: '.$this->root.'. Trying to access: '.$path);
		}

		return $path;
	}

	/**
	 * Get the paths set up to look in.
	 *
	 * @return  array  paths array
	 */
	public function getPaths()
	{
		return array_values($this->paths);
	}

	/**
	 * Replace all the paths
	 *
	 * @param   array  $paths  paths
	 * @return  $this
	 */
	public function setPaths(array $paths)
	{
		$this->paths = array();
		$this->addPaths($paths);

		return $this;
	}

	/**
	 * Find all files with a given name/subpath.
	 *
	 * @param   string   $file      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findAll($file, $reload = false, $reversed = false)
	{
		$file = $this->normalizeFileName($file);

		if ( ! $reload and $cached = $this->findCached('all', $file, $reversed))
		{
			return $cached;
		}

		$used = array();
		$found = array();
		$paths = $reversed ? array_reverse($this->paths) : $this->paths;

		foreach ($paths as $path)
		{
			if (is_file($path.$file))
			{
				$found[] = $path.$file;
				$used[] = $path;
			}
		}

		// Store the paths in cache
		$this->cache('all', $file, $reversed, $found, $used);

		return $found;
	}

	/**
	 * Reverse-find all files with a given name/subpath.
	 *
	 * @param   string   $file      file name
	 * @param   boolean  $reload    wether to bypass cache
	 */
	public function findAllReversed($file, $reload = false)
	{
		return $this->findAll($file, $reload, true);
	}

	/**
	 * Find one file with a given name/subpath.
	 *
	 * @param   string   $file      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function find($file, $reload = false, $reversed = false)
	{
		$file = $this->normalizeFileName($file);

		if ( ! $reload and $cached = $this->findCached('one', $file, $reversed))
		{
			return $cached;
		}

		$paths = $reversed ? array_reverse($this->paths) : $this->paths;

		foreach ($paths as $path)
		{
			if (is_file($path.$file))
			{
				$found = $path.$file;
				break;
			}
		}

		if (isset($found))
		{
			// Store the paths in cache
			$this->cache('one', $file, $reversed, $found, array($path));

			return $found;
		}
	}

	/**
	 * Reverse-find one file with a given name/subpath.
	 *
	 * @param   string   $file      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findReversed($file, $reload = false)
	{
		return $this->find($file, $reload, true);
	}

	/**
	 * Retrieve a location from cache.
	 *
	 * @param   string        $scope     scope [all,one]
	 * @param   string        $file      file name
	 * @param   boolean       $reversed  wether the search was reversed
	 * @return  string|array  cached result
	 */
	public function findCached($scope, $file, $reversed)
	{
		$cacheKey = $this->makeCacheKey($scope, $file, $reversed);

		if (isset($this->cache[$cacheKey]))
		{
			return $this->cache[$cacheKey][0];
		}
	}

	/**
	 * Clear the location cache
	 *
	 * @return  $this
	 */
	public function clearCache()
	{
		$this->cached = array();

		return $this;
	}

	/**
	 * Cache a find result
	 *
	 * @param   string   $scope      find scope
	 * @param   string   $file       file name
	 * @param   boolean  $reversed   wether it was a reversed search
	 * @param   array    $pathsUsed  which paths it depended on
	 * @return  $this
	 */
	public function cache($scope, $file, $reversed, $result, $pathsUsed = array())
	{
		$cacheKey = $this->makeCacheKey($scope, $file, $reversed);
		$this->cache[$cacheKey] = array($result, $pathsUsed);

		return $this;
	}

	/**
	 * Generate a cache key
	 *
	 * @param   string   $scope     find scope
	 * @param   string   $file      file name
	 * @param   boolean  $reversed  wether it was a reversed search
	 * @return  string   cache key
	 */
	public function makeCacheKey($scope, $file, $reversed)
	{
		$cacheKey = $scope.'::'.$file;

		if ($reversed)
		{
			$cacheKey .= '::reversed';
		}

		return $cacheKey;
	}

	/**
	 * Normalize a file name
	 *
	 * @param   string  $file  file name
	 * @return  string  normalized filename
	 */
	public function normalizeFileName($file)
	{
		if ( ! pathinfo($file, PATHINFO_EXTENSION))
		{
			$file .= '.'.$this->defaultExtension;
		}

		return ltrim($file, '/');
	}

	/**
	 * Set the default extension
	 *
	 * @param   string  $extension  extension
	 * @return  $this
	 */
	public function setDefaultExtension($extension)
	{
		$this->defaultExtension = ltrim($extension, '.');

		return $this;
	}
}