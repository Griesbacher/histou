<?php
/**
Template to display NSClient disk usage with percentage and other units.
PHP version 5
@category Template_File
@package templates/custom
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

//TODO: change the rule to your needs
$rule = new \histou\template\Rule(
    $host = '.*',
    $service = 'windows_disk',
    $command = '.*',
    $perfLabel = array('.*')
);

$genTemplate = function ($perfData) {
    $dashboard = new \histou\grafana\Dashboard($perfData['host'].'-'.$perfData['service']);
    $perfLabelWithPercentage = array();
    $perfLabelWithoutPercentage = array();
    $areWarnCritEqual = true;
    $warnOld = "";
    $critOld = "";
    foreach ($perfData['perfLabel'] as $key => $value) {
        if ($value['value']['unit'] == '%') {
            $perfLabelWithPercentage[$key] = $value;
            if (isset($warnOld) && isset($critOld) && ($warnOld != $value['warn']['value'] || $critOld != $value['crit']['value'])) {
                $areWarnCritEqual = false;
            }
            $warnOld = $value['warn']['value'];
            $critOld = $value['crit']['value'];
        } else {
            $perfLabelWithoutPercentage[$key] = $value;
        }
    }

    $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
    $panel = new \histou\grafana\GraphPanel($perfData['host'].' '.$perfData['service'].' %');
    $colors = array( "#7EB26D", "#EAB839", "#6ED0E0", "#1F78C1", "#BA43A9", "#508642", "#CCA300", "#447EBC", "#C15C17");
    $i = 0;
    $amountOfColors = sizeof($colors);
    foreach ($perfLabelWithPercentage as $key => $value) {
        $i++;
        $target = sprintf(
            '%s%s%s%s%s%s%s%s%s',
            $perfData['host'],
            INFLUX_FIELDSEPERATOR,
            $perfData['service'],
            INFLUX_FIELDSEPERATOR,
            $perfData['command'],
            INFLUX_FIELDSEPERATOR,
            $key,
            INFLUX_FIELDSEPERATOR,
            "value"
        );
        $panel->addDowntime($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $alias = $perfData['host']." ".$perfData['service']." ".$key." value";
        $panel->addAliasColor($alias, $colors[$i % $amountOfColors]);
        $panel->addTargetSimple($target, $alias);
        $panel->setLeftUnit('%');
    }
    if ($areWarnCritEqual) {
        $first = array_keys($perfLabelWithPercentage)[0];
        $panel->addWarning($perfData['host'], $perfData['service'], $perfData['command'], $first);
        $panel->addCritical($perfData['host'], $perfData['service'], $perfData['command'], $first);
    }
    $row->addPanel($panel);
    $dashboard->addRow($row);

    foreach ($perfLabelWithoutPercentage as $key => $value) {
        $row = new \histou\grafana\Row($perfData['host'].' '.$perfData['service'].' '.$perfData['command']);
        $panel = new \histou\grafana\GraphPanel($perfData['host'].' '.$key);
        $target = sprintf(
            '%s%s%s%s%s%s%s%s%s',
            $perfData['host'],
            INFLUX_FIELDSEPERATOR,
            $perfData['service'],
            INFLUX_FIELDSEPERATOR,
            $perfData['command'],
            INFLUX_FIELDSEPERATOR,
            $key,
            INFLUX_FIELDSEPERATOR,
            "value"
        );
        $alias = $perfData['host']." ".$perfData['service']." ".$key." value";
        $panel->addAliasColor($alias, '#085DFF');
        $panel->fillBelowLine($alias, 2);
        $panel->addTargetSimple($target, $alias);
        if (isset($value['value']['unit'])) {
            $panel->setLeftUnit($value['value']['unit']);
        }
        $panel->addWarning($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $panel->addCritical($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $panel->addDowntime($perfData['host'], $perfData['service'], $perfData['command'], $key);
        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    return $dashboard;
};
