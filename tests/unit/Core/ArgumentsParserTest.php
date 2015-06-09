<?php

use Qosasa\Core\ArgumentsParser;
use Qosasa\Core\Format;
use Qosasa\Core\Exceptions\ArgumentsParserException;


class ArgumentsParserTest extends PHPUnit_Framework_TestCase {
    
    protected function makeFormat($attrs)
    {
        $format = new Format;
        foreach ($attrs as $attr => $value) {
            $format->$attr = $value;
        }
        return $format;
    }

    public function testParseNumber()
    {
        $parser = new ArgumentsParser(new Format);        
        $numberFormat = $this->makeFormat(['type' => 'number']);

        $data = $parser->parseToken('0123', $numberFormat);
        $this->assertEquals(123, $data);

        $data = $parser->parseToken('1.75', $numberFormat);
        $this->assertEquals(1.75, $data);
    }

    /**
     * @expectedException Qosasa\Core\Exceptions\ArgumentsParserException
     * @expectedExceptionMessage Unable to parse 'non-numeric' as number
     */
    public function testParseWrongNumber()
    {
        $parser = new ArgumentsParser(new Format);        
        $numberFormat = $this->makeFormat(['type' => 'number']);
        $data = $parser->parseToken('non-numeric', $numberFormat);
    }

    public function testParseBoolean()
    {
        $parser = new ArgumentsParser(new Format);
        $booleanFormat = $this->makeFormat([
            'name' => 'field-name',
            'type' => 'boolean'
        ]);

        foreach ( ['yes', 'true', '1', 'field-name'] as $value) {
            $data = $parser->parseToken($value, $booleanFormat);
            $this->assertTrue($data);
        }

        foreach ( ['no', 'false', '0', '!field-name'] as $value) {
            $data = $parser->parseToken($value, $booleanFormat);
            $this->assertFalse($data);
        }

        $data = $parser->parseToken('notBoolean', $booleanFormat);
        $this->assertNull($data);
    }

    public function testParseSimpleArray()
    {
        $parser = new ArgumentsParser(new Format);
        $arrayFormat = $this->makeFormat([
            'type'      => 'array',
            'separator' => ',',
            'format'    => $this->makeFormat([ 'type' => 'string' ])
        ]);

        $data = $parser->parseToken('foo,bar:one,,bar:two', $arrayFormat);
        $this->assertEquals(['foo', 'bar:one', '', 'bar:two'], $data);
    }

    public function testParseSimpleObject()
    {
        $objectFormat = $this->makeFormat([
            'type'      => 'object',
            'separator' => ':',
            'format'    => [
                $this->makeFormat([
                    'name' => 'name',
                    'type' => 'string'
                ]),
                $this->makeFormat([
                    'name' => 'age',
                    'type' => 'number'
                ]),
                $this->makeFormat([
                    'name'    => 'admin',
                    'type'    => 'boolean',
                    'default' => false
                ]),
                $this->makeFormat([
                    'name'    => 'baaka',
                    'type'    => 'boolean',
                    'default' => true
                ])
            ]
        ]);

        $parser = new ArgumentsParser(new Format);

        $data = $parser->parseToken('amine:25', $objectFormat);
        $this->assertEquals([
            'name' => 'amine',
            'age' => 25,
            'admin' => false,
            'baaka' => true
        ], $data);

        $data = $parser->parseToken('amine:25:!admin:false', $objectFormat);
        $this->assertEquals([
            'name' => 'amine',
            'age' => 25,
            'admin' => false,
            'baaka' => false
        ], $data);

    }

    /**
     * @expectedException Qosasa\Core\Exceptions\ArgumentsParserException
     * @expectedExceptionMessage Required field missing: 1 given (amine) but 2 required (name:age)
     */
    public function testParseObjectWithMissingFields()
    {
        $objectFormat = $this->makeFormat([
            'type'      => 'object',
            'separator' => ':',
            'format'    => [
                $this->makeFormat([
                    'name' => 'name',
                    'type' => 'string'
                ]),
                $this->makeFormat([
                    'name' => 'age',
                    'type' => 'number'
                ]),
                $this->makeFormat([
                    'name'    => 'admin',
                    'type'    => 'boolean',
                    'default' => false
                ]),
                $this->makeFormat([
                    'name'    => 'baaka',
                    'type'    => 'boolean',
                    'default' => true
                ])
            ]
        ]);

        $parser = new ArgumentsParser(new Format);

        $data = $parser->parseToken('amine', $objectFormat);
    }

