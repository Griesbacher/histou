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
	}

    public function testParseSimpleFile()
    {
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
		$this->assertInstanceOf('\histou\grafana\Dashboard', $errorDashboard);

		file_put_contents(
			$path,
'#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
	"tablename":"host&service&command&perfLabel&value",
	"title":";host - service;"
}'
		);
		$result = \histou\template\Parser::parseSimpleTemplate($path);
		$this->assertInstanceOf('\histou\template\Rule', $result[0]);
		$this->assertInstanceOf('\closure', $result[1]);
		$perfData = array('host' => 'h1', 'service' => 's1', 'command' => 'c1', 'perfLabel' => array('p1' => 'v1'));
		$jsonString = $result[1]($perfData);
		$expected = '{
	"tablename":"h1&s1&c1&perfLabel&value",
	"title":";h1 - s1;"
}';
		$this->assertEquals($expected, $jsonString);


	}
}