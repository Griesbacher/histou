<?php

namespace tests\template;

class InfluxdbTest extends \MyPHPUnitFrameworkTestCase
{
    public function testFetchPerfData()
    {
		define('HOST', 'host');
		define('SERVICE', 'service');
		define('INFLUX_FIELDSEPERATOR', '&');
		$classname ='\histou\database\Influxdb';

		$stub = $this->getMockBuilder($classname)
		->setConstructorArgs(array('url'))
		->setMethods(array('makeRequest'))
		->getMock();

		$stub->expects($this->once())
		->method('makeRequest')
		->will($this->returnArgument(0));

		$this->assertSame($stub->fetchPerfData(), 'select * from /host&service&.*/ ORDER BY time DESC limit 1');
	}

	public function testFilterPerfdata()
    {
		define('HOST', 'debian-jessi-devel');
		define('SERVICE', '');
		define('INFLUX_FIELDSEPERATOR', '&');
		$influx = new \histou\database\Influxdb('url');
		$perfData = $influx->filterPerfdata(
			json_decode($this->influxDBResult, true)['results'],
			HOST,
			SERVICE,
			'\\'.INFLUX_FIELDSEPERATOR
		);
		$this->assertSame(HOST, $perfData['host']);
		$this->assertSame(SERVICE, $perfData['service']);
		$this->assertSame('hostalive', $perfData['command']);
		$this->assertSame(2, sizeof($perfData['perfLabel']));

		$error = $influx->filterPerfdata(
			json_decode($this->emptyResult, true)['results'],
			HOST,
			SERVICE,
			'\\'.INFLUX_FIELDSEPERATOR
		);
		$this->assertSame("No datafound", $error);

		$dummy = $influx->filterPerfdata(
			json_decode($this->dummyResult, true)['results'],
			HOST,
			SERVICE,
			'\\'.INFLUX_FIELDSEPERATOR
		);
		$this->assertSame("normal", $dummy['perfLabel']['pl']['foo']['type']);
	}

	private $emptyResult = '{
    "results": [
        {
            "series": [
			]
        }
    ]
}';
	private $dummyResult = '{
    "results": [
        {
            "series": [
				{
                    "name": "debian-jessi-devel&&hostalive&pl&foo",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            100
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&pl&bar",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            0
                        ]
                    ]
                }
			]
        }
    ]
}';
	private $influxDBResult = '{
    "results": [
        {
            "series": [
                {
                    "name": "debian-jessi-devel&&hostalive&pl&crit",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            100
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&pl&max",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            0
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&pl&min",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            0
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&pl&value",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            0
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&pl&warn",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "%",
                            80
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&rta&crit",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "ms",
                            5000
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&rta&min",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "ms",
                            0
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&rta&value",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "ms",
                            0.043
                        ]
                    ]
                },
                {
                    "name": "debian-jessi-devel&&hostalive&rta&warn",
                    "columns": [
                        "time",
                        "fill",
                        "type",
                        "unit",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-12-16T12:39:08Z",
                            "none",
                            "normal",
                            "ms",
                            3000
                        ]
                    ]
                }
            ]
        }
    ]
}';
}