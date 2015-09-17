<?php

use Mockery as m;
use Qosasa\Core\Exceptions\ResolverException;
use Qosasa\Core\Resolver;


class ResolverTest extends PHPUnit_Framework_TestCase {

	private function makeObjectFromArray($array)
	{
		$obj = new stdClass;
		foreach ($array as $key => $value) {
		 	$obj->{$key} = $value;
		}
		return $obj;
	}

	private function getFileSystemMock($directories)
	{
		$adapterMock = m::mock('League\Flysystem\Adapter\Local');
		$adapterMock->shouldReceive('applyPathPrefix')
			->with(m::any())
			->andReturnUsing(function($path){
				return '~/.qosasa/packages/' . $path;
			});

		$fsMock = m::mock('League\Flysystem\Filesystem');
		$fsMock->shouldReceive('listContents')
			->andReturn($directories);
		$fsMock->shouldReceive('getAdapter')
			->andReturn($adapterMock);

		return $fsMock;
	}

	/**
	 * Testing packages folder:
	 * 
	 * cpp/
	 * 	 class/
	 * 		format.xml
	 * 		template.twig
	 * 	 struct/
	 * 		format.xml
	 * 		template.twig
	 * js/
	 * 	 contains/
	 * 		format.yaml
	 * 		template.twig
	 * php/
	 * 	 class/
	 * 		format.json
	 * 		template.twig
	 * 	 contains/
	 * 		format.json
	 * 		some-ignored-file.txt
	 * 		template.blade
	 * 	 some-ignored-file.txt
	 * some-ignored-file.txt
	 * 
	 */
	private function getTestingFileSystemMock()
	{
		return $this->getFileSystemMock([
			[
			    'type' => 'dir',
			    'path' => 'js',
			    'timestamp' => '1442516680',
			    'dirname' => '',
			    'basename' => 'js',
			    'filename' => 'js'
			],
			[
			    'type' => 'dir',
			    'path' => 'js/contains',
			    'timestamp' => '1442516680',
			    'dirname' => 'js',
			    'basename' => 'contains',
			    'filename' => 'contains'
			],
			[
			    'type' => 'file',
			    'path' => 'js/contains/template.twig',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'js/contains',
			    'basename' => 'template.twig',
			    'extension' => 'twig',
			    'filename' => 'template'
			],
			[
			    'type' => 'file',
			    'path' => 'js/contains/format.yaml',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'js/contains',
			    'basename' => 'format.yaml',
			    'extension' => 'yaml',
			    'filename' => 'format'
			],
			[
			    'type' => 'dir',
			    'path' => 'cpp',
			    'timestamp' => '1442516680',
			    'dirname' => '',
			    'basename' => 'cpp',
			    'filename' => 'cpp'
			],
			[
			    'type' => 'dir',
			    'path' => 'cpp/class',
			    'timestamp' => '1442516680',
			    'dirname' => 'cpp',
			    'basename' => 'class',
			    'filename' => 'class'
			],
			[
			    'type' => 'file',
			    'path' => 'cpp/class/format.xml',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'cpp/class',
			    'basename' => 'format.xml',
			    'extension' => 'xml',
			    'filename' => 'format'
			],
			[
			    'type' => 'file',
			    'path' => 'cpp/class/template.twig',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'cpp/class',
			    'basename' => 'template.twig',
			    'extension' => 'twig',
			    'filename' => 'template'
			],
			[
			    'type' => 'dir',
			    'path' => 'cpp/struct',
			    'timestamp' => '1442516680',
			    'dirname' => 'cpp',
			    'basename' => 'struct',
			    'filename' => 'struct'
			],
			[
			    'type' => 'file',
			    'path' => 'cpp/struct/format.xml',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'cpp/struct',
			    'basename' => 'format.xml',
			    'extension' => 'xml',
			    'filename' => 'format'
			],
			[
			    'type' => 'file',
			    'path' => 'cpp/struct/template.twig',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'cpp/struct',
			    'basename' => 'template.twig',
			    'extension' => 'twig',
			    'filename' => 'template'
			],
			[
			    'type' => 'file',
			    'path' => 'some-ignored-file.txt',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => '',
			    'basename' => 'some-ignored-file.txt',
			    'extension' => 'txt',
			    'filename' => 'some-ignored-file'
			],
			[
			    'type' => 'dir',
			    'path' => 'php',
			    'timestamp' => '1442516680',
			    'dirname' => '',
			    'basename' => 'php',
			    'filename' => 'php'
			],
			[
			    'type' => 'dir',
			    'path' => 'php/class',
			    'timestamp' => '1442516680',
			    'dirname' => 'php',
			    'basename' => 'class',
			    'filename' => 'class'
			],
			[
			    'type' => 'file',
			    'path' => 'php/class/format.json',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php/class',
			    'basename' => 'format.json',
			    'extension' => 'json',
			    'filename' => 'format'
			],
			[
			    'type' => 'file',
			    'path' => 'php/class/template.twig',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php/class',
			    'basename' => 'template.twig',
			    'extension' => 'twig',
			    'filename' => 'template'
			],
			[
			    'type' => 'dir',
			    'path' => 'php/contains',
			    'timestamp' => '1442516680',
			    'dirname' => 'php',
			    'basename' => 'contains',
			    'filename' => 'contains'
			],
			[
			    'type' => 'file',
			    'path' => 'php/contains/template.blade',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php/contains',
			    'basename' => 'template.blade',
			    'extension' => 'blade',
			    'filename' => 'template'
			],
			[
			    'type' => 'file',
			    'path' => 'php/contains/format.json',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php/contains',
			    'basename' => 'format.json',
			    'extension' => 'json',
			    'filename' => 'format'
			],
			[
			    'type' => 'file',
			    'path' => 'php/contains/some-ignored-file.txt',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php/contains',
			    'basename' => 'some-ignored-file.txt',
			    'extension' => 'txt',
			    'filename' => 'some-ignored-file'
			],
			[
			    'type' => 'file',
			    'path' => 'php/some-ignored-file.txt',
			    'timestamp' => '1442516680',
			    'size' => '0',
			    'dirname' => 'php',
			    'basename' => 'some-ignored-file.txt',
			    'extension' => 'txt',
			    'filename' => 'some-ignored-file'
			]
		]);
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Cannot find the snippet 'foo'
     */
	public function testResolveSnippetNameWithNoPackage()
	{
		$resolver = new Resolver($this->getFileSystemMock([]));
		$path = $resolver->resolve('foo');
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Cannot find the snippet 'bar' on the package 'foo'
     */
	public function testResolveFullNameWithNoPackage()
	{
		$resolver = new Resolver($this->getFileSystemMock([]));
		$path = $resolver->resolve('foo.bar');
	}

	public function testResolveFullName()
	{
		$resolver = new Resolver($this->getTestingFileSystemMock());

		$this->assertEquals(
			'~/.qosasa/packages/php/class',
			$resolver->resolve('php.class')
		);
		$this->assertEquals(
			'~/.qosasa/packages/cpp/class',
			$resolver->resolve('cpp.class')
		);
	}

	public function testResolveWithAlias()
	{
		$resolver = new Resolver(
			$this->getTestingFileSystemMock(), 
			$this->makeObjectFromArray([
				'cc' => 'cpp.class',
				'jsc' => 'js.contains'
			])
		);

		$this->assertEquals(
			'~/.qosasa/packages/cpp/class',
			$resolver->resolve('cc')
		);
		$this->assertEquals(
			'~/.qosasa/packages/js/contains',
			$resolver->resolve('jsc')
		);
	}

	public function testResolveSnippetName()
	{
		$resolver = new Resolver($this->getTestingFileSystemMock());

		$this->assertEquals(
			'~/.qosasa/packages/cpp/struct',
			$resolver->resolve('struct')
		);
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Conflict: the snippet 'class' exists in multiple packages. Please specify the package !
     */
	public function testResolveSnippetNameWithConflict()
	{
		$resolver = new Resolver($this->getTestingFileSystemMock());
		$resolver->resolve('class');
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Cannot find the snippet 'foo'
     */
	public function testResolveInexistantSnippet()
	{
		$resolver = new Resolver($this->getTestingFileSystemMock());
		$resolver->resolve('foo');
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Cannot find the snippet 'foo'
     */
	public function testResolveInexistantSnippetUsingAlias()
	{
		$resolver = new Resolver(
			$this->getTestingFileSystemMock(),
			$this->makeObjectFromArray([
				'a' => 'foo'
			])
		);
		$resolver->resolve('a');
	}

	/**
     * @expectedException Qosasa\Core\Exceptions\ResolverException
     * @expectedExceptionMessage Cannot find the snippet 'foo' on the package 'bar'
     */
	public function testResolveInexistantePackage()
	{
		$resolver = new Resolver($this->getTestingFileSystemMock());
		$resolver->resolve('bar.foo');
	}

}
