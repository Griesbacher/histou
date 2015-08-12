<?php
$rule = new Rule(
    $host = '/^$/',
    $service = '/^$/',
    $command = '/^$/',
    $perfLable = array()
);

$genTemplate = function ($perfData) {
    /*$perfData:
    Array
    (
				[host] => Host2
				[service] => ping4
				[command] => ping4
				[perfLabel] => Array
					(
						[pl] => Array
							(
								[0] => crit
								[1] => min
								[2] => value
								[3] => warn
							)

						[rta] => Array
							(
								[0] => crit
								[1] => min
								[2] => value
								[3] => warn
							)

					)
    )
    */
    $perfKeys = array_keys($perfData['perfLabel']);
    $dashboard = new Dashboard($perfData['host']);
    for ($i = 0; $i < sizeof($perfData['perfLabel']); $i++) {
        $row = new Row($perfData['service'].' '.$perfData['command']);
        $panel = new GraphPanel($perfData['service'].' '.$perfData['command'].' '.$perfKeys[$i]);
        foreach ($perfData['perfLabel'][$perfKeys[$i]] as $type) {
            if ($type != 'min' && $type != 'max') {
				$target = sprintf('%s%s%s%s%s%s%s%s%s', $perfData['host'], INFLUX_FIELDSEPERATOR, $perfData['service'], INFLUX_FIELDSEPERATOR, $perfData['command'], INFLUX_FIELDSEPERATOR, $perfKeys[$i], INFLUX_FIELDSEPERATOR, $type);
                $alias = $perfData['host']." ".$perfData['service']." ".$perfKeys[$i]." ".$type;
				$panel->addTargetSimple($target, $alias);
				if($type == 'crit'){
					$panel->addAliasColor($alias, '#FF0000');
				}else if($type == 'warn'){
					$panel->addAliasColor($alias, '#FFFF00');
				}else if($type == 'value'){
					$panel->addAliasColor($alias, '#FFFFFF');
				}					
            }
        }
        $row->addPanel($panel);
        $dashboard->addRow($row);
    }
    return $dashboard;
};
?>