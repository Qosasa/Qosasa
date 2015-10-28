<?php namespace Qosasa\Core;

use League\Flysystem\File;
use Qosasa\Core\IO\JSONReadWrite;
use Qosasa\Core\Exceptions\SettingsException;
use Qosasa\Core\Exceptions\JSONReadWriteException;


class Settings {

	/**
	 * The settings file.
	 * 
	 * @var League\Flysystem\File
	 */
	private $file;

	/**
	 * The settings data.
	 * 
	 * @var array
	 */
	private $data;

	/**
	 * Creates a settings instance based on a file.
	 * 
	 * @param  League\Flysystem\File $file
	 * @return void
	 */
	public function __construct(File $file)
	{
		$this->file = $file;
		$this->load();
	}

	/**
	 * Loads data from the file.
	 *
	 * @return void
	 * @throws Qosasa\Core\Exceptions\SettingsException
	 */
	public function load()
	{
		try {
			$this->data = JSONReadWrite::read($this->file);
		} catch (JSONReadWriteException $e) {
			throw new SettingsException($e->getMessage());
		}
		$this->fillMissing();
	}

	/**
	 * Saves the settings to the file.
	 * 
	 * @return void
	 * @throws Qosasa\Core\Exceptions\SettingsException
	 */
	public function save()
	{
		try {
			JSONReadWrite::write($this->data, $this->file);
		} catch (JSONReadWriteException $e){
			throw new SettingsException("Cannot save settings");
		}
	}

	/**
	 * fills missing settings with default values.
	 * 
	 * @return void
	 */
	private function fillMissing()
	{
		$defaultData = [
			'packagesDir' => '~/.qosasa/packages',
			'aliases' => new \stdClass
		];

		if ($this->data === null) {
			$this->data = new \stdClass;
		}

		foreach ($defaultData as $key => $value) {
			if (! isset($this->data->$key)) {
				$this->data->$key = $value;
			}
		}
	}

	/**
	 * Sets a setting value.
	 * 
	 * @param String $name
	 * @param mixed $value
	 * @return void
	 */
	public function set($name, $value)
	{
		$this->data->$name = $value;
	}

	/**
	 * Gets a setting value or null if not exists.
	 * 
	 * @param  String $name
	 * @return mixed
	 */
	public function get($name)
	{
		if ($this->has($name)) {
			return $this->data->$name;
		}

		return null;
	}

	/**
	 * Returns true if setting exists.
	 * 
	 * @param  string  $name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset($this->data->$name);
	}

}
