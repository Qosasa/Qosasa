<?php namespace Qosasa\Core\Format\Providers;

use Qosasa\Core\Exceptions\FormatProviderException;
use Qosasa\Core\Format\FormatProviderInterface;
use League\Flysystem\File;
use League\Flysystem\FileNotFoundException;


class JSONFormatProvider implements FormatProviderInterface {

    /**
     * Create a format provider for JSON files
     *
     * @param  \League\Flysystem\File  $file
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Return format object
     *
     * @return object
     */
    public function getFormat()
    {
        
        try {
            $content = $this->file->read();
        } catch(FileNotFoundException $e) {
            throw new FormatProviderException("JSON file not found at: ".$this->file->getPath());
        }

        $json = json_decode($content);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new FormatProviderException("Error parsing JSON file");
        }

        return $json;
    }

}
