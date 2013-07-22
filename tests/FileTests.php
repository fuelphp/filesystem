<?php

use Fuel\FileSystem\File;

class FileTests extends PHPUnit_Framework_TestCase
{
	public function testFile()
	{
		$file = new File(__DIR__.'/../resources/one/a.php');
		$newContent = time();
		$file->update($newContent);
		$this->assertEquals($newContent, $file->getContents());
		$file->append(' appended');
		$this->assertEquals($newContent.' appended', $file->getContents());
		$this->assertTrue($file->exists());
		$nonExisting = new File(__DIR__.'/file.txt');
		$this->assertFalse($nonExisting->exists());
		$file->update('seven b');
		$this->assertEquals(7, $file->getSize());
		$now = time();
		$file->copyTo(__DIR__.'/../resources/newPlace.txt');
		$this->assertTrue(file_exists(__DIR__.'/../resources/newPlace.txt'));
		$deleteThis = new File(__DIR__.'/../resources/newPlace.txt');
		$this->assertEquals($now, $deleteThis->getCreatedTime());
		$deleteThis->moveTo('otherPlace');
		$this->assertFalse(file_exists(__DIR__.'/../resources/newPlace.txt'));
		$this->assertTrue(file_exists(__DIR__.'/../resources/otherPlace.txt'));
		$deleteThis->delete();
		$this->assertFalse(file_exists(__DIR__.'/../resources/otherPlace.txt'));
		$this->assertInternalType('string', $file->getMimeType());
		$this->assertEquals('file', $file->getType());
		$this->assertInternalType('int', $file->getAccessTime());
		$this->assertInternalType('int', $file->getModifiedTime());
	}
}
