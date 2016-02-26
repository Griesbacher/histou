<?php

namespace tests\template;

class ParserTest extends \MyPHPUnitFrameworkTestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
        define('DEFAULT_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'histou_test', 'default')));
        define('HEIGHT', '200px');
        define('INFLUX_FIELDSEPERATOR', '&');
        if (!file_exists(DEFAULT_TEMPLATE_FOLDER)) {
            mkdir(DEFAULT_TEMPLATE_FOLDER, 0777, true);
        }
        define('INFLUXDB', 'influxdb');
        define('ELASTICSEARCH', 'elasticsearch');
    }

    public function testParseSimpleFileInfluxdb()
    {
        define('DATABASE_TYPE', 'influxdb');
        $path = join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template1.simple'));
        file_put_contents(
            $path,
            '#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
    "hallo":"world",
}'
        );
        $result = \histou\template\Parser::parseSimpleTemplate($path);
        $this->assertInstanceOf('\histou\template\Rule', $result[0]);
        $this->assertInstanceOf('\closure', $result[1]);
        //Not valid JSON
        $errorDashboard = $result[1]('foo');
        $this->assertInstanceOf('\histou\grafana\dashboard\Dashboard', $errorDashboard);

        file_put_contents(
            $path,
            '#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
    "query": "SELECT mean(\"value\") FROM \"metrics\" WHERE \"host\" = \'debian-host\' AND \"service\" = \'hostcheck\' AND \"command\" = \'hostalive\' AND \"performanceLabel\" = \'pl\' AND $timeFilter GROUP BY time($interval) fill(null)",
    "title":";debian-host - hostcheck;"
}'
        );
        $result = \histou\template\Parser::parseSimpleTemplate($path);
        $this->assertInstanceOf('\histou\template\Rule', $result[0]);
        $this->assertInstanceOf('\closure', $result[1]);
        $perfData = array('host' => 'h1', 'service' => 's1', 'command' => 'c1', 'perfLabel' => array('p1' => 'v1'));
        $jsonString = $result[1]($perfData);
        $expected = '{
    "query": "SELECT mean(\"value\") FROM \"metrics\" WHERE \"host\" = \'h1\' AND \"service\" = \'s1\' AND \"command\" = \'c1\' AND \"performanceLabel\" = \'pl\' AND $timeFilter GROUP BY time($interval) fill(null)",
    "title":";h1 - s1;"
}';
        $this->assertEquals($expected, $jsonString);


    }
    
    public function testParseSimpleFileElastic()
    {
        define('DATABASE_TYPE', 'elasticsearch');
        $path = join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template1.simple'));
        file_put_contents(
            $path,
            '#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
"query":"host: \"localhost.localdomain\" AND service: \"hostcheck\" AND command: \"hostalive\" AND performanceLabel: \"pl\"",
"title":"localhost.localdomain ... hostcheck"
}'
        );
        $result = \histou\template\Parser::parseSimpleTemplate($path);
        $this->assertInstanceOf('\histou\template\Rule', $result[0]);
        $this->assertInstanceOf('\closure', $result[1]);
        $perfData = array('host' => 'h1', 'service' => 's1', 'command' => 'c1', 'perfLabel' => array('p1' => 'v1'));
        $jsonString = $result[1]($perfData);
        $expected = '{
"query":"host: \"h1\" AND service: \"s1\" AND command: \"c1\" AND performanceLabel: \"pl\"",
"title":"h1 ... s1"
}';
        $this->assertEquals($expected, $jsonString);


    }
}
