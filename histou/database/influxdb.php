<?php
/**
Contains Database Class.
PHP version 5
@category Database_Class
@package Histou\database
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/
namespace histou\database;

/**
Influxdb Class.
PHP version 5
@category Database_Class
@package Histou\database
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/

class Influxdb extends JSONDatabase
{
    /**
    Constructs a new Influxdb client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        parent::__construct($url."&q=");
    }

    /**
    Querys the database for perfdata.
    @returns array.
    **/
    public function fetchPerfData()
    {
        $seriesLimit = 100;
        $result = $this->makeGetRequest(
            sprintf(
                "select * from metrics where host='%s' and service='%s' GROUP BY performanceLabel ORDER BY time DESC LIMIT 1 SLIMIT ".($seriesLimit + 1),
                HOST,
                SERVICE
            ).';'.sprintf(
                "select * from metrics where host='%s' and service='%s' GROUP BY performanceLabel LIMIT 1 SLIMIT ".($seriesLimit + 1),
                HOST,
                SERVICE
            )
        );
        if (empty($result['results'])) {
            return null;
        } elseif (empty($result['results'][0])) {
            return $result['results'][1];
        } else {
            if(empty($result["results"][0]["series"])) {
                return null;
            }            
            if(count($result['results'][0]["series"]) > $seriesLimit) {
                $result['results'][0]["series"][$seriesLimit]["tags"]["performanceLabel"] = "{ERROR: MORE THAN $seriesLimit SERIES FOUND}"; # add brackets so it appears as last item
                $result['results'][0]["series"][$seriesLimit]["values"][0][1] = ""; # command name
            }
            return $result['results'][0];
        }
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
        if ($request == null || empty($request['series'])) {
            return "No data found";
        }
        $data = array('host' => $host, 'service' => $service, 'perfLabel' => array());
        foreach ($request['series'] as $series) {
            $labelData = array();
            foreach ($series['columns'] as $index => $value) {
                if (in_array($value, $this->perfKeys)) {
                    $labelData[$value] = $series['values'][0][$index];
                } elseif ($value == 'command') {
                    $data['command'] = $series['values'][0][$index];
                }
            }
            $data['perfLabel'][$series['tags']['performanceLabel']] = $labelData;
        }
        uksort($data['perfLabel'], "strnatcmp");
        return $data;
    }
}
