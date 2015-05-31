<?php

use Mockery as m;
use Qosasa\Core\Format\FormatLoader;


class FormatLoaderTest extends PHPUnit_Framework_TestCase {

    public function testLoadFormat()
    {
        $formatLoader = $this->getFormatLoader('json');
        $format = $formatLoader->load();

        $this->assertObjectHasAttribute('data', $format);
        $this->assertEquals($format->data, 11);
    }

    /**
     * @expectedException Qosasa\Core\Exceptions\FormatProviderResolverException
     */
    public function testUnkownFormatProvider()
    {
        $formatLoader = $this->getFormatLoader('unkown');   
        $format = $formatLoader->load();

        $this->assertObjectHasAttribute('data', $format);
        $this->assertEquals($format->data, 11);
    }

    public function getFormatLoader($providerName)
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('read')->once()->andReturn('{"data": 11}');

        $snippetMock = m::mock('Qosasa\Core\Snippet');
        $snippetMock->shouldReceive('getFormatFile')->once()->andReturn($fileMock);
        $snippetMock->shouldReceive('getProvider')->once()->andReturn($providerName);

        $formatInflaterMock = m::mock('Qosasa\Core\Format\FormatInflater');
        $formatInflaterMock->shouldReceive('inflate')->once()->andReturnUsing(function ($arg) {
            return $arg;
        });

        $formatLoader = new FormatLoader($snippetMock, $formatInflaterMock);

        return $formatLoader;
    }

}
