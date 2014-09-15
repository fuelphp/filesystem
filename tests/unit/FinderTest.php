<?php

namespace Fuel\FileSystem;

use Codeception\TestCase\Test;

class FinderTest extends Test
{

	public function testFindFile()
	{
		$base = realpath(__DIR__.'/../resources/');

		// TODO: Convert these tests to use vfs once that's hhvm compatible
		$baseFiles = array(
			'a' => $base.DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR.'a.php',
			'1a' => $base.DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR.'a.php',
			'2a.txt' => $base.DIRECTORY_SEPARATOR.'two'.DIRECTORY_SEPARATOR.'a.txt',
			'3a' => $base.DIRECTORY_SEPARATOR.'three'.DIRECTORY_SEPARATOR.'a.php',
			'3a.txt' => $base.DIRECTORY_SEPARATOR.'three'.DIRECTORY_SEPARATOR.'a.txt',
		);

		$finder = new Finder();
		$this->assertEquals(array(), $finder->getPaths());
		$finder->setPaths(array($base.'/one'));
		$this->assertEquals(array('__DEFAULT__'), $finder->getGroups());
		$this->assertEquals(array(), $finder->findAllFiles('group::a'));
		$this->assertNull($finder->findFile('group::a'));

		$this->assertNull($finder->findFile('null'));

		$this->assertEquals(
			$baseFiles['a'],
			$finder->findFile('a')
		);

		$this->assertEquals(array(
			$baseFiles['a']
		), $finder->findAllFiles('a'));

		$this->assertEquals(array(
			$baseFiles['a']
		), $finder->findAllFiles('a'));

		$finder->addPaths(array(
			$base.'/two',
			$base.'/three',
		));

		$this->assertEquals(array(
			$baseFiles['3a'],
			$baseFiles['1a'],
		), $finder->findAllFilesReversed('a'));

		$this->assertEquals($baseFiles['3a'], $finder->findFileReversed('a'));
		$this->assertEquals($baseFiles['3a.txt'], $finder->findFileReversed('a.txt'));

		$finder->setDefaultExtension('txt');
		$finder->clearCache();
		$this->assertEquals(array(
			$baseFiles['2a.txt'],
			$baseFiles['3a.txt'],
		), $finder->findAllFiles('a'));

		$this->assertEquals(array(
			$baseFiles['3a.txt'],
			$baseFiles['2a.txt'],
		), $finder->findAllReversed('a'));

		$finder->asHandlers();
		$finder->addPath(__DIR__.'/../resources');
		$this->assertContainsOnlyInstancesOf('Fuel\FileSystem\File', $finder->findAll('a'));
		$finder->asHandler();
		$this->assertContainsOnlyInstancesOf('Fuel\FileSystem\Directory', $finder->findAll('one'));

		$finder->removePath(__DIR__.'/../resources');

		$this->assertEquals(array(
			$base.DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR,
			$base.DIRECTORY_SEPARATOR.'two'.DIRECTORY_SEPARATOR,
			$base.DIRECTORY_SEPARATOR.'three'.DIRECTORY_SEPARATOR,
		), $finder->getPaths());

		$this->assertEquals($baseFiles['1a'], $finder->findFile('a.php'));
		$this->assertEquals($baseFiles['1a'], $finder->findFile('a.php'));
		$finder->removePaths(array($base.'/one'));
		$this->assertEquals($baseFiles['3a'], $finder->findFile('a.php'));

		$finder->addPath(__DIR__.'/../resources/');
		$expected = $base.DIRECTORY_SEPARATOR.'one';
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
	 * @expectedException \Exception
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
	 * @expectedException \Exception
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

		$this->assertInstanceOf('Fuel\FileSystem\File', $f->findFile('a'));

		$f = new Finder;
		$f->addPath(__DIR__.'/../resources/one');
		$f->addPath(__DIR__.'/../resources');
		$f->asHandlers();
		$this->assertInstanceOf('Fuel\FileSystem\File', $f->findFile('a'));
		$f->asHandlers();
		$this->assertInstanceOf('Fuel\FileSystem\Directory', $f->findDir('one'));
		$f->returnHandlers();
		$this->assertContainsOnlyInstancesOf('Fuel\FileSystem\File', $f->findAllFiles('a'));
		$this->assertContainsOnlyInstancesOf('Fuel\FileSystem\Directory', $f->findAllDirs('one'));
	}

}
