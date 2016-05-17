<?php
$rule = new \histou\template\Rule(
    $host = '*',
    $service = 'http',
    $command = 'http',
    $perfLabel = array('size', 'time')
);

$forecast = <<<EOF
[
   {
      "label":"time",
      "method":"movingAverage",
      "methodSpecificOptions":{
         "groupSize":10
      },
      "lookback":"3h",
      "forecast":"1h"
   },
   {
      "label":"size",
      "method":"movingAverage",
      "methodSpecificOptions":{
         "groupSize":20
      },
      "lookback":"6h",
      "forecast":"2h"
   }
]
EOF;
