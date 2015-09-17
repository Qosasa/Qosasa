<?php namespace Qosasa\Core\Format\Providers;

use Qosasa\Core\Exceptions\FormatProviderException;
use Qosasa\Core\Format\FormatProviderInterface;
use League\Flysystem\File;
use Qosasa\Core\IO\JSONReadWrite;
use Qosasa\Core\Exceptions\JSONReadWriteException;


class JSONFormatProvider implements FormatProviderInterface {

    /**
     * The format file instance
     * 
     * @var \League\Flysystem\File
     */
    protected $file;

     /**
     * Create a format provider for JSON files
     *
     * @param  \League\Flysystem\File $file the format file
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
     * @throws Qosasa\Core\Exceptions\FormatProviderException
     */
    public function getFormat()
    {
        try {
            $json = JSONReadWrite::read($this->file);        
        } catch(JSONReadWriteException $e){
            throw new FormatProviderException($e->getMessage());
        }

        return $json;
    }

}
