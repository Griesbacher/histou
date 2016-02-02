<?php
/**
Template for the icinga2 self check.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

$rule = new \histou\template\Rule(
    $host = '.*',
    $service = '.*',
    $command = '^icinga$',
    $perfLabel = array('.*')
);

$genTemplate = function ($perfData) {
    $colors = array( "#7EB26D", "#EAB839", "#6ED0E0", "#1F78C1", "#BA43A9", "#508642", "#CCA300", "#447EBC", "#C15C17");
    $dashboard = new \histou\grafana\Dashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);

    $genRow = function ($panelName, array $perfLabelparts, $perfLabelformat) use ($perfData, $colors, $dashboard) {
        $row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);
        $panel = new \histou\grafana\GraphPanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command'].' '.$panelName);
        $i = 0;
        foreach ($perfLabelparts as $part) {
            $perfLabel = sprintf($perfLabelformat, $part);
			$target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel, $colors[$i++]);
			$panel->addTarget($target);
        }
        $row->addPanel($panel);
        $dashboard->addRow($row);
    };

    foreach (array('active_host_checks', 'passive_host_checks', 'active_service_checks', 'passive_service_checks') as $checks) {
        $genRow($checks, array('', '_1min', '_5min', '_15min'), $checks.'%s');
    }
    foreach (array('latency', 'execution_time') as $type) {
        $genRow($type, array('min', 'max', 'avg'), '%s_'.$type);
    }

    $genRow(
        'number of services',
        array('ok', 'warning', 'warning', 'unknown', 'pending', 'unreachable', 'flapping', 'in_downtime', 'acknowledged'),
        'num_services_%s'
    );
    $genRow('number of hosts', array('up', 'down', 'unreachable', 'flapping', 'in_downtime', 'acknowledged'), 'num_hosts_%s');


    return $dashboard;
};
