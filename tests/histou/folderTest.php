<?php

namespace tests;

class FolderTest extends \MyPHPUnitFrameworkTestCase
{

    protected function setUp()
    {
        spl_autoload_register('__autoload');
        define('DEFAULT_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'histou_test', 'default')));
        define('CUSTOM_TEMPLATE_FOLDER', join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'histou_test', 'custom')));

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
		- missing folder
	*/
    public function testLoad()
    {
        $files = array(
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template1.php')),
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template1.php1')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template2.simple')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template2.2simple2')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template3.simple.foo')),
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template4.simple')),
        );

        $templateFiles1 = \histou\Folder::loadFolders(
            array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
        );
        $this->assertEquals(array(), $templateFiles1);

        foreach ($files as $file) {
            touch($file);
        }

        $templateFiles2 = \histou\Folder::loadFolders(
            array(CUSTOM_TEMPLATE_FOLDER, DEFAULT_TEMPLATE_FOLDER)
        );
        $expected = array(
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template1.php')),
            join(DIRECTORY_SEPARATOR, array(CUSTOM_TEMPLATE_FOLDER, 'template4.simple')),
            join(DIRECTORY_SEPARATOR, array(DEFAULT_TEMPLATE_FOLDER, 'template2.simple')),
        );
        $this->assertEquals(sort($expected), sort($templateFiles2));
		
		\histou\Folder::loadFolders(array("adsf"));
    }
}
