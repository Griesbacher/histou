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

class JSONDatabase
{
    protected $url;

    /**
    Constructs a new Influxdb client.
    @param string $url address.
    @return null
    **/
    public function __construct($url)
    {
        $this->url = $url;
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

	/**
	Querys the database for perfdata.
	@returns array.
	**/
	public function fetchPerfData()
    {
		throw new Exception('Not implemented yet');
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
		throw new Exception('Not implemented yet');
	}

    /**
    Querys the database with the given request.
    @param string $query db query.
    @return string
    @codeCoverageIgnore
    **/
    protected function makeGetRequest($query)
    {
        try {
            $content = file_get_contents($this->url.urlencode($query));
        } catch (\ErrorException $e) {
            return $e->getMessage();
        }
        return json_decode($content, true);
    }

	protected function makePostRequest($data)
	{
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = '';
		try {
			$response = curl_exec($ch);
        } catch (\ErrorException $e) {
            $response = $e->getMessage();
        }
		curl_close($ch);
		return json_decode($response, true);
	}
}