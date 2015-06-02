<?php namespace Qosasa\Core;

use Qosasa\Core\Format;

/**
 * Parse the arguments based on the format
 * and provide the data to be passed to the template.
 */
class ArgumentsParser {

    /**
     * The format to use to parse arguments.
     * 
     * @var Format
     */
    protected $format;

    /**
     * Create an arguments parser instance.
     *  
     * @param   Format $format The format to use when parsing arguments
     * @return  void
     */
    public function __construct(Format $format){
        $this->format = $format;
    }

    /**
     * Parses arguments using the format and returns the corresponding data.
     * 
     * @param  string $args A string specifying the arguments
     * @return array        The data to pass to the template
     */
    public function parse($args){
        $data = [];
        
        return $data;
    }

}