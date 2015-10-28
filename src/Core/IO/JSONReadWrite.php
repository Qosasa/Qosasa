<?php namespace Qosasa\Core\IO;

use Qosasa\Core\Exceptions\JSONReadWriteException;
use League\Flysystem\File;


class JSONReadWrite {


	/**
	 * Reads a file and parse the content as JSON.
	 * 
	 * @param  League\Flysystem\File $file
	 * @return object
	 * @throws Qosasa\Core\Exceptions\JSONReadWriteException
	 */
	public static function read(File $file)
	{
		try {
		    $content = $file->read();
		} catch(\Exception $e) {
		    throw new JSONReadWriteException("JSON file not found at: " . $file->getPath());
		}

		$json = json_decode($content);
		
		if (json_last_error() !== JSON_ERROR_NONE) {
		    throw new JSONReadWriteException("Error parsing JSON file: " . $file->getPath());
		}

		return $json;
	}

	/**
	 * Creates or updates the file with the JSON data.
	 * 
	 * @param  mixed $data
	 * @param  League\Flysystem\File $file
	 * @return void
	 * @throws Qosasa\Core\Exceptions\JSONReadWriteException
	 */
	public static function write($data, File $file)
	{
		$json = json_encode($data);

		if ($json === false)
			throw new JSONReadWriteException("Cannot encode the data to JSON");

		$file->put($json);
	}

}
