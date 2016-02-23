<?php
/**
Default Template which will be used if there is no template for the host/service.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

$rule = new \histou\template\Rule(
    $host = '^$',
    $service = '^$',
    $command = '^$',
    $perfLabel = array()
);

$genTemplate = function ($perfData) {
    /*$perfData:
    Array
    (
        [host] => debian
        [service] => hostcheck
        [perfLabel] => Array
            (
                [pl] => Array
                    (
                        [crit] => 100
                        [fill] => none
                        [max] =>
                        [min] => 0
                        [type] => normal
                        [unit] => %
                        [value] => 0
                        [warn] => 80
                    )

                [rta] => Array
                    (
                        [crit] => 5000
                        [fill] => none
                        [max] =>
                        [min] => 0
                        [type] => normal
                        [unit] => ms
                        [value] => 0.045
                        [warn] => 3000
                    )

            )

        [command] => command
    )
    */
    $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard($perfData['host'].'-'.$perfData['service']);
    foreach ($perfData['perfLabel'] as $key => $values) {
        $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
        $panel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command'].' '.$key);
        $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $target = $panel->addWarnToTarget($target, $key);
        $target = $panel->addCritToTarget($target, $key);
        $panel->addTarget($target);

        $downtime = $panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $panel->addTarget($downtime);
        $panel->fillBelowLine($key.'-value', 2);
        if (isset($values['unit'])) {
            $panel->setLeftUnit($values['unit']);
        }

        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
