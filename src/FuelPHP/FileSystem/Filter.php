<?php

namespace FuelPHP\FileSystem;

class Filter
{
	public function filter(array $contents)
	{
		$filtered = array();

		foreach ($contents as $item)
		{
			$passed = true;

			foreach ($this->filter as $filter)
			{
				if (preg_match($filter['pattern'], $item) !== $expected)
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

	public function addFilter($filter, $expected = true)
	{
		$filter = '#'.$filter.'#';

		$this->filters[] = array(
			'pattern' => $filter,
			'expected' => $expected
		);

		return $this;
	}

	public function hasExtension($extension)
	{
		$filter = '\\.['.ltrim($extension, '.').']$';

		return $this->addFilter($filter);
	}

	public function blockExtension($extension)
	{
		$filter = '\\.['.ltrim($extension, '.').']$';

		return $this->addFilter($filter, false);
	}

	public function blockHidden()
	{
		$filter = '^\\.';

		return $this->addFilter($filter, false);
	}

	public function isHidden()
	{
		$filter = '^\\.';

		return $this->addFilter($filter);
	}
}