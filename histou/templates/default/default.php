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

$rule = new Rule(
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
    [service] => http
    [perfLabel] => Array
        (
            [size] => Array
                (
                    [value] => Array
                        (
                            [unit] => B
                        )

                    [min] => Array
                        (
                            [unit] => B
                        )

                )

            [time] => Array
                (
                    [value] => Array
                        (
                            [unit] => s
                        )

                    [min] => Array
                        (
                            [unit] => s
                        )

                )

        )

    [command] => http
    )
    */

    $perfKeys = array_keys($perfData['perfLabel']);
    $dashboard = new Dashboard($perfData['host'].'-'.$perfData['service']);
    for ($i = 0; $i < sizeof($perfData['perfLabel']); $i++) {
        $row = new Row($perfData['service'].' '.$perfData['command']);
        $panel = new GraphPanel(
            $perfData['host'].' '.$perfData['service']
            .' '.$perfData['command'].' '.$perfKeys[$i]
        );
        //add value graph
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
        $alias = $perfData['host']." ".$perfData['service']." ".$perfKeys[$i]." value";
        $panel->addAliasColor($alias, '#085DFF');
        $panel->addTargetSimple($target, $alias);
        $panel->fillBelowLine($alias, 2);
        //Add Label
        if (isset($perfData['perfLabel'][$perfKeys[$i]]['value']['unit'])) {
            $panel->setleftYAxisLabel(
                $perfData['perfLabel'][$perfKeys[$i]]['value']['unit']
            );
        }
        //Add Warning and Critical
        $panel->addWarning(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfKeys[$i]
        );
        $panel->addCritical(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfKeys[$i]
        );
        $panel->addDowntime(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfKeys[$i]
        );
        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
