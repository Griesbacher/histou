<?php

namespace tests\template;

class FolderTest extends \MyPHPUnitFrameworkTestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
        define('TMP_FOLDER', sys_get_temp_dir());
        define('PHP_COMMAND', 'php');
        define('DEFAULT_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(TMP_FOLDER, 'histou_test', 'default')));
        define('CUSTOM_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(TMP_FOLDER, 'histou_test', 'custom')));

        if (!file_exists(DEFAULT_TEMPLATE_FOLDER)) {
            mkdir(DEFAULT_TEMPLATE_FOLDER, 0777, true);
        }
        if (!file_exists(CUSTOM_TEMPLATE_FOLDER)) {
            mkdir(CUSTOM_TEMPLATE_FOLDER, 0777, true);
        }
    }

    public function testCreateAndLoad()
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
                join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template3.simple')) => '#simple file
                                host = *
                                service = *
                                command = *
                                perfLabel = load1, load5, load15

                                #Copy the grafana dashboard below:
                                {
                                    "hallo":"world",
								}',
        );

        foreach ($files as $file => $content) {
            file_put_contents($file, $content);
        }

        $templateFiles = \histou\Folder::loadFolders(
            array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
        );

        $templateCache = new \histou\template\cache();
        $templates = $templateCache->loadTemplates($templateFiles);
        $this->assertSame(2, sizeof($templates));
        $this->assertSame(true, file_exists(join(DIRECTORY_SEPARATOR, array(TMP_FOLDER, '.histou_cache'))));
        $this->assertInstanceOf('\histou\template\Template', $templates[0]);
        $this->assertInstanceOf('\histou\template\SimpleTemplate', $templates[1]);

        //load from cachefile
        $templateCache2 = new \histou\template\cache();
        $templates2 = $templateCache2->loadTemplates($templateFiles);
        $this->assertSame(2, sizeof($templates2));
        $this->assertInstanceOf('\histou\template\Rule', $templates2[0]);
        $this->assertInstanceOf('\histou\template\Rule', $templates2[1]);
        $this->assertEquals($templates[0]->getRule(), $templates2[0]);
        $this->assertEquals($templates[1]->getRule(), $templates2[1]);

        sleep(1);
        //make template invalid
        file_put_contents(join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')), "<?php\n foo");

        //just one template loaded
        $templateCache3 = new \histou\template\cache();
        $templates3 = $templateCache3->loadTemplates($templateFiles);
        $this->assertSame(1, sizeof($templates3));

        //restore valid template
        file_put_contents(
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')),
            $files[join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php'))]
        );

        //one template from file loaded one from cache
        $templateCache4 = new \histou\template\cache();
        $templates4 = $templateCache4->loadTemplates($templateFiles);
        $this->assertSame(2, sizeof($templates4));
        $this->assertInstanceOf('\histou\template\Template', $templates4[0]);
        $this->assertInstanceOf('\histou\template\Rule', $templates4[1]);

        //second again stored
        $templateCache5 = new \histou\template\cache();
        $templates5 = $templateCache5->loadTemplates($templateFiles);
        $this->assertSame(2, sizeof($templates5));
        $this->assertInstanceOf('\histou\template\Rule', $templates5[0]);
        $this->assertInstanceOf('\histou\template\Rule', $templates5[1]);
    }
}
