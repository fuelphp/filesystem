<?php

namespace FuelPHP\FileSystem;

class Directory extends Handler
{
	/**
	 * Delete a directory recursively
	 *
	 * @return  boolean  wether the directory was deleted
	 */
	public function deleteRecursive()
	{
		return $this->delete(true);
	}

	/**
	 * Delete a directory
	 *
	 * @param   boolean  $recursive  wether to delete it's contents too
	 * @return  boolean  wether the directory was deleted
	 */
	public function delete($recursive = false)
	{
		if ( ! $recursive)
		{
			return parent::delete();
		}

		$finder = new Finder();
		$contents = $finder->listContents($this->path);

		foreach($contents as $item)
		{
			$item->delete(true);
		}

		return parent::delete();
	}

	/**
	 * List all files in a directory
	 *
	 * @param   int    $depth   depth
	 * @param   mixed  $filter  filter
	 * @return  array  directory contents
	 */
	public function listFiles($depth = 0, $filter = null)
	{
		return $this->listContents($depth, $filter, 'file');
	}

	/**
	 * List all directories in a directory
	 *
	 * @param   int    $depth   depth
	 * @param   mixed  $filter  filter
	 * @return  array  directory contents
	 */
	public function listDirs($depth = 0, $filter = null)
	{
		return $this->listContents($depth, $filter, 'dir');
	}

	/**
	 * List all files and directories in a directory
	 *
	 * @param   int     $depth   depth
	 * @param   mixed   $filter  filter
	 * @param   string  $type    file or dir
	 * @return  array   directory contents
	 */
	public function listContents($depth = 0, $filter = null, $type = 'all')
	{
		$pattern = $this->path.'/*';

		if (is_array($filter))
		{
			$filters = $filter;
			$filter = new Filter;

			foreach ($filters as $f => $type)
			{
				if ( ! is_int($f))
				{
					$f = $type;
					$type = null;
				}


			}

		}

		if ($filter instanceof Closure)
		{
			$callback = $filter;
			$filter = new Filter();
			$callback($filter);
		}

		$flags = GLOB_MARK;

		if ($type === 'file' and ! pathinfo($pattern, PATHINFO_EXTENSION))
		{
			// Add an extension wildcard
			$pattern .= '.*';
		}
		elseif ($type === 'dir')
		{
			$flags = GLOB_MARK | GLOB_ONLYDIR | GLOB_MARK;
		}

		$contents = glob($pattern, $flags);

		// Filter the content.
		if ($filter instanceof Filter)
		{
			$contents = $filter->filter($contents);
		}

		// Lower the depth for a recursive call
		if ($depth and $depth !== true)
		{
			$depth--;
		}

		$formatted = array();

		foreach ($contents as $item)
		{
			if ($type !== 'file' and is_dir($item))
			{
				$_contents = array();

				if ($depth === true or $depth === 0)
				{
					$_contents = $this->listContents($item, $filter, $depth, $type);
				}

				$formatted[$item] = $_contents;
			}
			elseif ($type !== 'dir' and is_file($item))
			{
				$formatted[] = $item;
			}
		}

		return $formatted;
	}
}