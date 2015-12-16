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

define(
    "INFLUX_QUERY",
    sprintf(
        "show series from /%s%s%s.*/",
        str_replace("/", '\/', HOST),
        INFLUX_FIELDSEPERATOR,
        str_replace('/', '\/', SERVICE)
    )
);

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
    }

    public function fetchPerfData()
    {
        return $this->makeRequest(
            sprintf(
                "select * from /%s%s%s%s.*/ ORDER BY time DESC limit 1",
                str_replace("/", '\/', HOST),
                INFLUX_FIELDSEPERATOR,
                str_replace('/', '\/', SERVICE),
                INFLUX_FIELDSEPERATOR
            )
        );
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

    const COLUMNS = 'columns';
    const PERF_LABEL = 'perfLabel';
    /**
    Filters the Performancedata out of an database request.
    @param string $request        database request.
    @param string $host           hostname to search for.
    @param string $service        servicename to search for.
    @param string $fieldSeperator database fieldSeperator.
    @return array
    **/
    public function filterPerfdata($request, $host, $service, $fieldSeperator)
    {
        $regex = sprintf(
            "/%s%s%s%s(.*?)%s(.*?)%s(.*)/",
            preg_quote($host, '/'),
            $fieldSeperator,
            preg_quote($service, '/'),
            $fieldSeperator,
            $fieldSeperator,
            $fieldSeperator
        );
        $data = array('host' => $host, 'service' => $service);
        foreach ($request as $queryResult) {
            if (!empty($queryResult['series'])) {
                foreach ($queryResult['series'] as $table) {
                    if (preg_match($regex, $table['name'], $result)) {
                        if (!array_key_exists(static::PERF_LABEL, $data)) {
                            $data[static::PERF_LABEL] = array();
                        }
                        if (!array_key_exists($result[2], $data[static::PERF_LABEL])) {
                            $data[static::PERF_LABEL][$result[2]] = array();
                        }
                        $data['command'] = $result[1];
                        $data[static::PERF_LABEL][$result[2]][$result[3]] = array();
                        if (array_key_exists(static::COLUMNS, $table)) {
                            for ($tagId = 1; $tagId < sizeof($table[static::COLUMNS]); $tagId++) {
                                $data[static::PERF_LABEL][$result[2]][$result[3]][$table[static::COLUMNS][$tagId]] = $table['values'][0][$tagId];
                            }
                        }
                    }
                }
            } else {
                return "No datafound";
            }
        }
        if (isset($data[static::PERF_LABEL])) {
            ksort($data[static::PERF_LABEL], SORT_NATURAL);
            foreach ($data[static::PERF_LABEL] as &$perfLabel) {
                uksort($perfLabel, get_class($this).'::comparePerfLabel');
            }
        }
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
