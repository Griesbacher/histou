<?php
/**
Load Check Template File.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/

$rule = new \histou\template\Rule(
    $host = '*',
    $service = '*',
    $command = '*',
    $perfLabel = array('xload1', 'xload5', 'xload15')
);

$genTemplate = function ($perfData) {
    $colors = array('#085DFF', '#07ff78', '#4707ff');
    $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard($perfData['host'].'-'.$perfData['service']);
    $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
    $panel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
    $i = 0;
    foreach ($perfData['perfLabel'] as $key => $values) {
        $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $key, $colors[$i]);
        $panel->addTarget($target);

        $downtime = $panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $panel->addTarget($downtime);
        if (isset($values['unit'])) {
            $panel->setLeftUnit($values['unit']);
        }
        $i++;
    }
    $row->addPanel($panel);
    $dashboard->addRow($row);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
