<?php

namespace tests\template;

class ElasticsearchTest extends \MyPHPUnitFrameworkTestCase
{
    public function testFetchPerfData()
    {
        define('HOST', 'host');
        define('SERVICE', 'service');
        $classname ='\histou\database\Elasticsearch';

        $stub = $this->getMockBuilder($classname)
        ->setConstructorArgs(array('url'))
        ->setMethods(array('makePostRequest'))
        ->getMock();

        $stub->expects($this->once())
        ->method('makePostRequest')
        ->willReturn("ok");
        $this->assertSame("ok", $stub->fetchPerfData());
    }

    public function testFilterPerfdata()
    {
        define('HOST', 'localhost.localdomain');
        define('SERVICE', 'load');
        $elastic = new \histou\database\Elasticsearch('url');
        $perfData = $elastic->filterPerfdata(
            json_decode($this->elasticResult, true),
            HOST,
            SERVICE
        );
        $this->assertSame(HOST, $perfData['host']);
        $this->assertSame(SERVICE, $perfData['service']);
        $this->assertSame('load', $perfData['command']);
        $this->assertSame(3, sizeof($perfData['perfLabel']));

        $error = $elastic->filterPerfdata(
            json_decode($this->emptyResult, true),
            HOST,
            SERVICE
        );
        $this->assertSame("No data found", $error);
        
        $error = $elastic->filterPerfdata(
            json_decode($this->timeout, true),
            HOST,
            SERVICE
        );
        $this->assertSame("Timedout", $error);
    }

    private $emptyResult = '{
  "took": 23,
  "timed_out": false,
  "_shards": {
    "total": 5,
    "successful": 5,
    "failed": 0
  },
  "hits": {
    "total": 0,
    "max_score": null,
    "hits": [

    ]
  },
  "aggregations": {
    "timestamp": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 0,
      "buckets": [

      ]
    }
  }
}';
    private $timeout = '{
  "took": 23,
  "timed_out": true,
  "_shards": {
    "total": 5,
    "successful": 5,
    "failed": 0
  },
  "hits": {
    "total": 0,
    "max_score": null,
    "hits": [

    ]
  },
  "aggregations": {
    "timestamp": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 0,
      "buckets": [

      ]
    }
  }
}';
    private $elasticResult = '{
  "took": 736,
  "timed_out": false,
  "_shards": {
    "total": 5,
    "successful": 5,
    "failed": 0
  },
  "hits": {
    "total": 12846,
    "max_score": 4.4528265,
    "hits": [
      {
        "_index": "nagflux-2016.02",
        "_type": "metrics",
        "_id": "AVMOnCBKjQWpOKqsZAdv",
        "_score": 4.4528265
      }
    ]
  },
  "aggregations": {
    "timestamp": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 12843,
      "buckets": [
        {
          "key": 1456495940000,
          "key_as_string": "2016-02-26T14:12:20.000Z",
          "doc_count": 3,
          "command": {
            "doc_count_error_upper_bound": 0,
            "sum_other_doc_count": 0,
            "buckets": [
              {
                "key": "load",
                "doc_count": 3,
                "performanceLabel": {
                  "doc_count_error_upper_bound": 0,
                  "sum_other_doc_count": 0,
                  "buckets": [
                    {
                      "key": "load1",
                      "doc_count": 1,
                      "warn": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 5,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "unit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 10,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "value": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0.17000000178813934,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      }
                    },
                    {
                      "key": "load15",
                      "doc_count": 1,
                      "warn": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 3,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "unit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 4,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "value": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0.2199999988079071,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      }
                    },
                    {
                      "key": "load5",
                      "doc_count": 1,
                      "warn": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 4,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "unit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0,
                            "doc_count": 1
                          }
                        ]
                      },
                      "crit": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 6,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-max": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "value": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": 0.20000000298023224,
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-fill": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [
                          {
                            "key": "none",
                            "doc_count": 1
                          }
                        ]
                      },
                      "warn-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      },
                      "crit-min": {
                        "doc_count_error_upper_bound": 0,
                        "sum_other_doc_count": 0,
                        "buckets": [

                        ]
                      }
                    }
                  ]
                }
              }
            ]
          }
        }
      ]
    }
  }
}';
}
