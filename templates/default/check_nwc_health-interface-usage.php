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
    $host = '.*',
    $service = '.*',
    $command = '.*',
    $perfLabel = array('^.*_usage_in$', '^.*_usage_out$', '^.*_traffic_in$', '^.*_traffic_out$')
);

$genTemplate = function ($perfData) {
    $perfKeys = array_keys($perfData['perfLabel']);
    $dashboard = new \histou\grafana\Dashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    $interfaces = array();
    foreach ($perfData['perfLabel'] as $key => $value) {
        if (preg_match(';^(.*?)_\w+?_\w+?$;', $key, $hit)) {
            array_push($interfaces, $hit[1]);
        }
    }
    $interfaces = array_unique($interfaces);
    foreach ($interfaces as $interface) {
        $row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);
        foreach (array('usage', 'traffic') as $type) {
            $panel = new \histou\grafana\GraphPanel($perfData['service'].' '.$interface.' '. $type);
            $panel->setSpan(6);
            foreach (array('in', 'out') as $direction) {
                $perfLabel = $interface.'_'.$type.'_'.$direction;
                $target = sprintf(
                    '%s%s%s%s%s%s%s%s%s',
                    $perfData['host'],
                    INFLUX_FIELDSEPERATOR,
                    $perfData['service'],
                    INFLUX_FIELDSEPERATOR,
                    $perfData['command'],
                    INFLUX_FIELDSEPERATOR,
                    $perfLabel,
                    INFLUX_FIELDSEPERATOR,
                    "value"
                );
                $alias = $perfLabel." value";
                $panel->addTargetSimple($target, $alias);
                $panel->fillBelowLine($alias, 2);
                if ($direction == 'out') {
                    $panel->negateY($alias);
                    $panel->addAliasColor($alias, '#07ff78');
                } else {
                    $panel->addAliasColor($alias, '#085DFF');
                }
                $panel->addDowntime($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel);
                if ($type == 'usage') {
                    $aliasWarn = $direction.'-warn';
                    $aliasCrit = $direction.'-crit';
                    $panel->addWarning($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel, $aliasWarn);
                    $panel->addCritical($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel, $aliasCrit);
                    if ($direction == 'out') {
                        $panel->negateY('/'.$aliasWarn.'.*/');
                        $panel->negateY('/'.$aliasCrit.'.*/');
                    }
                }

            }
            if (isset($perfData['perfLabel'][$perfLabel]['value']['unit'])) {
                $panel->setLeftUnit($perfData['perfLabel'][$perfLabel]['value']['unit']);
            }
            $row->addPanel($panel);
        }
        $dashboard->addRow($row);
    }
    return $dashboard;
};
