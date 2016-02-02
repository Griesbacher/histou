<?php
/**
This template is used for check_nwc_health with --mode interface-*.
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
    $perfLabel = array('^.*?_(usage|traffic|errors|discards)_(in|out)$')
);

$genTemplate = function ($perfData) {
    $dashboard = new \histou\grafana\Dashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    $interfaces = array();
    $types = array();
    foreach ($perfData['perfLabel'] as $key => $value) {
        if (preg_match(';^(.*?)_(\w+?)_\w+$;', $key, $hit)) {
            array_push($interfaces, $hit[1]);
            array_push($types, $hit[2]);
        }
    }
    $interfaces = array_unique($interfaces);
    $types = array_unique($types);
    usort($types, function ($firstLabel, $secondLabel) {
        $index = function ($label) {
            switch($label) {
                case 'usage':
                    return 0;
                case 'traffic':
                    return 1;
                case 'errors':
                    return 2;
                case 'discards':
                    return 3;
                default:
                    return 4;
            }
        };
        return ($index($firstLabel) - $index($secondLabel)) ? -1 : 1;
    });
    $row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);
	$numberPanels = 0;
    foreach ($interfaces as $interface) {
        foreach ($types as $type) {
            $panel = new \histou\grafana\GraphPanel($perfData['service'].' '.$interface.' '. $type);
            $panel->setSpan(6);

            foreach (array('in', 'out') as $direction) {
                $perfLabel = $interface.'_'.$type.'_'.$direction;
				$target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel);
				$panel->addTarget($panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel));
				$alias = $perfLabel.'-value';
                if ($direction == 'out') {
                    $panel->negateY($alias);
                    $panel->addAliasColor($alias, '#4707ff');
                } else {
                    $panel->addAliasColor($alias, '#085DFF');
                }
                if ($type != 'traffic') {
					$target = $panel->addWarnToTarget($target, $perfLabel);
					$target = $panel->addCritToTarget($target, $perfLabel);
                    if ($direction == 'out') {
                        $panel->negateY("$perfLabel-warn");
                        $panel->negateY("$perfLabel-crit");
                    }
                }
				$panel->addTarget($target);
            }
            if (isset($perfData['perfLabel'][$perfLabel]['value']['unit'])) {
                $panel->setLeftUnit($perfData['perfLabel'][$perfLabel]['value']['unit']);
            }
			if ($numberPanels != 0 && $numberPanels % 2 == 0) {

				$dashboard->addRow($row);
				$row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);
			}
			$row->addPanel($panel);
			$numberPanels++;
        }
    }
    $dashboard->addRow($row);
    return $dashboard;
};