    public function testParseUsingComplexFormat()
    {
        $format = $this->makeFormat([
            'type'      => 'object',
            'separator' => ':',
            'flags'     => ['h', 'compact'],
            'format'    => [
                $this->makeFormat([
                    'name' => 'name',
                    'type' => 'string'
                ]),
                $this->makeFormat([
                    'name'      => 'parents',
                    'type'      => 'array',
                    'separator' => ',',
                    'default'   => [],
                    'format'    => $this->makeFormat([ 'type' => 'string' ])
                ]),
                $this->makeFormat([
                    'name'      => 'interfaces',
                    'type'      => 'array',
                    'separator' => ',',
                    'default'   => [],
                    'format'    => $this->makeFormat([ 'type' => 'string' ])
                ]),
                $this->makeFormat([
                    'name'      => 'attrs',
                    'type'      => 'array',
                    'separator' => ',',
                    'format'    => $this->makeFormat([
                        'type'      => 'object',
                        'separator' => '.',
                        'format'    => [
                            $this->makeFormat([
                                'name' => 'name',
                                'type' => 'string'
                            ]),
                            $this->makeFormat([
                                'name' => 'type',
                                'type' => 'string'
                            ]),
                            $this->makeFormat([
                                'name'    => 'static',
                                'type'    => 'boolean',
                                'default' => false
                            ]),
                            $this->makeFormat([
                                'name'    => 'initial',
                                'type'    => 'string',
                                'default' => ''
                            ])
                        ]
                    ])
                ])   
            ]
        ]);
        
        $parser = new ArgumentsParser($format);

        $data = $parser->parse('Baaka:Person:StupidInterface,CrazyInterface:level.int,count.int.static.0,favoriteColor.string.false.blue --compact');
        $this->assertEquals([
            'data'  => [
                'name'       => 'Baaka',
                'parents'    => [ 'Person' ],
                'interfaces' => [ 'StupidInterface', 'CrazyInterface' ],
                'attrs'      => [
                    [ 
                        'name'    => 'level',
                        'type'    => 'int',
                        'static'  => false,
                        'initial' => ''
                    ], [ 
                        'name'    => 'count',
                        'type'    => 'int',
                        'static'  => true,
                        'initial' => 0
                    ], [ 
                        'name'    => 'favoriteColor',
                        'type'    => 'string',
                        'static'  => false,
                        'initial' => 'blue'
                    ],
                ]
            ],
            'flags' => [
                'compact' => true,
                'h'       => false
            ]
        ], $data);
        
        $data = $parser->parse('Person:name.string,count.int.static,friends.vector< Person* >');
        $this->assertEquals([
            'data'  => [
                'name'       => 'Person',
                'parents'    => [],
                'interfaces' => [],
                'attrs'      => [
                    [ 
                        'name'    => 'name',
                        'type'    => 'string',
                        'static'  => false,
                        'initial' => ''
                    ], [ 
                        'name'    => 'count',
                        'type'    => 'int',
                        'static'  => true,
                        'initial' => ''
                    ], [ 
                        'name'    => 'friends',
                        'type'    => 'vector< Person* >',
                        'static'  => false,
                        'initial' => ''
                    ]
                ]
            ],
            'flags' => [
                'compact' => false,
                'h'       => false
            ]
        ], $data);

    }

    /**
     * @expectedException Qosasa\Core\Exceptions\ArgumentsParserException
     * @expectedExceptionMessage Unknown flag 'foo'
     */
    public function testParseWithUnknownFlag()
    {
        $format = $this->makeFormat([
            'type'      => 'string',
            'flags'     => ['bar', 'baz']
        ]);
        
        $parser = new ArgumentsParser($format);

        $data = $parser->parse('my --awesome string --foo --baz');
    }

    public function testParseStringWithFlags()
    {
        $format = $this->makeFormat([
            'type'      => 'string',
            'flags'     => ['bar', 'baz']
        ]);
        
        $parser = new ArgumentsParser($format);

        $data = $parser->parse('my --awesome string --baz');
        $this->assertEquals([
            'data'  => 'my --awesome string',
            'flags' => [
                'bar' => false,
                'baz' => true
            ]
        ], $data);
        
        $data = $parser->parse('--my awesome string --bar');
        $this->assertEquals([
            'data'  => '--my awesome string',
            'flags' => [
                'bar' => true,
                'baz' => false
            ]
        ], $data);
        
    }

}
