<?php
/**
Contains Database Class.
PHP version 5
@category Database_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/
namespace histou\database;

/**
Influxdb Class.
PHP version 5
@category Database_Class
@package Histou
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/Griesbacher/histou
**/

class Influxdb
{
    private $url;

    /**
    Constructs a new Influxdb client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        $this->url = $url."&q=";
        $this->perfKeys = array(
            'value',
            'warn', 'warn-min', 'warn-max',
            'crit', 'crit-min', 'crit-max',
            'min',
            'max',
            'type',
            'unit',
            'fill'
        );
    }

    public function fetchPerfData()
    {
        $result = $this->makeRequest(
            sprintf(
                "select * from metrics where host='%s' and service='%s' GROUP BY performanceLabel ORDER BY time DESC LIMIT 1",
                HOST,
                SERVICE
            ).';'.sprintf(
                "select * from metrics where host='%s' and service='%s' GROUP BY performanceLabel LIMIT 1",
                HOST,
                SERVICE
            )
        );
        if (empty($result[0])) {
            return $result[1];
        } else {
            return $result[0];
        }
    }

    /**
    Querys the database with the given request.
    @param string $query db query.
    @return string
    @codeCoverageIgnore
    **/
    public function makeRequest($query)
    {
        try {
            $content = file_get_contents($this->url.urlencode($query));
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
        return json_decode($content, true)['results'];
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
		ksort($data['perfLabel'], SORT_NATURAL);
        return $data;
    }

    /**
    Index for PerformanceLabels to sort them.
    @param string $label PerformanceLabel.
    @return int.
    **/
    private static function getPerfLabelIndex($label)
    {
        switch($label) {
            case 'value':
                return 1;
            case 'warn':
                return 2;
            case 'crit':
                return 3;
            case 'min':
                return 4;
            case 'max':
                return 5;
            default:
        }
        return 0;
    }

    /**
    Sort function for PerformanceLabels.
    @param string $firstLabel  first.
    @param string $secondLabel second.
    @return int
    **/
    private static function comparePerfLabel($firstLabel, $secondLabel)
    {
        $first = Influxdb::getPerfLabelIndex($firstLabel);
        $second = Influxdb::getPerfLabelIndex($secondLabel);
        return ($first < $second) ? -1 : 1; //equals not possible due to key sorting
    }
}
