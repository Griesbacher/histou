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
    $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    $templeQuery = 'SHOW TAG VALUES WITH KEY = "performanceLabel" WHERE "host" = \''.$perfData['host'].'\' AND "service" = \''.$perfData['service'].'\'';
    $templateName = 'Interface';
    $dashboard->addTemplateForPerformanceLabel(
        $templateName,
        $perfData['host'],
        $perfData['service'],
        $regex = '^(.*?)_(\w+?)_\w+$',
        $multiFormat = true,
        $includeAll = false
    );
    $tempalteVariableString = $dashboard->genTemplateVariable($templateName);

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
    foreach ($types as $type) {
        $panel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel($perfData['service']." $tempalteVariableString ". $type);
        $panel->setSpan(6);

        if (isset($perfData['perfLabel'][$interfaces[0].'_'.$type.'_in']['unit'])) {
            $panel->setLeftUnit($perfData['perfLabel'][$interfaces[0].'_'.$type.'_in']['unit']);
        }
        foreach (array('in', 'out') as $direction) {
            if (DATABASE_TYPE == ELASTICSEARCH) { //https://github.com/grafana/grafana/issues/4075
                $perfLabel = $tempalteVariableString."\_".$type.'_'.$direction;
            } else {
                $perfLabel = $tempalteVariableString."_".$type.'_'.$direction;
            }
            $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel);
            $panel->addTarget($panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel));
            if ($type != 'traffic') {
                $target = $panel->addWarnToTarget($target, $perfLabel, false);
                $target = $panel->addCritToTarget($target, $perfLabel, false);
            }
            $panel->addTarget($target);
        }
        $panel->negateY("/.*?_out-.*/");
        $panel->addRegexColor('/^.*?_(usage|traffic|errors|discards)_in-value$/', '#085DFF');
        $panel->addRegexColor('/^.*?_(usage|traffic|errors|discards)_out-value$/', '#4707ff');
        $panel->addRegexColor('/^.*?_(usage|traffic|errors|discards)_(in|out)-warn-?(min|max)?$/', '#FFFC15');
        $panel->addRegexColor('/^.*?_(usage|traffic|errors|discards)_(in|out)-crit-?(min|max)?$/', '#FF3727');
        if (isset($perfData['perfLabel'][$perfLabel]['value']['unit'])) {
            $panel->setLeftUnit($perfData['perfLabel'][$perfLabel]['value']['unit']);
        }
        if ($numberPanels != 0 && $numberPanels % 2 == 0) {
            $row->setCustomProperty("repeat", $templateName);
            $dashboard->addRow($row);
            $row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);
        }
        $row->addPanel($panel);
        $numberPanels++;
    }
    $row->setCustomProperty("repeat", $templateName);
    $dashboard->addRow($row);
    return $dashboard;
};
