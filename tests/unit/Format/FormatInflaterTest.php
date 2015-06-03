<?php

use Mockery as m;
use Qosasa\Core\Format\FormatInflater;


class FormatInflaterTest extends PHPUnit_Framework_TestCase {

    public function testParseNameArrayWithType()
    {
        $formatInflater = new FormatInflater;
        list($name, $type, $isArray) = $formatInflater->parseName('isStatic[boolean]');
        $this->assertEquals($name, 'isStatic');
        $this->assertEquals($type, 'boolean');
        $this->assertEquals($isArray, true);
    }

    public function testParseNameArrayWithoutType()
    {
        $formatInflater = new FormatInflater;
        list($name, $type, $isArray) = $formatInflater->parseName('isStatic[]');
        $this->assertEquals($name, 'isStatic');
        $this->assertEquals($type, null);
        $this->assertEquals($isArray, true);
    }

    public function testParseNameString()
    {
        $formatInflater = new FormatInflater;
        list($name, $type, $isArray) = $formatInflater->parseName('isStatic');
        $this->assertEquals($name, 'isStatic');
        $this->assertEquals($type, null);
        $this->assertEquals($isArray, false);
    }

    public function testFillString()
    {
        $formatInflater = new FormatInflater;
        $format = $formatInflater->fillString('isStatic[]', true);
        $this->assertEquals($format->name, 'isStatic');
        $this->assertEquals($format->type, 'array');
        $this->assertEquals($format->separator, ',');
    }

    public function testFillObject()
    {
        $formatInflater = new FormatInflater;
        $obj = (object) [
            "name"    => "isStatic",
            "type"    => "boolean",
            "default" => false
        ];
        $format = $formatInflater->fillObject($obj, true);
        $this->assertEquals($format->name, 'isStatic');
        $this->assertEquals($format->type, 'boolean');
        $this->assertEquals($format->default, false);
    }

    public function testInflateFormat()
    {
        $formatInflater = new FormatInflater;
        $inflatedFormat = $formatInflater->inflate(json_decode('{
            "type": "object",
            "fields": [
                "name",
                "parents[]",
                "interfaces[]",
                {
                    "name": "attrs[object]",
                    "separator": ".",
                    "fields": [
                        "name",
                        "type",
                        {
                            "name": "static", 
                            "type": "boolean",
                            "default": false
                        },
                        { 
                            "name": "hasGetter", 
                            "type": "boolean",
                            "default": true
                        },
                        { 
                            "name": "hasSetter", 
                            "type": "boolean",
                            "default": true
                        }
                    ]
                }
            ]
        }'));
        $this->assertEquals(json_encode($inflatedFormat), '{"name":null,"type":"object","default":null,"separator":":","format":[{"name":"name","type":"string","default":null,"separator":null,"format":null,"flags":null},{"name":"parents","type":"array","default":null,"separator":",","format":{"name":null,"type":"string","default":null,"separator":null,"format":null,"flags":null},"flags":null},{"name":"interfaces","type":"array","default":null,"separator":",","format":{"name":null,"type":"string","default":null,"separator":null,"format":null,"flags":null},"flags":null},{"name":"attrs","type":"object","default":null,"separator":".","format":[{"name":"name","type":"string","default":null,"separator":null,"format":null,"flags":null},{"name":"type","type":"string","default":null,"separator":null,"format":null,"flags":null},{"name":"static","type":"boolean","default":false,"separator":null,"format":null,"flags":null},{"name":"hasGetter","type":"boolean","default":true,"separator":null,"format":null,"flags":null},{"name":"hasSetter","type":"boolean","default":true,"separator":null,"format":null,"flags":null}],"flags":null}],"flags":null}');
    }

}
