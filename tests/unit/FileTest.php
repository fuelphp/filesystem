<?php

namespace Fuel\FileSystem;

use Codeception\TestCase\Test;

class FileTest extends Test
{

	public function testFile()
	{
		$path = __DIR__.'/../resources/one/a.php';
		$file = new File($path);
		$newContent = time();
		$this->assertEquals($path, $file->getPath());
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
