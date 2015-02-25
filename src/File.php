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

class File extends Handler
{
	/**
	 * Returns the files contents
	 *
	 * @return string
	 */
	public function getContents()
	{
		return file_get_contents($this->path);
	}

	/**
	 * Appends data to a file
	 *
	 * @param string $data
	 *
	 * @return boolean
	 */
	public function append($data)
	{
		$bites = file_put_contents($this->path, $data, FILE_APPEND | LOCK_EX);

		return $bites !== false;
	}

	/**
	 * Updates a file
	 *
	 * @param string $data
	 *
	 * @return boolean
	 */
	public function update($data)
	{
		$bites = file_put_contents($this->path, $data, LOCK_EX);

		return $bites !== false;
	}

	/**
	 * Copies a file
	 *
	 * @param string $destination
	 *
	 * @return boolean
	 */
	public function copyTo($destination)
	{
		return copy($this->path, $destination);
	}

	/**
	 * Returns the file size
	 *
	 * @param string $destination
	 *
	 * @return boolean
	 */
	public function getSize()
	{
		return filesize($this->path);
	}

	/**
	 * Returns the mime-type
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->path);
		finfo_close($finfo);

		return $mime;
	}
}
