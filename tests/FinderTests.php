<?php

use FuelPHP\FileSystem\Finder;

class FinderTests extends PHPUnit_Framework_TestCase
{
	public function testFindFile()
	{
		$finder = new Finder();
		$finder->setPaths(array(__DIR__.'/../resources/one'));

		$this->assertNull($finder->find('null'));

		$this->assertEquals(__DIR__.'/../resources/one/a.php', $finder->find('a'));

		$this->assertEquals(array(
			__DIR__.'/../resources/one/a.php'
		), $finder->findAll('a'));

		$this->assertEquals(array(
			__DIR__.'/../resources/one/a.php'
		), $finder->findAll('a'));

		$finder->addPaths(array(
			__DIR__.'/../resources/two',
			__DIR__.'/../resources/three',
		));

		$this->assertEquals(array(
			__DIR__.'/../resources/three/a.php',
			__DIR__.'/../resources/one/a.php',
		), $finder->findAllReversed('a'));

		$this->assertEquals(__DIR__.'/../resources/three/a.php', $finder->findReversed('a'));
		$this->assertEquals(__DIR__.'/../resources/three/a.txt', $finder->findReversed('a.txt'));

		$finder->setDefaultExtension('txt');
		$finder->clearCache();
		$this->assertEquals(array(
			__DIR__.'/../resources/two/a.txt',
			__DIR__.'/../resources/three/a.txt',
		), $finder->findAll('a'));

		$this->assertEquals(array(
			__DIR__.'/../resources/one/',
			__DIR__.'/../resources/two/',
			__DIR__.'/../resources/three/',
		), $finder->getPaths());

		$this->assertEquals(__DIR__.'/../resources/one/a.php', $finder->find('a.php'));
		$this->assertEquals(__DIR__.'/../resources/one/a.php', $finder->find('a.php'));
		$finder->removePaths(array(__DIR__.'/../resources/one'));
		$this->assertEquals(__DIR__.'/../resources/three/a.php', $finder->find('a.php'));
	}
}