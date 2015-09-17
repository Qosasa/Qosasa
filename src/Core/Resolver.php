<?php namespace Qosasa\Core;

use Qosasa\Core\Exceptions\ResolverException;
use League\Flysystem\Filesystem;


class Resolver {

	/**
	 * Aliases.
	 * 
	 * @var stdClass
	 */
	private $aliases;

	/**
	 * Packages filesystem.
	 * 
	 * @var League\Flysystem\Filesystem
	 */
	private $fs;

	/**
	 * Array of snippets
	 * Ex: [
	 * 	'snippetA' => ['package1', 'package2'],
	 * 	...
	 * ]
	 * 
	 * @var array
	 */
	private $snippets;

	/**
	 * Creates a resolver instance.
	 * 
	 * @param League\Flysystem\Filesystem $fs
	 * @param array $aliases
	 * @return void
	 */
	public function __construct(Filesystem $fs, $aliases = null)
	{
		$this->fs = $fs;
		$this->aliases = $aliases;
		$this->fillSnippets();
	}

	/**
	 * Gets full path to the snippet directory by name or alias.
	 * 
	 * @param  string $name
	 * @return string
	 * @throws Qosasa\Core\Exceptions\ResolverException
	 */
	public function resolve($name)
	{
		$snippetName = $packageName = null;

		if($this->aliases != null && isset($this->aliases->{$name})) {
			$name = $this->aliases->{$name};
		}

		$name = explode('.', $name);
		if(count($name) == 1) {
			$snippetName = $name[0];
			if(empty($this->snippets) || ! isset($this->snippets[$snippetName])) {
				throw new ResolverException("Cannot find the snippet '{$snippetName}'");
			}
			if(count($this->snippets[$snippetName]) > 1) {
				throw new ResolverException("Conflict: the snippet '{$snippetName}' exists in multiple packages. Please specify the package !");
			}
			$packageName = reset($this->snippets[$snippetName]);
		} else if(count($name) == 2) {
			$snippetName = $name[1];
			$packageName = $name[0];
			if(empty($this->snippets) || ! isset($this->snippets[$snippetName]) || ! in_array($packageName, $this->snippets[$snippetName])) {
				throw new ResolverException("Cannot find the snippet '{$snippetName}' on the package '{$packageName}'");
			}
		} else {
			$name = implode('.', $name);
			throw new ResolverException("Cannot resolve the name '{$name}'");
		}

		return $this->fs->getAdapter()->applyPathPrefix(
			$packageName . DIRECTORY_SEPARATOR . $snippetName
		);
	}

	/**
	 * Fills the snippets array.
	 * 
	 * @return void
	 */
	private function fillSnippets()
	{
		$this->snippets = [];
		$contents = $this->fs->listContents('', true);

		foreach ($contents as $item) {
			if($item['type'] === 'dir' && substr_count($item['path'], DIRECTORY_SEPARATOR) == 1) {
				$packageName = $item['dirname'];
				$snippetName = $item['basename'];
				if(! array_key_exists($snippetName, $this->snippets)) {
					$this->snippets[$snippetName] = [];
				}
				$this->snippets[$snippetName][] = $packageName;
			}
		}
	}

}
