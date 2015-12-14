<?php

namespace tests\grafana;

class RowTest extends \MyPHPUnitFrameworkTestCase
{
    public function testCreatePanel()
    {
        $row = new \histou\grafana\Row('r1', '100px');
        $this->assertSame('r1', $row->toArray()['title']);
        $row->setEditable(false);
        $this->assertSame(false, $row->toArray()['editable']);
        $row->setCustomProperty('foo', 'bar');
        $this->assertSame('bar', $row->toArray()['foo']);
    }
}
