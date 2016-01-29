<?php
/**
Icmp Check Template File.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

$rule = new \histou\template\Rule(
    $host = '*',
    $service = '*',
    $command = '*',
    $perfLabel = array('rta', 'pl', 'rtmax', 'rtmin')
);

$genTemplate = function ($perfData) {
    $colors = array('#085DFF', '#07ff78', '#db07ff');
    $dashboard = new \histou\grafana\Dashboard($perfData['host'].'-'.$perfData['service']);

    $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
    $panel = new \histou\grafana\GraphPanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command'].' rta');
    $i = 0;
    foreach (array('rta', 'rtmin', 'rtmax') as $type) {
        $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $type, $colors[$i]);
        $target = $panel->addWarnToTarget($target, $type);
        $target = $panel->addCritToTarget($target, $type);
        $panel->addTarget($target);

        $downtime = $panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $type);
        $panel->addTarget($downtime);
        $panel->fillBelowLine($type.'-value', 2);
        $i++;
    }
    if (isset($perfData['perfLabel']['rta']['unit'])) {
        $panel->setLeftUnit($perfData['perfLabel']['rta']['unit']);
    }
    $row->addPanel($panel);
    $dashboard->addRow($row);
    $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
    $panel = new \histou\grafana\GraphPanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command'].' pl');
    $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], 'pl');
    $target = $panel->addWarnToTarget($target, $type);
    $target = $panel->addCritToTarget($target, $type);
    $panel->addTarget($target);

    $downtime = $panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], 'pl');
    $panel->addTarget($downtime);
    $panel->fillBelowLine($type.'-value', 2);

    $row->addPanel($panel);
    $dashboard->addRow($row);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
