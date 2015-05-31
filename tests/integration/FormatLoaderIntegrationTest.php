<?php

use Mockery as m;
use Qosasa\Core\Format\FormatLoader;
use Qosasa\Core\Format\FormatInflater;


class FormatLoaderIntegrationTest extends PHPUnit_Framework_TestCase {

    public function testLoadFormat()
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('read')->once()->andReturn('{
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
        }');

        $snippetMock = m::mock('Qosasa\Core\Snippet');
        $snippetMock->shouldReceive('getFormatFile')->once()->andReturn($fileMock);
        $snippetMock->shouldReceive('getProvider')->once()->andReturn('json');

        $formatInflater = new FormatInflater;
        
        $formatLoader = new FormatLoader($snippetMock, $formatInflater);

        $format = $formatLoader->load();

        $this->assertInstanceOf('Qosasa\Core\Format', $format);
        $this->assertEquals(json_encode($format), '{"name":null,"type":"object","default":null,"separator":":","format":[{"name":"name","type":"string","default":null,"separator":",","format":null,"flags":null},{"name":"parents","type":"string","default":null,"separator":",","format":null,"flags":null},{"name":"interfaces","type":"string","default":null,"separator":",","format":null,"flags":null},{"name":"attrs","type":"object","default":null,"separator":".","format":[{"name":"name","type":"string","default":null,"separator":",","format":null,"flags":null},{"name":"type","type":"string","default":null,"separator":",","format":null,"flags":null},{"name":"static","type":"boolean","default":false,"separator":null,"format":null,"flags":[]},{"name":"hasGetter","type":"boolean","default":true,"separator":null,"format":null,"flags":[]},{"name":"hasSetter","type":"boolean","default":true,"separator":null,"format":null,"flags":[]}],"flags":[]}],"flags":[]}');
    }

}
