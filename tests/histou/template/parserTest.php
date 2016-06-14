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
   "rows":[
      {
         "panels":[
            {
               "targets":[
                  {
                     "tags":[
                        {
                           "key":"host",
                           "operator":"=",
                           "value":"Host"
                        },
                        {
                           "condition":"AND",
                           "key":"service",
                           "operator":"=",
                           "value":"Service"
                        },
                        {
                           "condition":"AND",
                           "key":"command",
                           "operator":"=",
                           "value":"Command"
                        },
                        {
                           "condition":"AND",
                           "key":"performanceLabel",
                           "operator":"=",
                           "value":"pl"
                        }
                     ]
                  }
               ]
            }
         ]
      }
   ],
    "title":";Host - Service;"
}'
        );
        $result = \histou\template\Parser::parseSimpleTemplate($path);
        $this->assertInstanceOf('\histou\template\Rule', $result[0]);
        $this->assertInstanceOf('\closure', $result[1]);
        $perfData = array('host' => 'h1', 'service' => 's1', 'command' => 'c1', 'perfLabel' => array('p1' => 'v1'));
        $jsonString = $result[1]($perfData);
        $expected = '{
   "rows":[
      {
         "panels":[
            {
               "targets":[
                  {
                     "tags":[
                        {
                           "key":"host",
                           "operator":"=",
                           "value":"h1"
                        },
                        {
                           "condition":"AND",
                           "key":"service",
                           "operator":"=",
                           "value":"s1"
                        },
                        {
                           "condition":"AND",
                           "key":"command",
                           "operator":"=",
                           "value":"c1"
                        },
                        {
                           "condition":"AND",
                           "key":"performanceLabel",
                           "operator":"=",
                           "value":"pl"
                        }
                     ]
                  }
               ]
            }
         ]
      }
   ],
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
   "rows":[
      {
         "panels":[
            {
               "targets":[
                  {
                     "tags":[
                        {
                           "key":"host",
                           "operator":"=",
                           "value":"Host-2"
                        },
                        {
                           "condition":"AND",
                           "key":"service",
                           "operator":"=",
                           "value":"Service-2"
                        },
                        {
                           "condition":"AND",
                           "key":"command",
                           "operator":"=",
                           "value":"Command-2"
                        },
                        {
                           "condition":"AND",
                           "key":"performanceLabel",
                           "operator":"=",
                           "value":"pl"
                        },
                        {
                           "condition":"AND",
                           "key":"foo",
                           "operator":"=",
                           "value":"bar"
                        }
                     ]
                  }
               ]
            }
         ]
      }
   ],
    "title":";Host-2 - Service-2;"
}'
        );
        $result = \histou\template\Parser::parseSimpleTemplate($path);
        $this->assertInstanceOf('\histou\template\Rule', $result[0]);
        $this->assertInstanceOf('\closure', $result[1]);
        $perfData = array('host' => 'h1', 'service' => 's1', 'command' => 'c1', 'perfLabel' => array('p1' => 'v1'));
        $jsonString = $result[1]($perfData);
        $expected = '{
   "rows":[
      {
         "panels":[
            {
               "targets":[
                  {
                     "tags":[
                        {
                           "key":"host",
                           "operator":"=",
                           "value":"h1"
                        },
                        {
                           "condition":"AND",
                           "key":"service",
                           "operator":"=",
                           "value":"s1"
                        },
                        {
                           "condition":"AND",
                           "key":"command",
                           "operator":"=",
                           "value":"c1"
                        },
                        {
                           "condition":"AND",
                           "key":"performanceLabel",
                           "operator":"=",
                           "value":"pl"
                        },
                        {
                           "condition":"AND",
                           "key":"foo",
                           "operator":"=",
                           "value":"bar"
                        }
                     ]
                  }
               ]
            }
         ]
      }
   ],
    "title":";h1 - s1;"
}';
        $this->assertEquals($expected, $jsonString);


    }
}
