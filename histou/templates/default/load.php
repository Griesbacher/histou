<?php
/**
Load Check Template File.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

$rule = new Rule(
    $host = '*',
    $service = '*',
    $command = '*',
    $perfLable = array('load1', 'load5', 'load15')
);

$genTemplate = function ($perfData) {

    $perfKeys = array_keys($perfData['perfLabel']);
    $dashboard = new Dashboard($perfData['host'].'-'.$perfData['service']);
    $row = new Row($perfData['service'].' '.$perfData['command']);
    $panel = new GraphPanel(
        $perfData['host'].' '.$perfData['service']
        .' '.$perfData['command']
    );
    $colors = array('#085DFF', '#07ff78', '#4707ff');
    for ($i = 0; $i < sizeof($perfData['perfLabel']); $i++) {
        $target = sprintf(
            '%s%s%s%s%s%s%s%s%s',
            $perfData['host'],
            INFLUX_FIELDSEPERATOR,
            $perfData['service'],
            INFLUX_FIELDSEPERATOR,
            $perfData['command'],
            INFLUX_FIELDSEPERATOR,
            $perfKeys[$i],
            INFLUX_FIELDSEPERATOR,
            "value"
        );
        $alias = $perfKeys[$i];
        $panel->addAliasColor($alias, $colors[$i]);
        $panel->addTargetSimple($target, $alias);
        $panel->fillBelowLine($alias, 2);
    }
    $panel->addWarning(
        $perfData['host'],
        $perfData['service'],
        $perfData['command'],
        $perfKeys['load1']
    );
    $panel->addCritical(
        $perfData['host'],
        $perfData['service'],
        $perfData['command'],
        $perfKeys['load1']
    );
    $panel->addDowntime(
        $perfData['host'],
        $perfData['service'],
        $perfData['command'],
        $perfKeys['load1']
    );
    $row->addPanel($panel);
    $dashboard->addRow($row);

    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};