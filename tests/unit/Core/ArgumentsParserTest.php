<?php

use Qosasa\Core\ArgumentsParser;
use Qosasa\Core\Format;
use Qosasa\Core\Exceptions\InvalidArguments;

class ArgumentsParserTest extends PHPUnit_Framework_TestCase {

    public function testParsingArgumentWithStringFormat(){
        $format = new Format;
        $format->type = 'string';
        $format->flags = ['flagA', 'flagB'];

        $parser = new ArgumentsParser($format);
        
        $data = $parser->parse('foo:bar,zoo');
        $this->assertEquals([
            'data' => 'foo:bar,zoo',
            'flagA' => false,
            'flagB' => false
        ], $data);

        $data = $parser->parse('some-text --flagB');
        $this->assertEquals([
            'data' => 'some-text',
            'flagA' => false,
            'flagB' => true
        ], $data);

        $data = $parser->parse('some-text --flagB --flagA');
        $this->assertEquals([
            'data' => 'some-text',
            'flagA' => true,
            'flagB' => true
        ], $data);

    }

    public function testParsingNumericArgumentWithNumberFormat(){
        $format = new Format;
        $format->type = 'number';

        $parser = new ArgumentsParser($format);
        
        $data = $parser->parse('0123');
        $this->assertEquals([ 'data' => 123 ], $data);

        $data = $parser->parse('1.75');
        $this->assertEquals([ 'data' => 1.75 ], $data);
    }

    /**
     * @expectedException InvalidArguments
     */
    public function testParsingNonNumericArgumentWithNumberFormat(){
        $format = new Format;
        $format->type = 'number';

        $parser = new ArgumentsParser($format);
        
        $data = $parser->parse('0123');
        $this->assertEquals([ 'data' => 123 ], $data);

        $data = $parser->parse('1.75');
        $this->assertEquals([ 'data' => 1.75 ], $data);
    }

    public function testParsingArgumentsWithSimpleArrayFormat(){
        $format = new Format;
        $format->type = 'array';
        $format->separator = ',';
        $format->format = new Format;
        $format->format->type = 'string';
        
        $parser = new ArgumentsParser($format);
        $data = $parser->parse('foo,bar:one,,bar:two');
        $this->assertEquals([ 
            'data' => ['foo', 'bar:one', '', 'bar:two']
        ], $data);
    }

    public function testParsingArgumentsWithSimpleObjectFormat(){
        // $parser = new ArgumentsParser;
    }


}