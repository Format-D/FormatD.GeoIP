<?php
namespace FormatD\GeoIP\Eel\Helper;

use FormatD\GeoIP\Service\IPLocalizationService;
use GeoIp2\Model\City;
use GeoIp2\Model\Country;
use GeoIp2\Model\Insights;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * Helper to get GeoIP related data from service or cache
 */
class GeoIPHelper implements ProtectedContextAwareInterface {

	/**
	 * @Flow\Inject
	 * @var IPLocalizationService
	 */
	protected $ipLocalizationService;

	/**
	 * @param string $key
	 * @return Country
	 */
	public function country($key = 'country.isoCode')
	{
		$record = $this->ipLocalizationService->getCountry();
		return $this->getPropertyPath($record, $key);
	}

	/**
	 * @param string $key
	 * @return City
	 */
	public function city($key = 'country.isoCode')
	{
		$record= $this->ipLocalizationService->getCity();
		return $this->getPropertyPath($record, $key);
	}

	/**
	 * @param string $key
	 * @return Insights
	 */
	public function insights($key = 'country.isoCode')
	{
		$record = $this->ipLocalizationService->getInsights();
		return $this->getPropertyPath($record, $key);
	}

	/**
	 * All methods are considered safe
	 *
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName)
	{
		return true;
	}

	/**
	 * fetches nested object properties by dot notation
	 *
	 * @param object $subject
	 * @param string $path
	 * @return mixed
	 */
	protected function getPropertyPath($subject, $path)
	{
		$pathSegments = explode('.', $path);
		$property = $subject;
		while ($seg = array_shift($pathSegments)) {
			$property = $property->$seg;
		}
		return $property;
	}
}
