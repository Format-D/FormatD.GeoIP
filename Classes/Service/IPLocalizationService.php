<?php
namespace FormatD\GeoIP\Service;

/*
 * This file is part of the FormatD.GeoIP package.
 */

use GeoIp2\Model\City;
use GeoIp2\Model\Country;
use GeoIp2\Model\Insights;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Http\HttpRequestHandlerInterface;
use Neos\Flow\Log\PsrSystemLoggerInterface;
use GeoIp2\WebService\Client;

/**
 * Service for handling localization information of an ip address
 *
 * @Flow\Scope("singleton")
 */
class IPLocalizationService {

	const SUPPORTED_ENDPOINTS = ['country', 'city', 'insights'];

	/**
	 * @Flow\Inject
	 * @var \Neos\Cache\Frontend\VariableFrontend
	 */
	protected $cacheFrontend;

	/**
	 * @Flow\InjectConfiguration(path="geoIpUserId")
	 * @var string
	 */
	protected $geoIpUserId;

	/**
	 * @Flow\InjectConfiguration(path="geoIpLicense")
	 * @var string
	 */
	protected $geoIpLicense;

	/**
	 * @Flow\InjectConfiguration(path="debug.enable")
	 * @var boolean
	 */
	protected $debug;

	/**
	 * @Flow\InjectConfiguration(path="debug.simulateIpAddress")
	 * @var string
	 */
	protected $debugSimulateIpAddress;

	/**
	 * @Flow\Inject
	 * @var PsrSystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var Country
	 */
	protected $country;

	/**
	 * @var City
	 */
	protected $city;

	/**
	 * @var Insights
	 */
	protected $insights;

	/**
	 * Return the geoip country Model
	 *
	 * @return Country
	 */
	public function getCountry()
	{
		return $this->getGeoIPData('country');
	}

	/**
	 * Return the geoip city Model
	 *
	 * @return City
	 */
	public function getCity()
	{
		return $this->getGeoIPData('city');
	}

	/**
	 * Return the geoip insights Model
	 *
	 * @return Insights
	 */
	public function getInsights()
	{
		return $this->getGeoIPData('insights');
	}

	/**
	 * @param string $endpoint
	 * @return object
	 * @throws \FormatD\GeoIP\Exception
	 */
	protected function getGeoIPData($endpoint)
	{
		if (array_search($endpoint, self::SUPPORTED_ENDPOINTS)  === false) {
			throw new \FormatD\GeoIP\Exception('Endpoint "' . $endpoint . '" not supported', 1552917446);
		}

		if (isset($this->$endpoint)) {
			return $this->$endpoint;
		}

		$ip = $this->getIPAddress();

		if (!$ip) {
			return NULL;
		}

		$cacheIdentifier = md5($endpoint . '--' . $ip);

		//$this->cacheFrontend->remove($cacheIdentifier);

		if ($this->cacheFrontend->has($cacheIdentifier)) {
			$record = $this->cacheFrontend->get($cacheIdentifier);
			if ($this->debug) {
				$this->systemLogger->info('Loading IP location from cache: '. $record->continent->code . ' / ' . ($record->country->isoCode ? $record->country->isoCode : '?'));
			}
			return $this->$endpoint = $record;
		}

		try {
			$client = new Client($this->geoIpUserId, $this->geoIpLicense);
			$record = $client->$endpoint($ip);
			if ($this->debug) {
				$this->systemLogger->info('IP location determined as '. $record->continent->code . ' / ' . ($record->country->isoCode ? $record->country->isoCode : '?')  .'. Storing location data in cache.');
			}
			$this->cacheFrontend->set($cacheIdentifier, $record);
			return $this->$endpoint = $record;
		} catch (\Exception $e) {
			$this->systemLogger->error('Error during geoip request to ' . $endpoint . ' endpoint: ' . $e->getMessage());
			if ($this->debug) {
				throw $e;
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getIPAddress()
	{
		$activeRequestHandler = $this->bootstrap->getActiveRequestHandler();
		if (!($activeRequestHandler instanceof HttpRequestHandlerInterface)) {
			return NULL;
		}

        $ip = $activeRequestHandler->getHttpRequest()->getServerParams()['REMOTE_ADDR'];

		if ($this->debug && $this->debugSimulateIpAddress) {
			$ip = $this->debugSimulateIpAddress;
		}

		return $ip;
	}

}
