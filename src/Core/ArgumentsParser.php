<?php namespace Qosasa\Core;

use Qosasa\Core\Format;
use Qosasa\Core\Exceptions\ArgumentsParserException;

class ArgumentsParser {

    /**
     * The format to use to parse arguments.
     * 
     * @var Qosasa\Core\Format
     */
    protected $format;

    /**
     * Create an arguments parser instance.
     *  
     * @param   Qosasa\Core\Format $format
     * @return  void
     */
    public function __construct(Format $format)
    {
        $this->format = $format;
    }

    /**
     * Parses arguments using the format and returns the corresponding data.
     * 
     * @param  string $args
     * @return array
     * @throws Qosasa\Core\Exceptions\ArgumentsParserException if arguments contain an unknown flag
     */
    public function parse($args)
    {
        $result = [
            'data' => null,
            'flags' => []
        ];
        // Initializing flags
        foreach( $this->format->flags as $flag ) {
            $result['flags'][$flag] = false;
        }
        $tokens = $args;
        // Retrieve flags from the arguments
        // arguments: tokens [--flag ...]
        if( count($this->format->flags) > 0 ) {
            $args = explode(' ', $args);
            $i = count($args) - 1;
            while( $i > 0 ) {
                if( '--' == substr($args[$i], 0, 2) ) {
                    $flag = substr($args[$i], 2);
                    if( ! in_array($flag, $this->format->flags) ) {
                        throw new ArgumentsParserException("Unknown flag '{$flag}'");
                    }
                    $result['flags'][$flag] = true;
                    $i --;
                } else {
                    break;
                }
            }
            $tokens = implode(' ', array_slice($args, 0, $i + 1));
        }
        // Parsing tokens
        $result['data'] = $this->parseToken($tokens, $this->format);

        return $result;
    }

    /**
     * Parses the token using the given format.
     * 
     * @param  string $token 
     * @param  Qosasa\Core\Format $format 
     * @return mixed 
     * @throws Qosasa\Core\Exceptions\ArgumentsParserException if the type of $format is unknown
     */
    public function parseToken($token, Format $format){
        switch($format->type) {
            case 'string':
                return $token;
            case 'number':
                return $this->parseNumber($token);
            case 'boolean':
                return $this->parseBoolean($token, $format->name);
            case 'array':
                return $this->parseArray($token, $format->separator, $format->format);
            case 'object':
                return $this->parseObject($token, $format->separator, $format->format);
            default:
                throw new ArgumentsParserException("Unknown format type: '{$format->type}'");
        }
    }

    /**
     * Parses the token as a number.
     * 
     * @param  string $token 
     * @return int|double 
     * @throws Qosasa\Core\Exceptions\ArgumentsParserException if the token is not numeric
     */
    protected function parseNumber($token)
    {
        if( ! is_numeric($token) ) {
            throw new ArgumentsParserException("Unable to parse '{$token}' as number");
        }
        return $token + 0;
    }

    /**
     * Parses the token as a boolean. Returns null if token does not have a valid boolean value.
     * 
     * @param  string    $token
     * @param  string    $name
     * @return bool|null
     */
    protected function parseBoolean($token, $name)
    {
        if( in_array($token, ['yes', 'true', '1', $name]) ) {
            return true;
        } else if( in_array($token, ['no', 'false', '0', "!{$name}"]) ){
            return false;
        } else {
            return null;
        }
    }

    /**
     * Parses the token as an array.
     * 
     * @param  string $token
     * @param  string $separator
     * @param  Qosasa\Core\Format $format
     * @return array 
     */
    protected function parseArray($token, $separator, Format $format)
    {
        $result = [];
        $tokens = explode($separator, $token);
        foreach($tokens as $value) {
            array_push($result, $this->parseToken($value, $format));
        }
        return $result;
    }

    /**
     * Parses the token as an object.
     * 
     * @param  string $token
     * @param  string $separator 
     * @param  array  $fields
     * @return array
     * @throws Qosasa\Core\Exceptions\ArgumentsParserException if a requied field is not present
     */
    protected function parseObject($token, $separator, $fields)
    {
        $result = [];
        $tokens = explode($separator, $token);
        $tokensNumber = count($tokens);

        $requiredFieldsIndexes = [];
        $optionalFieldsIndexes = [];
        foreach($fields as $index => $format) {
            if( $format->default === null ) {
                array_push($requiredFieldsIndexes, $index);
            } else {
                array_push($optionalFieldsIndexes, $index);
            }
        }
        $requiredFieldsIndexesNumber = count($requiredFieldsIndexes);

        if( $tokensNumber < $requiredFieldsIndexesNumber ) {
            $requiredFields = array_map(function($index) use ($fields) {
                    return $fields[$index]->name;
                }, $requiredFieldsIndexes);
            $requiredFields = implode($separator, $requiredFields);
            throw new ArgumentsParserException("Required field missing: {$tokensNumber} given "
                . "({$token}) but {$requiredFieldsIndexesNumber} required ({$requiredFields})");
        }

        $givenOptionalFieldsIndexes = array_slice(
            $optionalFieldsIndexes, 0, $tokensNumber - $requiredFieldsIndexesNumber);
        $notPresentFieldsIndexes = array_slice(
            $optionalFieldsIndexes, $tokensNumber - $requiredFieldsIndexesNumber);
        $givenFieldsIndexes = array_merge($requiredFieldsIndexes, $givenOptionalFieldsIndexes);
        sort($givenFieldsIndexes);

        // Fill the given fields
        for( $i = 0; $i < $tokensNumber; $i ++) {
            $fieldFormat = $fields[$givenFieldsIndexes[$i]];
            $result[$fieldFormat->name] = $this->parseToken($tokens[$i], $fieldFormat);
        }

        // Fill other fields with default values
        foreach( $notPresentFieldsIndexes as $index ) {
            $fieldFormat = $fields[$index];
            $result[$fieldFormat->name] = $fieldFormat->default;
        }

        return $result;
    }

}
