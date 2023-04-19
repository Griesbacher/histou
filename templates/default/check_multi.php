<?php
/**
This template is used for check_multi.
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
    $perfLabel = array('^.*?::.*?::.*')
);

$genTemplate = function ($perfData) {
    $colors = array('#085DFF', '#07ff78', "#BA43A9", "#C15C17");
    $dashboard = \histou\grafana\dashboard\DashboardFactory::generateDashboard($perfData['host'].'-'.$perfData['service']);
    $dashboard->addDefaultAnnotations($perfData['host'], $perfData['service']);
    $templateNamePlugin = 'Plugin';
    $dashboard->addTemplateForPerformanceLabel(
        $templateNamePlugin,
        $perfData['host'],
        $perfData['service'],
        '^(?!check)(.*?)::.*$',
        false,
        false
    );
    $tempalteVariableStringPerfLabelPlugin = $dashboard->genTemplateVariable($templateNamePlugin);
    $templateNamePerfLabel = 'PerfLabel';
    $dashboard->addTemplate(
        INFLUXDB_DB,
        $templateNamePerfLabel,
        sprintf(
            'SHOW TAG VALUES WITH KEY = "performanceLabel" WHERE "host" = \'%s\' AND "service" = \'%s\' AND performanceLabel =~ /%s.*/',
            $perfData['host'],
            $perfData['service'],
            $tempalteVariableStringPerfLabelPlugin
        ),
        '',
        true,
        true,
        2
    );
    $tempalteVariableStringPerfLabel = $dashboard->genTemplateVariable($templateNamePerfLabel);
    $database = "";

    foreach ($perfData['perfLabel'] as $key => $value) {
        if (preg_match(';^db_(.*?)_log_free$;', $key, $hit)) {
                $database = $key;
                break;
        }
    }

    $statesRow = new \histou\grafana\Row("Overallstate", "200px");
    $statesPanel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel("check_multi overview");
    $overviewData = array(
        'count_ok' => array('#00FF33', "OK"),
        'count_warning' => array('#FFFC15', "Warning"),
        'count_critical' => array('#FF3727', "Critical"),
        'count_unknown' => array('#FF9E00', "Unknown")
    );
    foreach ($overviewData as $label => $data) {
        $statesPanel->addTarget(
            $statesPanel->genTargetSimple(
                $perfData['host'],
                $perfData['service'],
                $perfData['command'],
                "check_multi_extended::check_multi_extended::$label",
                $data[0],
                $data[1]
            )
        );
    }
    $statesPanel->addTarget(
        $statesPanel->genTargetSimple(
            $perfData['host'],
            $perfData['service'],
            $perfData['command'],
            "check_multi::check_multi::plugins",
            '#085DFF',
            "Plugins"
        )
    );
    $statesPanel->setSpan(11);
    $statesPanel->setLeftYAxisMinMax(0);
    $statesRow->addPanel($statesPanel);

    $overallState = \histou\grafana\singlestatpanel\SinglestatPanelFactory::generatePanel("");
    $overallState->setSpan(1);
    $overallState->addTarget($overallState->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], 'check_multi_extended::check_multi_extended::overall_state'));
    $overallState->setColor(array('#99ff66', '#ffc125', '#ee0000'));
    $overallState->setThresholds("1", "2");
    $overallState->addRangeToTextElement(0, 0.5, 'OK');
    $overallState->addRangeToTextElement(0.5, 1.5, 'Warn');
    $overallState->addRangeToTextElement(1.5, 2.5, 'Crit');
    $overallState->addRangeToTextElement(2.5, 3.5, 'Unkn');
    $statesRow->addPanel($overallState);
    $dashboard->addRow($statesRow);
    


    $row = new \histou\grafana\Row($tempalteVariableStringPerfLabel);
    $panel = \histou\grafana\graphpanel\GraphPanelFactory::generatePanel($tempalteVariableStringPerfLabel);
    $target = $panel->genTargetSimple($perfData['host'], $perfData['service'], $perfData['command'], $tempalteVariableStringPerfLabel);
    $target = $panel->addWarnToTarget($target, $tempalteVariableStringPerfLabel);
    $target = $panel->addCritToTarget($target, $tempalteVariableStringPerfLabel);
    $panel->addTarget($target);

    $downtime = $panel->genDowntimeTarget($perfData['host'], $perfData['service'], $perfData['command'], $tempalteVariableStringPerfLabel);
    $panel->addTarget($downtime);
    $row->addPanel($panel);
    $row->setCustomProperty("repeat", $templateNamePerfLabel);
    $dashboard->addRow($row);
    
    return $dashboard;
};
