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

class File extends Handler
{
	/**
	 * Get the files contents
	 *
	 * @return  string  file contents
	 */
	public function getContents()
	{
		return file_get_contents($this->path);
	}

	/**
	 * Append data to a file
	 *
	 * @param   string   $data  data to append
	 * @return  boolean  wether the data was appended
	 */
	public function append($data)
	{
		$bites = file_put_contents($this->path, $data, FILE_APPEND | LOCK_EX);

		return $bites !== false;
	}

	/**
	 * Update a file
	 *
	 * @param   string   $data  new data
	 * @return  boolean  wether the file was updated
	 */
	public function update($data)
	{
		$bites = file_put_contents($this->path, $data, LOCK_EX);

		return $bites !== false;
	}

	/**
	 * Copy a file
	 *
	 * @param   string   $destination  new location
	 * @return  boolean  wether the file was copied
	 */
	public function copyTo($destination)
	{
		return copy($this->path, $destination);
	}

	/**
	 * get the files size
	 *
	 * @param   string   $destination  new location
	 * @return  boolean  wether the file was copied
	 */
	public function getSize()
	{
		return filesize($this->path);
	}

	/**
	 * Get the mime-type
	 *
	 * @return  string  file's mime-type
	 */
	public function getMimeType()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->path);
		finfo_close($finfo);

		return $mime;
	}
}
