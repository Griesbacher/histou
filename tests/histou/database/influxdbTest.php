<?php

namespace tests\template;

class InfluxdbTest extends \MyPHPUnitFrameworkTestCase
{
    public function testFetchPerfData()
    {
        define('HOST', 'host');
        define('SERVICE', 'service');
        $classname ='\histou\database\Influxdb';

        $stub = $this->getMockBuilder($classname)
        ->setConstructorArgs(array('url'))
        ->setMethods(array('makeGetRequest'))
        ->getMock();

        $stub->expects($this->once())
        ->method('makeGetRequest')
        ->willReturn(array('results' => array('1', '2')));
        $this->assertSame('1', $stub->fetchPerfData());


        $stub2 = $this->getMockBuilder($classname)
        ->setConstructorArgs(array('url'))
        ->setMethods(array('makeGetRequest'))
        ->getMock();

        $stub2->expects($this->once())
        ->method('makeGetRequest')
        ->willReturn(array('results' => array('', '2')));
        $this->assertSame('2', $stub2->fetchPerfData());
        
        $stub3 = $this->getMockBuilder($classname)
        ->setConstructorArgs(array('url'))
        ->setMethods(array('makeGetRequest'))
        ->getMock();

        $stub3->expects($this->once())
        ->method('makeGetRequest')
        ->willReturn(array('results' => array()));
        $this->assertSame(null, $stub3->fetchPerfData());
    }

    public function testFilterPerfdata()
    {
        define('HOST', 'debian-jessi-devel');
        define('SERVICE', 'hostcheck');
        $influx = new \histou\database\Influxdb('url');
        $perfData = $influx->filterPerfdata(
            json_decode($this->influxDBResult, true)['results'][0],
            HOST,
            SERVICE
        );
        $this->assertSame(HOST, $perfData['host']);
        $this->assertSame(SERVICE, $perfData['service']);
        $this->assertSame('hostalive', $perfData['command']);
        $this->assertSame(2, sizeof($perfData['perfLabel']));

        $error = $influx->filterPerfdata(
            json_decode($this->emptyResult, true)['results'][0],
            HOST,
            SERVICE
        );
        $this->assertSame("No data found", $error);
    }

    private $emptyResult = '{
    "results": [
        {
            "series": [
            ]
        }
    ]
}';
    private $influxDBResult = '{"results":[{"series":[{"name":"metrics","tags":{"performanceLabel":"pl"},"columns":["time","command","crit","downtime","fill","host","max","min","service","type","unit","value","warn"],"values":[["2016-01-29T13:47:37Z","hostalive",100,null,"none","debian-jessi-devel",null,0,"hostcheck","normal","%",0,80]]},{"name":"metrics","tags":{"performanceLabel":"rta"},"columns":["time","command","crit","downtime","fill","host","max","min","service","type","unit","value","warn"],"values":[["2016-01-29T13:47:37Z","hostalive",5000,null,"none","debian-jessi-devel",null,0,"hostcheck","normal","ms",0.049,3000]]}]},{"series":[{"name":"metrics","tags":{"performanceLabel":"pl"},"columns":["time","command","crit","downtime","fill","host","max","min","service","type","unit","value","warn"],"values":[["2016-01-28T14:44:13Z","hostalive",100,null,"none","debian-jessi-devel",null,0,"hostcheck","normal","%",0,80]]},{"name":"metrics","tags":{"performanceLabel":"rta"},"columns":["time","command","crit","downtime","fill","host","max","min","service","type","unit","value","warn"],"values":[["2016-01-28T14:44:13Z","hostalive",5000,null,"none","debian-jessi-devel",null,0,"hostcheck","normal","ms",0.039,3000]]}]}]}';
}
