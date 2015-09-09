<?php
//Example Template, mostly like the default, exept the rule set and the linewidth
$rule = new Rule(
    $host = '*',
    $service = 'ALL',
    $command = '.*',
    $perfLable = array('rta', 'pl')
);

$genTemplate = function ($perfData) {
    $perfKeys = array_keys($perfData['perfLabel']);
    $dashboard = new Dashboard($perfData['host']);
    for ($i = 0; $i < sizeof($perfData['perfLabel']); $i++) {
        $row = new Row($perfData['service'].' '.$perfData['command']);
        $panel = new GraphPanel($perfData['host'].' '.$perfData['service'].' '.$perfData['command'].' '.$perfKeys[$i]);
        $panel->setLineWidth(10);
        //add value graph
        $target = sprintf('%s%s%s%s%s%s%s%s%s', $perfData['host'], INFLUX_FIELDSEPERATOR, $perfData['service'], INFLUX_FIELDSEPERATOR, $perfData['command'], INFLUX_FIELDSEPERATOR, $perfKeys[$i], INFLUX_FIELDSEPERATOR, "value");
        $alias = $perfData['host']." ".$perfData['service']." ".$perfKeys[$i]." value";
        $panel->addAliasColor($alias, '#FFFFFF');
        $panel->addTargetSimple($target, $alias);
        //Add Lable
        if(isset($perfData['perfLabel'][$perfKeys[$i]]['value']['unit'])){
            $panel->setleftYAxisLabel($perfData['perfLabel'][$perfKeys[$i]]['value']['unit']);
        }
        //Add Warning and Critical
        $panel->addWarning($perfData['host'], $perfData['service'], $perfData['command'], $perfKeys[$i]);
        $panel->addCritical($perfData['host'], $perfData['service'], $perfData['command'], $perfKeys[$i]);
        
        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    return $dashboard;
};
?>
