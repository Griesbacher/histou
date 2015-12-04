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

$rule = new Rule(
    $host = '*',
    $service = '*',
    $command = '*',
    $perfLabel = array('rta', 'pl', 'rtmax', 'rtmin')
);

$genTemplate = function ($perfData) {
    $dashboard = new Dashboard($perfData['host'].'-'.$perfData['service']);
    foreach (array('rta', 'pl') as $perfLabel) {
        $row = new Row($perfData['service'].' '.$perfData['command']);
        $panel = new GraphPanel(
            $perfData['host'].' '.$perfData['service']
            .' '.$perfData['command'].' '.$perfLabel
        );

        $colors = array('#085DFF', '#07ff78', '#db07ff');
        if ($perfLabel == 'rta') {
            $types = array('rta', 'rtmax', 'rtmin');
        } else {
            $types = array('pl');
        }
        for ($i = 0; $i < sizeof($types); $i++) {
            $target = sprintf(
                '%s%s%s%s%s%s%s%s%s',
                $perfData['host'],
                INFLUX_FIELDSEPERATOR,
                $perfData['service'],
                INFLUX_FIELDSEPERATOR,
                $perfData['command'],
                INFLUX_FIELDSEPERATOR,
                $types[$i],
                INFLUX_FIELDSEPERATOR,
                "value"
            );
            $alias = $types[$i];
            $panel->addAliasColor($alias, $colors[$i]);
            $panel->addTargetSimple($target, $alias);
            $panel->fillBelowLine($alias, 2);
        }
        $panel->addWarning(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfLabel
        );
        $panel->addCritical(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfLabel
        );
        $panel->addDowntime(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            $perfLabel
        );
        $row->addPanel($panel);

        $dashboard->addRow($row);
    }
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
