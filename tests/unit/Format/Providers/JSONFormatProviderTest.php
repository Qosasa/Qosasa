<?php

use Mockery as m;
use Qosasa\Core\Format\Providers\JSONFormatProvider;
use League\Flysystem\FileNotFoundException;


class JSONFormatProviderTest extends PHPUnit_Framework_TestCase {

    public function testGetFormat()
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('read')->once()->andReturn('{"data": 11}');
        $formatProvider = new JSONFormatProvider($fileMock);
        $format = $formatProvider->getFormat();
        $this->assertObjectHasAttribute('data', $format);
        $this->assertEquals($format->data, 11);
    }

    /**
     * @expectedException Qosasa\Core\Exceptions\FormatProviderException
     * @expectedExceptionMessage JSON file not found at: path/format.json
     */
    public function testFileNotFound()
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('getPath')->once()->andReturn('path/format.json');
        $fileMock->shouldReceive('read')->once()->andThrow(new FileNotFoundException("msg"));
        $formatProvider = new JSONFormatProvider($fileMock);
        $format = $formatProvider->getFormat();
    }

    /**
     * @expectedException Qosasa\Core\Exceptions\FormatProviderException
     * @expectedExceptionMessage Error parsing JSON file
     */
    public function testJSONError()
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('read')->once()->andReturn('data: 11}');
        $formatProvider = new JSONFormatProvider($fileMock);
        $format = $formatProvider->getFormat();
    }

}
