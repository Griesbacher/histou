<?php

namespace tests\template;

class TemplateTest extends \MyPHPUnitFrameworkTestCase
{
    protected function setUp()
    {
        spl_autoload_register('__autoload');
        \histou\Basic::parsIni('histou.ini.example');
    }

    /*public function testMatchesTablename()
    {
        $t = new \histou\template\Template(
            '',
            new \histou\template\Rule('host', 'service', 'command', array('p1','p2'), ''),
            function ($perfData) {
                return 'foo';
            }
        );

        $this->assertSame(true, $t->matchesTablename('host&service&command&p1&value'));
        $this->assertSame(false, $t->matchesTablename('horst&service&command&p1&value'));
        $this->assertSame(false, $t->matchesTablename('host&service&command&p3&value'));
    }*/

    public function testValidTemplate()
    {
        \histou\template\Rule::setCheck(
            'host',
            'service',
            'command',
            array('p1', 'p2', 'p3')
        );

        $validTests = array(
            array(new \histou\template\Rule('host', 'service', 'command', array(), ''), false),
            array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2'), ''), false),
            array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''), true),
            array(new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3', 'p4'), ''), false),
            array(new \histou\template\Rule('host', 'service', 'command', array('.*'), ''), true),
            array(new \histou\template\Rule('host', 'service', '.*', array('.*'), ''), true),
            array(new \histou\template\Rule('host', 'service', 'foo', array('.*'), ''), false),
        );

        foreach ($validTests as $test) {
            $this->assertSame($test[1], static::createTemplate($test[0])->isValid(), $test[0]);
        }
    }

    public function testVariablesInRules()
    {
        \histou\template\Rule::setCheck(
            'host123',
            'service456',
            'service456-command',
            array('host123_p1', 'p2', 'p3')
        );

        $validTests = array(
            array(new \histou\template\Rule('host123', 'service456', 'service456-command', array('&host&_p1', 'p2', 'p3'), ''), true),
            array(new \histou\template\Rule('host123', 'service456', '&service&-command', array('host123_p1', 'p2', 'p3'), ''), true),
            array(new \histou\template\Rule('host123', 'service456', '&service&-command', array('&host&_p1', 'p2', 'p3'), ''), true),
        );

        foreach ($validTests as $test) {
            $this->assertSame($test[1], static::createTemplate($test[0])->isValid());
        }
    }

    public function testCompareTemplate()
    {
        $compareTests = array(
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array(), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array(), '')
                                )
            ),
            //start winns over no match
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p2'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p3'), ''),
                                )
            ),
            //exactmatch wins over star
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                )
            ),
            //exactmatch wins over star
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                                )
            ),
            //equals
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                                )
            ),
            //command over perfLabel
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'foo', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('.*'), ''),
                                )
            ),
            //too much infos
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3', 'p4'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2'), ''),
                                )
            ),
            //too much infos desc
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3', 'p4'), ''),
                                )
            ),
            //too much infos desc
            array(
                'expected' => new \histou\template\Rule('.*', '.*', '.*', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('.*', '.*', '.*', array('p1', 'p2'), ''),
                                    new \histou\template\Rule('.*', '.*', '.*', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('.*', '.*', '.*', array('p1', 'p2', 'p3', 'p4'), ''),
                                )
            ),

            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', '.*', array('p1', 'p2', 'p3'), ''),
                                )
            ),
            array(
                'expected' => new \histou\template\Rule('host', 'service', '.*', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', 'foo', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', '.*', array('p1', 'p2', 'p3'), ''),
                                )
            ),
            array(
                'expected' => new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                'input' => array(
                                    new \histou\template\Rule('host', 'service', '.*', array('p1', 'p2', 'p3'), ''),
                                    new \histou\template\Rule('host', 'service', 'command', array('p1', 'p2', 'p3'), ''),
                                )
            ),
        );

        \histou\template\Rule::setCheck(
            'host',
            'service',
            'command',
            array('p1', 'p2', 'p3')
        );

        $i = 0;
        foreach ($compareTests as $test) {
            $templates = array();
            foreach ($test['input'] as $rule) {
                array_push($templates, static::createTemplate($rule));
            }
            usort($templates, '\histou\template\Template::compare');

            $this->assertEquals($test['expected'], $templates[0]->getRule(), $i++.": ".$test['expected']."\n != \n".$templates[0]->getRule());
        }

        foreach ($compareTests as $test) {
            $rules = array();
            foreach ($test['input'] as $rule) {
                array_push($rules, $rule);
            }
            usort($rules, '\histou\template\Template::compare');

            $this->assertEquals($test['expected'], $rules[0]);
        }
    }

    public function testFailCompareTemplate()
    {
        $this->setExpectedException('InvalidArgumentException');
        $templates = array(new \histou\template\loader(), new \histou\template\loader());
        usort($templates, '\histou\template\Template::compare');
    }

    private static function createTemplate($rule)
    {
        return new \histou\template\Template(
            '',
            $rule,
            function ($perfData) {
                    return 'foo';
            }
        );
    }
}
