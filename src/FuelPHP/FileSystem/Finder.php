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
	 * @var  boolean  $returnHandlers  wether to return handlers
	 */
	protected $returnHandlers = false;

	/**
	 * @var  null|boolean  $nextAsHandlers  wether to fetch the next result as handler objects
	 */
	protected $nextAsHandlers = null;

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
	 * Wether to return handlers.
	 *
	 * @param   boolean  $returnHandlers  wether to return handlers
	 * @return  $this
	 */
	public function returnHandlers($returnHandlers = true)
	{
		$this->returnHandlers = $returnHandlers;

		return $this;
	}

	/**
	 * Wether to let the next find result return handlers.
	 *
	 * @param   boolean  $returnHandlers  wether to return handlers
	 * @return  $this
	 */
	public function asHandlers($returnHandlers = true)
	{
		$this->nextAsHandlers = $returnHandlers;

		return $this;
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
	 * @param   string  $path  path
	 * @return  $this
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

		return $this;
	}

	/**
	 * Normalize a path
	 *
	 * @param   string  $path  path
	 * @return  string  normalized path
	 * @throws  Exception
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
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 * @param   string   $type      dir, file or all
	 */
	public function findAll($name, $reload = false, $reversed = false, $type = 'all')
	{
		$name = trim($name, '/');
		$scope = 'all::'.$type;
		$asHandlers = $this->returnHandlers;

		if ($this->nextAsHandlers !== null)
		{
			$asHandlers = $this->nextAsHandlers;
			$this->nextAsHandlers = null;
		}

		if ($type !== 'dir')
		{
			$file = $this->normalizeFileName($name);
		}

		if ( ! $reload and $cached = $this->findCached($scope, $name, $reversed))
		{
			return $cached;
		}

		$used = array();
		$found = array();
		$paths = $reversed ? array_reverse($this->paths) : $this->paths;

		foreach ($paths as $path)
		{
			if ($type !== 'dir' and is_file($path.$file))
			{
				$found[] = $asHandlers ? new File($path.$file) : $path.$file;
				$used[] = $path;
			}
			elseif ($type !== 'file' and is_dir($path.$name))
			{
				$found[] = $asHandlers ? new Directory($path.$name) : $path.$name;
				$used[] = $path;
			}
		}

		// Store the paths in cache
		$this->cache($scope, $name, $reversed, $found, $used);

		return $found;
	}

	/**
	 * Find all files with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findAllFiles($name, $reload = false, $reversed = false)
	{
		return $this->findAll($name, $reload, $reversed, 'file');
	}

	/**
	 * Find all directories with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findAllDirs($name, $reload = false, $reversed = false)
	{
		return $this->findAll($name, $reload, $reversed, 'dir');
	}

	/**
	 * Reverse-find all files and directories with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   string   $type      dir, file or all
	 */
	public function findAllReversed($name, $reload = false, $type = 'all')
	{
		return $this->findAll($name, $reload, true, $type);
	}

	/**
	 * Reverse-find all directories with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 */
	public function findAllDirsReversed($name, $reload = false)
	{
		return $this->findAll($name, $reload, true, 'dir');
	}

	/**
	 * Reverse-find all files with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 */
	public function findAllFilesReversed($name, $reload = false)
	{
		return $this->findAll($name, $reload, true, 'file');
	}

	/**
	 * Find one file or directories with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 * @param   string   $type      dir, file or all
	 */
	public function find($name, $reload = false, $reversed = false, $type = 'all')
	{
		$name = trim($name, '/');
		$scope = 'one::'.$type;
		$asHandlers = $this->returnHandlers;

		if ($this->nextAsHandlers !== null)
		{
			$asHandlers = $this->nextAsHandlers;
			$this->nextAsHandlers = null;
		}

		if ($type !== 'dir')
		{
			$file = $this->normalizeFileName($name);
		}

		if ( ! $reload and $cached = $this->findCached($scope, $name, $reversed))
		{
			return $cached;
		}

		$paths = $reversed ? array_reverse($this->paths) : $this->paths;

		foreach ($paths as $path)
		{
			if ($type !== 'dir' and is_file($path.$file))
			{
				$found = $path.$file;

				if ($asHandlers)
				{
					$found = new File($found);
				}

				break;
			}
			elseif ($type !== 'file' and is_dir($path.$name))
			{
				$found = $path.$name;

				if ($asHandlers)
				{
					$found = new Directory($found);
				}
				break;
			}
		}

		if (isset($found))
		{
			// Store the paths in cache
			$this->cache($scope, $name, $reversed, $found, array($path));

			return $found;
		}
	}

	/**
	 * Find one file with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findFile($name, $reload = false, $reversed = false)
	{
		return $this->find($name, $reload, $reversed, 'file');
	}

	/**
	 * Find one directories with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findDir($name, $reload = false, $reversed = false)
	{
		return $this->find($name, $reload, $reversed, 'dir');
	}

	/**
	 * Reverse-find one file or directory with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 * @param   string   $type      dir, file or all
	 */
	public function findReversed($name, $reload = false, $type = 'all')
	{
		return $this->find($name, $reload, true, $type);
	}

	/**
	 * Reverse-find one file with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findFileReversed($name, $reload = false)
	{
		return $this->findReversed($name, $reload, 'file');
	}

	/**
	 * Reverse-find one directory with a given name/subpath.
	 *
	 * @param   string   $name      file name
	 * @param   boolean  $reload    wether to bypass cache
	 * @param   boolean  $reversed  wether to search reversed
	 */
	public function findDirReversed($name, $reload = false)
	{
		return $this->findReversed($name, $reload, 'dir');
	}

	/**
	 * Retrieve a location from cache.
	 *
	 * @param   string        $scope     scope [all,one]
	 * @param   string        $name      file name
	 * @param   boolean       $reversed  wether the search was reversed
	 * @return  string|array  cached result
	 */
	public function findCached($scope, $name, $reversed)
	{
		$cacheKey = $this->makeCacheKey($scope, $name, $reversed);

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
	 * @param   string   $name       file name
	 * @param   boolean  $reversed   wether it was a reversed search
	 * @param   array    $pathsUsed  which paths it depended on
	 * @return  $this
	 */
	public function cache($scope, $name, $reversed, $result, $pathsUsed = array())
	{
		$cacheKey = $this->makeCacheKey($scope, $name, $reversed);
		$this->cache[$cacheKey] = array($result, $pathsUsed);

		return $this;
	}

	/**
	 * Generate a cache key
	 *
	 * @param   string   $scope     find scope
	 * @param   string   $name      file name
	 * @param   boolean  $reversed  wether it was a reversed search
	 * @return  string   cache key
	 */
	public function makeCacheKey($scope, $name, $reversed)
	{
		$cacheKey = $scope.'::'.$name;

		if ($reversed)
		{
			$cacheKey .= '::reversed';
		}

		return $cacheKey;
	}

	/**
	 * Normalize a file name
	 *
	 * @param   string  $name  file name
	 * @return  string  normalized filename
	 */
	public function normalizeFileName($name)
	{
		if ( ! pathinfo($name, PATHINFO_EXTENSION))
		{
			$name .= '.'.$this->defaultExtension;
		}

		return ltrim($name, '/');
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