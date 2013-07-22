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

class Filter
{
	/**
	 * @var  array  $typeCache  cache for file types
	 */
	protected $typeCache = array();

	/**
	 * @var  array  $filters  filters
	 */
	protected $filters = array();

	/**
	 * Check wether an path is of the correct type, dir or file.
	 *
	 * @param   string   $type  desired type
	 * @param   string   $path  path to a file
	 * @return  boolean  wether the path is of the correct type
	 */
	public function isCorrectType($type, $path)
	{
		if ( ! $type)
		{
			return true;
		}

		if ( ! isset($this->typeCache[$path]))
		{
			$this->typeCache[$path] = is_file($path) ? 'file' : 'dir';
		}

		return $this->typeCache[$path] === $type;
	}

	/**
	 * Filter a batch of filesystem entries.
	 *
	 * @param   array  $contents  entries to filter
	 * @return  array  filtered contents
	 */
	public function filter(array $contents)
	{
		$filtered = array();
		$this->typeCache = array();

		foreach ($contents as $item)
		{
			$passed = true;

			foreach ($this->filters as $filter)
			{
				$correctType = $this->isCorrectType($filter['type'], $item);

				if ($correctType and preg_match($filter['pattern'], $item) !== $expected)
				{
					$passed = false;
				}
			}

			if ($passed)
			{
				$filtered[] = $item;
			}
		}

		return $contents;
	}

	/**
	 * Add a filter
	 *
	 * @param   string   $filter    regex expression
	 * @param   boolean  $expected  expected result
	 * @param   string   $type      file, dir or null for all
	 */
	public function addFilter($filter, $expected = true, $type = null)
	{
		$filter = '#'.$filter.'#';

		$this->filters[] = array(
			'pattern' => $filter,
			'expected' => $expected,
			'type' => $type,
		);

		return $this;
	}

	/**
	 * Ensure an extension
	 *
	 * @param   string  $extension  file extension
	 * @return  $this
	 */
	public function hasExtension($extension)
	{
		$filter = '\\.['.ltrim($extension, '.').']$';

		return $this->addFilter($filter, true, 'file');
	}

	/**
	 * Block by extension
	 *
	 * @param   string  $extension  file extension
	 * @return  $this
	 */
	public function blockExtension($extension)
	{
		$filter = '\\.['.ltrim($extension, '.').']$';

		return $this->addFilter($filter, false, 'file');
	}

	/**
	 * Block hidden files
	 *
	 * @param   string  $type  file, dir or null for both
	 * @return  $this
	 */
	public function blockHidden($type = null)
	{
		$filter = '^\\.';

		return $this->addFilter($filter, false, $type);
	}

	/**
	 * Allow only hidden files
	 *
	 * @param   string  $type  file, dir or null for both
	 * @return  $this
	 */
	public function isHidden($type = null)
	{
		$filter = '^\\.';

		return $this->addFilter($filter, true, $type);
	}
}
