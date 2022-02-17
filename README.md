
# FormatD.GeoIP

A Neos CMS Integration for the geolocation service geoip2 api client of Maxmind ([maxmind.com](https://www.maxmind.com/)).


## What does it do?

This package provides a service-class and eel-helper to access the geo-information of a user (by analyzing the IP address with the maxmind api).
Optionally you can integrate a prompt to redirect the user to the content dimension matching his current location.


## Compatibility

Versioning scheme:

     1.0.0 
     | | |
     | | Bugfix Releases (non breaking)
     | Neos Compatibility Releases (non breaking except framework dependencies)
     Feature Releases (breaking)

Releases und compatibility:

| Package-Version | Neos CMS Version |
|-----------------|------------------|
| 1.0.x           | 4.x, 5.x         |


## Using the service in you own plugins

Inject the IPLocalizationService into your class and call the method according to the maxmind endpoint (getCountry, getCity, getInsights). 
the corresponding model of the geoip2 library holding all available information is returned.


## Using the service in fusion

To get de country iso code in lowercase call this eel-helper.

```
	${String.toLowerCase(FormatD.GeoIP.country())}
```

The eel-helper accepts an optional argument "key" (e.g. "continent.code") for direct access to the data provided by geoip2


## Using the dimension switch prompt

To display a layer on your site telling the user to switch dimension if he is located somewhere else integrate this fusion prototype and place it where you need it.

1. Place fusion prototype where you want it in your site
```
	include: resource://FormatD.GeoIP/Private/Fusion/LocationBasedDimensionSwitchPrompt.fusion
	page.someWhereInYourSite = FormatD.GeoIP:LocationBasedDimensionSwitchPrompt
```

2. Integrate LocationBasedDimensionSwitchPrompt.js into your site js bundling (it requires jQuery)
	
3. Configure the mapping (which iso code to what dimension value) in the Settings.yaml (see configuration)

4. Make it pretty with css


## Configuration options

Set your maxmind credentials in the Settings.yaml

```
FormatD:
  GeoIP:
    geoIpUserId: 123456
    geoIpLicense: 'somepassword'
```  

For local testing you can enable debugging and simulate your ip address
```   
FormatD:
  GeoIP:
    debug:
      enable: true
      simulateIpAddress: '8.8.8.8' # example us ip (google)
```

To make the LocationBasedDimensionSwitchPrompt work you have to configure which iso code you want to redirect to which dimension value.

```   
FormatD:
  GeoIP:
    locationBasedDimensionSwitch:
      dimensionName: 'country'
      countryIsoCodeToPresetMap:
        gb: 'uk'
        de: 'de'
```

Calls to the API are cached for one day. You can modify the default lifetime in your Caches.yaml

