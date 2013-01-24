<?php

use FuelPHP\FileSystem\Finder;

class FinderTests extends PHPUnit_Framework_TestCase
{
	public function testFindFile()
	{
		$base = realpath(__DIR__.'/../resources/');
		$finder = new Finder();
		$finder->setPaths(array($base.'/one'));

		$this->assertNull($finder->findFile('null'));

		$this->assertEquals($base.'/one/a.php', $finder->findFile('a'));

		$this->assertEquals(array(
			$base.'/one/a.php'
		), $finder->findAllFiles('a'));

		$this->assertEquals(array(
			$base.'/one/a.php'
		), $finder->findAllFiles('a'));

		$finder->addPaths(array(
			$base.'/two',
			$base.'/three',
		));

		$this->assertEquals(array(
			$base.'/three/a.php',
			$base.'/one/a.php',
		), $finder->findAllFilesReversed('a'));

		$this->assertEquals($base.'/three/a.php', $finder->findFileReversed('a'));
		$this->assertEquals($base.'/three/a.txt', $finder->findFileReversed('a.txt'));

		$finder->setDefaultExtension('txt');
		$finder->clearCache();
		$this->assertEquals(array(
			$base.'/two/a.txt',
			$base.'/three/a.txt',
		), $finder->findAllFiles('a'));

		$this->assertEquals(array(
			$base.'/three/a.txt',
			$base.'/two/a.txt',
		), $finder->findAllReversed('a'));

		$finder->asHandlers();
		$finder->addPath(__DIR__.'/../resources');
		$this->assertContainsOnlyInstancesOf('FuelPHP\FileSystem\File', $finder->findAll('a'));
		$finder->asHandlers();
		$this->assertContainsOnlyInstancesOf('FuelPHP\FileSystem\Directory', $finder->findAll('one'));

		$finder->removePath(__DIR__.'/../resources');
		$this->assertEquals(array(
			$base.'/one/',
			$base.'/two/',
			$base.'/three/',
		), $finder->getPaths());

		$this->assertEquals($base.'/one/a.php', $finder->findFile('a.php'));
		$this->assertEquals($base.'/one/a.php', $finder->findFile('a.php'));
		$finder->removePaths(array($base.'/one'));
		$this->assertEquals($base.'/three/a.php', $finder->findFile('a.php'));

		$finder->addPath(__DIR__.'/../resources/');
		$expected = $base.'/one';
		$this->assertEquals($expected, $finder->findDir('one'));
		$this->assertEquals($expected, $finder->findDirReversed('one'));
		$this->assertEquals(array($expected), $finder->findAllDirs('one'));
		$this->assertEquals(array($expected), $finder->findAllDirsReversed('one'));
	}

	public function testConstructor()
	{
		$finder = new Finder(array(
			__DIR__.'/../resources/three/',
		), 'txt');

		$expected = realpath(__DIR__.'/../resources/three/a.txt');
		$this->assertEquals($expected, $finder->findFile('a'));
	}

	/**
	 * @expectedException  Exception
	 */
	public function testRoot()
	{
		$f = new Finder();
		$f->setRoot(__DIR__.'/../resources');
		$expected = realpath(__DIR__.'/../resources');
		$this->assertEquals($expected, $f->getRoot());
		$f->addPath(__DIR__);
	}

	/**
	 * @expectedException  Exception
	 */
	public function testInvalidRoot()
	{
		$f = new Finder;
		$f->setRoot('not a path');
	}

	public function testFindHandler()
	{
		$f = new Finder;
		$f->addPath(__DIR__.'/../resources/one');
		$f->returnHandlers();

		$this->assertInstanceOf('FuelPHP\FileSystem\File', $f->findFile('a'));

		$f = new Finder;
		$f->addPath(__DIR__.'/../resources/one');
		$f->addPath(__DIR__.'/../resources');
		$f->asHandlers();
		$this->assertInstanceOf('FuelPHP\FileSystem\File', $f->findFile('a'));
		$f->asHandlers();
		$this->assertInstanceOf('FuelPHP\FileSystem\Directory', $f->findDir('one'));
		$f->returnHandlers();
		$this->assertContainsOnlyInstancesOf('FuelPHP\FileSystem\File', $f->findAllFiles('a'));
		$this->assertContainsOnlyInstancesOf('FuelPHP\FileSystem\Directory', $f->findAllDirs('one'));
	}
}