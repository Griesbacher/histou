<?php
$rule = new Rule(
    $host = ';.*;',
    $service = ';.*;',
    $command = ';.*;',
    $perfLable = array(';rta;', ';pl;')
);

$genTemplate = function ($perfData) {
    $perfKeys = array_keys($perfData['perfLabel']);
    
    $dashboard = new Dashboard($perfData['host']);
    for ($i = 0; $i < sizeof($perfData['perfLabel']); $i++) {
        $row = new Row($perfData['service'].' '.$perfData['command']);
        $panel = new GraphPanel($perfData['service'].' '.$perfData['command'].' '.$perfKeys[$i]);
		$panel->setLinewidth(10);
        foreach ($perfData['perfLabel'][$perfKeys[$i]]['identifier'] as $type) {
            if ($type != 'crit' && $type != 'warn' && $type != 'min' && $type != 'max') {
                $panel->addTargetSimple(sprintf('%s%s%s%s%s%s%s%s%s', $perfData['host'], INFLUX_FIELDSEPERATOR, $perfData['service'], INFLUX_FIELDSEPERATOR, $perfData['command'], INFLUX_FIELDSEPERATOR, $perfKeys[$i], INFLUX_FIELDSEPERATOR, $type), $type);
            }
        }
        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    return $dashboard;
};
?>
