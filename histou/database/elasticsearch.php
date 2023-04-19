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
Elasticsearch Class.
PHP version 5
@category Database_Class
@package Histou\database
@author Philip Griesbacher <griesbacher@consol.de>
@license http://opensource.org/licenses/gpl-license.php GNU Public License
@link https://github.com/ConSol/histou
**/

class Elasticsearch extends JSONDatabase
{
    /**
    Constructs a new Elasticsearch client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        parent::__construct($url."-*/_search");
    }

    /**
    Querys the database for perfdata.
    @returns array.
    **/
    public function fetchPerfData()
    {
        return $this->makePostRequest(
            sprintf(
                '{
  "size": 1,
  "query": {
    "filtered": {
      "query": {
        "query_string": {
          "query": "host: %s AND service: %s"
        }
      }
    }
  },
  "aggs": {
    "timestamp": {
      "terms": {
        "field": "timestamp",
        "size": 1,
        "order": {
          "_term": "desc"
        }
      },
      "aggs": {
        "command": {
          "terms": {
            "field": "command"
          },
          "aggs": {
            "performanceLabel": {
              "terms": {
                "field": "performanceLabel"
              },
              "aggs": {
                "unit": {
                  "terms": {
                    "field": "unit"
                  }
                },
                "value": {
                  "terms": {
                    "field": "value"
                  }
                },
                "warn-max": {
                  "terms": {
                    "field": "warn-max"
                  }
                },
                "warn-fill": {
                  "terms": {
                    "field": "warn-fill"
                  }
                },
                "warn": {
                  "terms": {
                    "field": "warn"
                  }
                },
                "crit-max": {
                  "terms": {
                    "field": "crit-max"
                  }
                },
                "crit-fill": {
                  "terms": {
                    "field": "crit-fill"
                  }
                },
                "min": {
                  "terms": {
                    "field": "min"
                  }
                },
                "crit": {
                  "terms": {
                    "field": "crit"
                  }
                },
                "warn-min": {
                  "terms": {
                    "field": "warn-min"
                  }
                },
                "crit-min": {
                  "terms": {
                    "field": "crit-min"
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}',
                HOST,
                SERVICE
            )
        );
    }

    public function filterPerfdata($request, $host, $service)
    {
        if (!empty($request['timed_out'])) {
            return "Timedout";
        } elseif ($request['hits']['total'] == 0) {
            return "No data found";
        }
        $data = array('host' => $host, 'service' => $service, 'perfLabel' => array());
        $data['command'] = $request['aggregations']['timestamp']['buckets'][0]['command']['buckets'][0]['key'];
        $perfData = $request['aggregations']['timestamp']['buckets'][0]['command']['buckets'][0]['performanceLabel']['buckets'];
        foreach ($perfData as $perfLabel) {
            $values = array();
            foreach ($perfLabel as $key => $value) {
                if (in_array($key, $this->perfKeys) && sizeof($value['buckets']) > 0) {
                    $values[$key] = $value['buckets'][0]['key'];
                }
            }
            $data['perfLabel'][$perfLabel['key']] = $values;
        }
        return $data;
    }
}
