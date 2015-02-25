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

abstract class Handler
{
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Checks whether a file/dir exists
	 *
	 * @return boolean
	 */
	public function exists()
	{
		return file_exists($this->path);
	}

	/**
	 * Deletes a file/dir
	 *
	 * @return boolean
	 */
	public function delete()
	{
		return unlink($this->path);
	}

	/**
	 * Moves a file/dir
	 *
	 * @return boolean
	 */
	public function moveTo($destination)
	{
		return $this->renameTo($destination);
	}

	/**
	 * Renames a file/dir
	 *
	 * @return boolean
	 */
	public function renameTo($name)
	{
		if (strpos($name, DIRECTORY_SEPARATOR) !== 0)
		{
			$name = pathinfo($this->path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.$name;
		}

		if ( ! pathinfo($name, PATHINFO_EXTENSION))
		{
			$name .= '.'.pathinfo($this->path, PATHINFO_EXTENSION);
		}

		if ($result = rename($this->path, $name))
		{
			$this->path = realpath($name);
		}

		return $result;
	}

	/**
	 * Creates a symlink to a file/dir
	 *
	 * @return boolean
	 */
	public function symlinkTo($destination)
	{
		return symlink($this->path, $destination);
	}

	/**
	 * Checks wether a file/dir is writable
	 *
	 * @return boolean
	 */
	public function isWritable()
	{
		return is_writable($this->path);
	}

	/**
	 * Checks wether a file/dir is readable
	 *
	 * @return boolean
	 */
	public function isReadable()
	{
		return is_readable($this->path);
	}

	/**
	 * Retrieves wether the path is a file or a dir
	 *
	 * @return string
	 */
	public function getType()
	{
		return filetype($this->path);
	}

	/**
	 * Retrieves the last access time
	 *
	 * @return integer
	 */
	public function getAccessTime()
	{
		return fileatime($this->path);
	}

	/**
	 * Retrieves the last modified time
	 *
	 * @return integer
	 */
	public function getModifiedTime()
	{
		return filemtime($this->path);
	}

	/**
	 * Retrieves the created time
	 *
	 * @return integer
	 */
	public function getCreatedTime()
	{
		return filectime($this->path);
	}

	/**
	 * Retrieves the permissions
	 *
	 * @return integer
	 */
	public function getPermissions()
	{
		return fileperms($this->path);
	}

	/**
	 * Sets the permissions
	 *
	 * @return boolean
	 */
	public function setPermissions($permissions)
	{
		if (is_string($permissions))
		{
			$permissions = '0'.ltrim($permissions, '0');
			$permissions = octdec($permissions);
		}

		return chmod($this->path, $permissions);
	}

	/**
	 * Retrieves the path
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Converts to path
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getPath();
	}
}
