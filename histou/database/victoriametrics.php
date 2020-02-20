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
        parent::__construct($url."/api/v1/series?match[]=");
    }
/**  curl -G -X GET -s http://localhost:8428/api/v1/series --data-urlencode match[]='{host="localhost",service="load"}' **/
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


Array
(
    [status] => success
    [data] => Array
        (
            [0] => Array
                (
                    [__name__] => metrics_max
                    [service] => ssh open
                    [db] => nagflux
                    [host] => localhost
                    [command] => check_tcp
                    [performanceLabel] => time
                    [unit] => s
                )

            [1] => Array
                (
                    [__name__] => metrics_value
                    [service] => ssh open
                    [db] => nagflux
                    [host] => localhost
                    [command] => check_tcp
                    [performanceLabel] => time
                    [unit] => s
                )

            [2] => Array
                (
                    [__name__] => metrics_min
                    [service] => ssh open
                    [db] => nagflux
                    [host] => localhost
                    [command] => check_tcp
                    [performanceLabel] => time
                    [unit] => s
                )

        )

)

*/
        if ($request == null || empty($request['data'])) {
            return "No data found";
        }
	$data = array('perfLabel' => array());
	foreach ($request['data'] as $series) {
		fwrite($stderr, sprintf("%s\n", $series['__name__']));
		$data['host'] = $series['host'];
		$data['service'] = $series['service'];
		$data['command'] = $series['command'];
		$label = $series['performanceLabel'];
		if(!array_key_exists($label, $data['perfLabel'])) {
			$data['perfLabel'][$label] = array();
		}
		if(!empty($series['unit'])) {
			$unit = $series['unit'];
			$data['perfLabel'][$label]['unit'] = $unit;
		}
		$field = preg_replace('/^metrics_/','',$series['__name__']);
		$data['perfLabel'][$label][$field] = 0;
	}

        uksort($data['perfLabel'], "strnatcmp");
	return $data;
    }
}
