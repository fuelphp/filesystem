<?php

use FuelPHP\FileSystem\Finder;

class FinderTests extends PHPUnit_Framework_TestCase
{
	public function testFindFile()
	{
		$base = realpath(__DIR__.'/../resources/');
		$finder = new Finder();
		$finder->setPaths(array($base.'/one'));

		$this->assertNull($finder->find('null'));

		$this->assertEquals($base.'/one/a.php', $finder->find('a'));

		$this->assertEquals(array(
			$base.'/one/a.php'
		), $finder->findAll('a'));

		$this->assertEquals(array(
			$base.'/one/a.php'
		), $finder->findAll('a'));

		$finder->addPaths(array(
			$base.'/two',
			$base.'/three',
		));

		$this->assertEquals(array(
			$base.'/three/a.php',
			$base.'/one/a.php',
		), $finder->findAllReversed('a'));

		$this->assertEquals($base.'/three/a.php', $finder->findReversed('a'));
		$this->assertEquals($base.'/three/a.txt', $finder->findReversed('a.txt'));

		$finder->setDefaultExtension('txt');
		$finder->clearCache();
		$this->assertEquals(array(
			$base.'/two/a.txt',
			$base.'/three/a.txt',
		), $finder->findAll('a'));

		$this->assertEquals(array(
			$base.'/one/',
			$base.'/two/',
			$base.'/three/',
		), $finder->getPaths());

		$this->assertEquals($base.'/one/a.php', $finder->find('a.php'));
		$this->assertEquals($base.'/one/a.php', $finder->find('a.php'));
		$finder->removePaths(array($base.'/one'));
		$this->assertEquals($base.'/three/a.php', $finder->find('a.php'));
	}

	public function testConstructor()
	{
		$finder = new Finder(array(
			__DIR__.'/resources/three/',
		), 'txt');

		$expected = realpath(__DIR__.'/resources/three/a.txt');
		$this->assertEquals($expected, $finder->find('a'));
	}
}