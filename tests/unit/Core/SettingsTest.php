<?php

use Mockery as m;
use Qosasa\Core\Settings;
use Qosasa\Core\Exceptions\SettingsException;


class SettingsTest extends PHPUnit_Framework_TestCase {

    private function getFileMock($content, $newContent = '')
    {
        $fileMock = m::mock('League\Flysystem\File');
        $fileMock->shouldReceive('read')->once()->andReturn($content, $newContent);
        $fileMock->shouldReceive('getPath')->once()->andReturn('path/to/file');
        $fileMock->shouldReceive('write')->once()->andReturn(true);
        $fileMock->shouldReceive('put')->once()->andReturn(true);
        return $fileMock;
    }

    public function testLoadEmptyFile()
    {
        $file = $this->getFileMock('');
        $setts = new Settings($file);
        $this->assertEquals($setts->get('packagesDir'), '~/.qosasa/packages');
        $this->assertEquals($setts->get('aliases'), new \stdClass);
    }

    /**
     * @expectedException Qosasa\Core\Exceptions\SettingsException
     * @expectedExceptionMessage Error parsing JSON file: path/to/file
     */
    public function testLoadFileHavingErrors()
    {
        $file = $this->getFileMock('{"packagesDir":"",');
        $setts = new Settings($file);
    }

    public function testGet()
    {
        $file = $this->getFileMock('{"packagesDir":"/home/awesome/packages","aliases":{"short":"original"}}');
        $setts = new Settings($file);
        $this->assertEquals($setts->get('packagesDir'), "/home/awesome/packages");
        $aliases = new \stdClass;
        $aliases->short = 'original';
        $this->assertEquals($setts->get('aliases'), $aliases);
    }

    public function testHas()
    {
        $file = $this->getFileMock('{"packagesDir":"/home/awesome/packages","aliases":{"short":"original"}}');
        $setts = new Settings($file);
        $this->assertTrue($setts->has('packagesDir'));
        $this->assertTrue($setts->has('aliases'));
        $this->assertFalse($setts->has('aliass'));
    }

    public function testSet()
    {
        $file = $this->getFileMock('{"packagesDir":"/home/awesome/packages","aliases":{"short":"original"}}');
        $setts = new Settings($file);
        $aliases = new \stdClass;
        $aliases->short = 'other.original';
        $setts->set('packagesDir', '/my/new/path');
        $setts->set('aliases', $aliases);
        $this->assertEquals($setts->get('packagesDir'), "/my/new/path");
        $this->assertEquals($setts->get('aliases'), $aliases);
    }

    public function testSave()
    {
        $file = $this->getFileMock('{"packagesDir":"/home/awesome/packages","aliases":{"short":"original"}}', '{"packagesDir":"/my/new/path","aliases":{"short":"other.original"}}');
        $setts = new Settings($file);
        $aliases = new \stdClass;
        $aliases->short = 'other.original';
        $setts->set('packagesDir', '/my/new/path');
        $setts->set('aliases', $aliases);
        $setts->save();
        $setts->load();
        $this->assertEquals($setts->get('packagesDir'), "/my/new/path");
        $this->assertEquals($setts->get('aliases'), $aliases);
    }

}
