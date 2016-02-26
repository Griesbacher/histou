<?php

namespace tests;

class DebugTest extends \MyPHPUnitFrameworkTestCase
{
    public function init()
    {
        $_GET['host'] = 'host';
        \histou\Basic::parsIni('histou.ini.example');
        \histou\Basic::parsArgs();
    }

    public function testBasics()
    {
        $this->assertSame(\histou\Debug::isEnable(), false);
        \histou\Debug::enable();
        $this->assertSame(\histou\Debug::isEnable(), true);

        $this->assertSame(\histou\Debug::printBoolean(true), "true");
        $this->assertSame(\histou\Debug::printBoolean(false), "false");

        \histou\Debug::add("foo");
        $this->assertSame("#### foo\n", \histou\Debug::getLogAsMarkdown());
        \histou\Debug::add("bar");
        $this->assertSame("#### foo\n#### bar\n", \histou\Debug::getLogAsMarkdown());
    }

    public function testGenMarkdownRow()
    {
        $this->init();
        $panel = new \histou\grafana\TextPanel('', 1);
        $panel->setMode(\histou\grafana\TextPanel::MARKDOWN);
        $panel->setContent("foo");
        $row = new \histou\grafana\Row("bar");
        $row->addPanel($panel);
        $this->assertEquals($row, \histou\Debug::genRow("foo", \histou\grafana\TextPanel::MARKDOWN, "bar"));
    }

    public function testErrorMarkdownDashboard()
    {
        $this->init();
        $panel = new \histou\grafana\TextPanel('', 1);
        $panel->setMode(\histou\grafana\TextPanel::MARKDOWN);
        $panel->setContent("foo");
        $row = new \histou\grafana\Row("ERROR");
        $row->addPanel($panel);
        $dashboard = new \histou\grafana\dashboard\dashboardInfluxdb('Error');
        $dashboard->addRow($row);
        $this->assertEquals($dashboard, \histou\Debug::errorMarkdownDashboard("foo"));

    }
}
