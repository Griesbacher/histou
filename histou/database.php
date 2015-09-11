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
    private $_url;

    /**
    Constructs a new Influxdb client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        $this->_url = $url."&q=";
    }

    /**
    Querys the database with the given request.
    @param string $query db query.
    @return string
    **/
    public function makeRequest($query)
    {
        $content = file_get_contents($this->_url.urlencode($query));
        if ($content === false) {
            returnData('Influxdb not reachable', 1, 'Influxdb not reachable');
        } else {
            return json_decode($content, true)['results'];
        }
    }

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
            preg_quote($host, '/'), $fieldSeperator,
            preg_quote($service, '/'), $fieldSeperator,
            $fieldSeperator, $fieldSeperator
        );
        $data = array('host' => $host, 'service' => $service);
        foreach ($request as $queryResult) {
            if (!empty($queryResult['series'])) {
                foreach ($queryResult['series'] as $table) {
                    if (preg_match($regex, $table['name'], $result)) {
                        if (!array_key_exists('perfLabel', $data)) {
                            $data['perfLabel'] = array();
                        }
                        if (!array_key_exists($result[2], $data['perfLabel'])) {
                            $data['perfLabel'][$result[2]] = array();
                        }
                        $data['command'] = $result[1];
                        $data['perfLabel'][$result[2]][$result[3]] = array();
                        if (array_key_exists('columns', $table)) {
                            for ($tagId = 1; $tagId < sizeof($table['columns']); $tagId++) {
                                $data['perfLabel'][$result[2]][$result[3]][$table['columns'][$tagId]] = $table['values'][0][$tagId];
                            }
                        }
                    }
                }
            } else {
                return array($queryResult['error']);
            }
        }
        if (isset($data['perfLabel'])) {
            ksort($data['perfLabel'], SORT_NATURAL);
            foreach ($data['perfLabel'] as &$perfLabel) {
                uksort($perfLabel, "Influxdb::_comparePerfLabel");
            }
        }
        return $data;
    }

    /**
    Index for PerformanceLabels to sort them.
    @param string $label PerformanceLabel.
    @return int.
    **/
    private static function _getPerfLabelIndex($label)
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
        }
        return 0;
    }

    /**
    Sort function for PerformanceLabels.
    @param string $firstLabel  first.
    @param string $secondLabel second.
    @return int
    **/
    private static function _comparePerfLabel($firstLabel, $secondLabel)
    {
        $first = Influxdb::_getPerfLabelIndex($firstLabel);
        $second = Influxdb::_getPerfLabelIndex($secondLabel);
        if ($first == $second) {
            return 0;
        }
        return ($first < $second) ? -1 : 1;
    }
}
