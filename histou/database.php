<?php
class Influxdb
{
    private $url;
    
    public function __construct($url) 
    {
        $this->url = $url."&q=";
    }
    
    public function makeRequest($query)
    {
        $content = file_get_contents($this->url.urlencode($query));
        if ($content === false) {
            returnData('Influxdb not reachable', 1, 'Influxdb not reachable');
        } else {
            return json_decode($content, true);
        }
    }
    
    public function filterPerfdata($request, $host, $service, $fieldSeperator)
    {
        $regex = sprintf("/%s%s%s%s(.*?)%s(.*?)%s(.*)/", $host, $fieldSeperator, $service, $fieldSeperator, $fieldSeperator, $fieldSeperator);
        $data = array('host' => $host, 'service' => $service);		
		foreach ($request['results'] as $queryResult) {
			if (!empty($queryResult)) {
				foreach ($queryResult['series'] as $table) {
					if (preg_match($regex, $table['name'], $result)) {
						if (!array_key_exists('perfLabel', $data)) {                    
							$data['perfLabel'] = array();
						}
						if (!array_key_exists($result[2], $data['perfLabel'])) {
							$data['perfLabel'][$result[2]] = array();
						}
						array_push($data['perfLabel'][$result[2]], $result[3]);
						$data['command'] = $result[1];
					}
				}
			}
		}
        return $data;
    }
}
?>
