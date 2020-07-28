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

        $req=sprintf('last_over_time({__name__=~"metrics.*",host="%s",service="%s"}[1d])', HOST, SERVICE);
        //\histou\Debug::add('request: '. print_r ($req,true)."\n");   
        $result = $this->makeGetRequest( $req  );
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
    if ($request == null || empty($request['data']) || empty($request['data']['result'])) {
        return "No data found";
    }
	$data = array('perfLabel' => array());
        $i=0;
	foreach ($request['data']['result'] as $series) {
                $i+=1;
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
                if(!empty($series['metric']['__name__'])) {

			$field = preg_replace('/^metrics_/','',$series['metric']['__name__']);
		} else {
                        $field = sprintf('metrics%i',$i);
                }

        $data['perfLabel'][$label][$field] = $series['value'][1];
	}
    
    uksort($data['perfLabel'], "strnatcmp");
    \histou\Debug::add('data: '. print_r ($data,true)."\n");
	return $data;
    }
}
