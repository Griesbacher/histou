<?php

namespace tests\template;

class LoaderTest extends \MyPHPUnitFrameworkTestCase
{

    protected function setUp()
    {
        spl_autoload_register('__autoload');
        define('DEFAULT_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'histou_test', 'default')));
        define('CUSTOM_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'histou_test', 'custom')));
        define('PHP_COMMAND', 'php');

        if (!file_exists(DEFAULT_TEMPLATE_FOLDER)) {
            mkdir(DEFAULT_TEMPLATE_FOLDER, 0777, true);
        }
        if (!file_exists(CUSTOM_TEMPLATE_FOLDER)) {
            mkdir(CUSTOM_TEMPLATE_FOLDER, 0777, true);
        }
    }

    /*
    tested:
        - custom wins over default
        - just valid files are used
    */
    public function testLoad()
    {
        $files = array(
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')) => '<?php
                            $rule = new \histou\template\Rule(
                                $host = ".*",
                                $service = ".*",
                                $command = "NONE",
                                $perfLabel = array("rta", "pl")
                            );
                            $genTemplate = function ($perfData) {
                                return "template1";
                            };',
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template2.php')) => '<?php
                            $rule = new \histou\template\Rule(
                                $host = ".*",
                                $service = ".*",        adsfasdfasdf
                                $command = ".*",
                                $perfLabel = array("rta", "pl")
                            );
                            $genTemplate = function ($perfData) {
                                return "template1";
                            };',
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template3.simple')) => '
#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
    "hallo":"world"
}',
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template3.simple123')) => '
#simple file
host = *
service = *
command = *
perfLabel = load1, load5, load15

#Copy the grafana dashboard below:
{
    "hallo":"world"
}',
        );

        foreach ($files as $file => $content) {
            file_put_contents($file, $content);
        }

        $templateFiles = \histou\Folder::loadFolders(
            array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
        );

        $expected = array(
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')),
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template2.php')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template3.simple')),
        );
        $this->assertEquals(sort($expected), sort($templateFiles));

        $templates = array();
        foreach ($files as $file => $content) {
            array_push($templates, \histou\template\Loader::loadTemplate($file));
        }

        $this->assertInstanceOf('\histou\template\Template', $templates[0]);
        $this->assertSame(null, $templates[1]); //Syntaxcheck failed
        $this->assertInstanceOf('\histou\template\SimpleTemplate', $templates[2]);
        $this->assertSame(null, $templates[3]); //Wrong filename

        $this->assertSame(CUSTOM_TEMPLATE_FOLDER, $templates[0]->getPath());
        $this->assertSame('template1.php', $templates[0]->getBaseName());
        $this->assertSame('template1', $templates[0]->getSimpleFileName());
        $this->assertSame("File:\t$expected[0]:
Rule:
\tFile:\t$expected[0]:
\t\tHost: ;.*;
\t\tService: ;.*;
\t\tCommand: ;^$;
\t\tPerflabel: ;pl;, ;rta;", $templates[0]->__toString());

        $rule = $templates[0]->getRule();
        $this->assertSame('template1.php', $rule->getBaseName());
        $this->assertSame(join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')), $rule->getFileName());

        $expected = '{
    "hallo":"world"
}';
        $this->assertSame($expected, $templates[2]->generateDashboard('foo'));

        $default = \histou\template\template::findDefaultTemplate($templates, "template1.php");
        $this->assertSame($templates[0], $default);
        $default2 = \histou\template\template::findDefaultTemplate($templates, "foo.php");
        $this->assertSame(null, $default2);
    }
}
