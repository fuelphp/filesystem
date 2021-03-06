<?php
/**
 * @package    Fuel\FileSystem
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\FileSystem;


class Directory extends Handler
{
	/**
	 * Deletes a directory recursively
	 *
	 * @return boolean
	 */
	public function deleteRecursive()
	{
		return $this->delete(true);
	}

	/**
	 * Deletes a directory
	 *
	 * @param boolean $recursive
	 *
	 * @return boolean
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
	 * Lists all files in a directory
	 *
	 * @param integer $depth
	 * @param mixed   $filter
	 * @param boolean $asHandlers
	 *
	 * @return array
	 */
	public function listFiles($depth = 0, $filter = null, $asHandlers = false)
	{
		return $this->listContents($depth, $filter, 'file', $asHandlers);
	}

	/**
	 * Lists all files in a directory as Handlers
	 *
	 * @param integer $depth
	 * @param mixed   $filter
	 *
	 * @return array
	 */
	public function listFileHandlers($depth = 0, $filter = null)
	{
		return $this->listContents($depth, $filter, 'file', true);
	}

	/**
	 * Lists all directories in a directory
	 *
	 * @param integer $depth
	 * @param mixed   $filter
	 * @param boolean $asHandlers
	 *
	 * @return array
	 */
	public function listDirs($depth = 0, $filter = null, $asHandlers = false)
	{
		return $this->listContents($depth, $filter, 'dir', $asHandlers);
	}

	/**
	 * Lists all directories in a directory
	 *
	 * @param integer $depth
	 * @param mixed   $filter
	 *
	 * @return array
	 */
	public function listDirHandlers($depth = 0, $filter = null)
	{
		return $this->listContents($depth, $filter, 'dir', true);
	}

	/**
	 * Lists all files and directories in a directory
	 *
	 * @param integer  $depth
	 * @param mixed    $filter
	 * @param string   $type
	 * @param boolean  $asHandlers
	 *
	 * @return array
	 */
	public function listContents($depth = 0, $filter = null, $type = 'all', $asHandlers = false)
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

				$expected = true;

				if (strpos($f, '!') === 0)
				{
					$f = substr($f, 1);
					$expected = false;
				}

				$filter->addFilter($f, $expected, $type);
			}
		}

		if ($filter instanceof \Closure)
		{
			$callback = $filter;
			$filter = new Filter();
			$callback($filter);
		}

		if ( ! $filter)
		{
			$filter = new Filter;
		}

		$flags = GLOB_MARK;

		if ($type === 'file' and ! pathinfo($pattern, PATHINFO_EXTENSION))
		{
			// Add an extension wildcard
			$pattern .= '.*';
		}
		elseif ($type === 'dir')
		{
			$flags = GLOB_MARK | GLOB_ONLYDIR;
		}

		$contents = glob($pattern, $flags);

		// Filter the content.
		$contents = $filter->filter($contents);

		// Lower the depth for a recursive call
		if ($depth and $depth !== true)
		{
			$depth--;
		}

		$formatted = array();

		foreach ($contents as $item)
		{
			if ($filter->isCorrectType('dir', $item))
			{
				$_contents = array();

				if (($depth === true or $depth === 0) and ! $asHandlers)
				{
					$dir = new Directory($item);

					$_contents = $dir->listContents($item, $filter, $depth, $type);
				}

				if ($asHandlers)
				{
					$formatted[] = new Directory($item);
				}
				else
				{
					$formatted[$item] = $_contents;
				}
			}
			elseif ($filter->isCorrectType('file', $item))
			{
				if ($asHandlers)
				{
					$item = new File($item);
				}

				$formatted[] = $item;
			}
		}

		return $formatted;
	}
}
