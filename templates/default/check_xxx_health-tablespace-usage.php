<?php
/**
This template is used for check_oracle_health/check_db2_health mode: tabelspace-usage.
PHP version 5
@category Template_File
@package Histou/templates/default
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/

$rule = new \histou\template\Rule(
    $host = '.*',
    $service = '.*',
    $command = '.*',
    $perfLabel = array('^tbs_(.*?)_usage(_pct)?$')
);

$genTemplate = function ($perfData) {
    $colors = array('#085DFF', '#07ff78', "#BA43A9", "#C15C17");
    $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    $templateName = 'Database';
    $dashboard->addTemplateForPerformanceLabel(
        $templateName,
        $perfData['host'],
        $perfData['service'],
        $regex = '^tbs_(.*?)_usage$',
        $multiFormat = true,
        $includeAll = false
    );
    $templateVariableString = $dashboard->genTemplateVariable($templateName);
    $database = "";

    foreach ($perfData['perfLabel'] as $key => $value) {
        if (preg_match(';^tbs_(.*?)_usage$;', $key, $hit)) {
                $database = $key;
                break;
        }
    }
    $types = array(
                'pct' => array("usage_pct"),
                'fix' => array("usage")
            );
    $numberPanels = 0;
    $row = new \histou\grafana\Row($perfData['service'].' '.$perfData['command']);

    foreach ($types as $type => $labels) {
        $panel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel($perfData['service']." $templateVariableString ". $type);
        $panel->setSpan(6);
        if ($type == 'fix' && isset($perfData['perfLabel'][$database]['unit'])) {
            $panel->setLeftUnit($perfData['perfLabel'][$database]['unit']);
        } elseif ($type == 'pct') {
            $panel->setLeftUnit('%');
        }

        $currentColorIndex = 0;
        foreach ($labels as $label) {
            $perfLabel = "tbs_".$templateVariableString."_".$label;
            $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel);
            $panel->addTarget($panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $perfLabel));
            $target = $panel->addWarnToTarget($target, $perfLabel, false);
            $target = $panel->addCritToTarget($target, $perfLabel, false);
            $panel->addTarget($target);
            $panel->addRegexColor("/^tbs_.*?_$label-value$/", $colors[$currentColorIndex++]);
        }
        $panel->addRegexColor('/^tbs_.*?-warn-?(min|max)?$/', '#FFFC15');
        $panel->addRegexColor('/^tbs_.*?-crit-?(min|max)?$/', '#FF3727');

        $row->addPanel($panel);
    }
    $row->setCustomProperty("repeat", $templateName);
    $dashboard->addRow($row);
    return $dashboard;
};
