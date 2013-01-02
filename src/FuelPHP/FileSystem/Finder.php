<?php

namespace FuelPHP\FileSystem;

use Closure;

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
	 * Constructor.
	 *
	 * @param  array  $path  paths
	 */
	public function __construct($paths = array())
	{
		$this->addPaths((array) $paths);
	}

	/**
	 * Adds paths to look in.
	 *
	 * @param   array  $paths  paths
	 * @return  $this
	 */
	public function addPaths(array $paths)
	{
		array_map(array($this, 'addPath'), $paths);

		return $this;
	}

	/**
	 * Add a path
	 *
	 * @param   string  $path  path
	 * @return  $this
	 */
	public function addPath($path)
	{
		$path = $this->normalizePath($path);

		// This is done for easy reference and
		// eliminates the need to check for doubles
		$this->paths[$path] = $path;

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

		if (isset($this->paths[$path]))
		{
			unset($this->paths[$path]);
		}

		return $this;
	}

	/**
	 * Normalize a path
	 *
	 * @param   string  $path  path
	 * @return  string  normalized path
	 */
	public function normalizePath($path)
	{
		$seperators = array('\\', '/./', '//');
		$path = str_replace($seperators, '/', $path);

		return rtrim($path, '/').'/';
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

		$found = array();
		$paths = $reversed ? array_reverse($this->paths) : $this->paths;

		foreach ($paths as $path)
		{
			if (is_file($path.$file))
			{
				$found[] = $path.$file;
			}
		}

		// Store the paths in cache
		$this->cache('all', $file, $reversed, $found);

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
			$this->cache('one', $file, $reversed, $found);

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
			return $this->cache[$cacheKey];
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
	 * @param   string   $scope     find scope
	 * @param   string   $file      file name
	 * @param   boolean  $reversed  wether it was a reversed search
	 * @return  $this
	 */
	public function cache($scope, $file, $reversed, $result)
	{
		$cacheKey = $this->makeCacheKey($scope, $file, $reversed);
		$this->cache[$cacheKey] = $result;

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
		$extension = pathinfo($file, PATHINFO_EXTENSION);

		if ( ! $extension)
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