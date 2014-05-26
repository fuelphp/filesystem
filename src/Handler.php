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

abstract class Handler
{
	/**
	 * @var  string  $path  path
	 */
	protected $path;

	/**
	 * Constructor
	 *
	 * @param  string  $path  path
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Check wether a file/dir exists
	 *
	 * @return  boolean  wether the file/dir exists
	 */
	public function exists()
	{
		return file_exists($this->path);
	}

	/**
	 * Delete a file/dir
	 *
	 * @return  boolean  wether the file/dir was deleted
	 */
	public function delete()
	{
		return unlink($this->path);
	}

	/**
	 * Move a file/dir
	 *
	 * @return  boolean  wether the file/dir was moved
	 */
	public function moveTo($destination)
	{
		return $this->renameTo($destination);
	}

	/**
	 * Rename a file/dir
	 *
	 * @return  boolean  wether the file/dir was renamed
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
	 * Create a symlink to a file/dir
	 *
	 * @return  boolean  wether the symlink was created
	 */
	public function symlinkTo($destination)
	{
		return symlink($this->path, $destination);
	}

	/**
	 * Check wether a file/dir is writable
	 *
	 * @return  boolean  wether a file/dir is writable
	 */
	public function isWritable()
	{
		return is_writable($this->path);
	}

	/**
	 * Check wether a file/dir is readable
	 *
	 * @return  boolean  wether a file/dir is readable
	 */
	public function isReadable()
	{
		return is_readable($this->path);
	}

	/**
	 * Retrieve wether the path is a file or a dir
	 *
	 * @return  string  wether the path is a file or a dir
	 */
	public function getType()
	{
		return filetype($this->path);
	}

	/**
	 * Retrieve the last access time
	 *
	 * @return  int  last access time
	 */
	public function getAccessTime()
	{
		return fileatime($this->path);
	}

	/**
	 * Retrieve the last modified time
	 *
	 * @return  int  last modified time
	 */
	public function getModifiedTime()
	{
		return filemtime($this->path);
	}

	/**
	 * Retrieve the created time
	 *
	 * @return  int  created time
	 */
	public function getCreatedTime()
	{
		return filectime($this->path);
	}

	/**
	 * Retrieve the permissions
	 *
	 * @return  int  permissions
	 */
	public function getPermissions()
	{
		return fileperms($this->path);
	}

	/**
	 * Set the permissions
	 *
	 * @return  boolean  wether the permissions were set
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
	 * Convert to path
	 *
	 * @return  string  path name
	 */
	public function __toString()
	{
		return $this->path;
	}
}
