<?php
/**
Contains Database Class.
PHP version 5
@category Database_Class
@package Histou\database
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\database;

/**
Influxdb Class.
PHP version 5
@category Database_Class
@package Histou\database
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class Victoriametrics extends JSONDatabase
{
    /**
    Constructs a new Influxdb client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        parent::__construct($url."/api/v1/query?query=");
    }
    /**
    Querys the database for perfdata.
    @returns array.
    **/
    public function fetchPerfData()
    {
        $result = $this->makeGetRequest( sprintf('{host="%s",service="%s"}', HOST, SERVICE ) );
	return $result;
    }

    /**
    Filters the Performancedata out of an database request.
    @param string $request        database request.
    @param string $host           hostname to search for.
    @param string $service        servicename to search for.
    @return array
    **/
    public function filterPerfdata($request, $host, $service)
    {
	//FIXME
    ob_start();
    print_r($request);
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, ob_get_contents());
    ob_end_clean();
/**
 Array
(
    [host] => localhost
    [service] => ssh open
    [perfLabel] => Array
        (
            [time] => Array
                (
                    [crit] => 
                    [max] => 10
                    [min] => 0
                    [unit] => s
                    [value] => 0.000245
                    [warn] => 
                )

        )

    [command] => check_tcp
)


    "data": {
        "result": [
            {
                "metric": {
                    "__name__": "metrics_max",
                    "command": "check_tcp",
                    "db": "nagflux",
                    "host": "localhost",
                    "performanceLabel": "time",
                    "service": "ssh open",
                    "unit": "s"
                },
                "value": [
                    1582301019,
                    "10"
                ]
            },
            {
                "metric": {
                    "__name__": "metrics_min",
                    "command": "check_tcp",
                    "db": "nagflux",
                    "host": "localhost",
                    "performanceLabel": "time",
                    "service": "ssh open",
                    "unit": "s"
                },
                "value": [
                    1582301019,
                    "0"
                ]
            },
            {
                "metric": {
                    "__name__": "metrics_value",
                    "command": "check_tcp",
                    "db": "nagflux",
                    "host": "localhost",
                    "performanceLabel": "time",
                    "service": "ssh open",
                    "unit": "s"
                },
                "value": [
                    1582301019,
                    "0.000304"
                ]
            }
        ],
        "resultType": "vector"
    },
    "status": "success"
}

*/
    if ($request == null || empty($request['data']) || empty($request['data']['result'])) {
        return "No data found";
    }
	$data = array('perfLabel' => array());
	foreach ($request['data']['result'] as $series) {
		fwrite($stderr, sprintf("%s\n", $series['metric']['__name__']));
		$data['host'] = $series['metric']['host'];
		$data['service'] = $series['metric']['service'];
		$data['command'] = $series['metric']['command'];
		$label = $series['metric']['performanceLabel'];
		if(!array_key_exists($label, $data['perfLabel'])) {
			$data['perfLabel'][$label] = array();
		}
		if(!empty($series['metric']['unit'])) {
			$unit = $series['metric']['unit'];
			$data['perfLabel'][$label]['unit'] = $unit;
		}
		$field = preg_replace('/^metrics_/','',$series['metric']['__name__']);
        $data['perfLabel'][$label][$field] = 0;
        $data['perfLabel'][$label]['value'] = $series['value'][1];
	}

    uksort($data['perfLabel'], "strnatcmp");

	//FIXME
    ob_start();
    print_r($data);
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, ob_get_contents());
    ob_end_clean();

	return $data;
    }
}
