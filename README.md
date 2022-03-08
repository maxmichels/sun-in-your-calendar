# Sun in your Calendar   ↑SR 06:17 / ↓SS 17:59

This is the code powering the [Sun in your Calendar](https://sun.maxmichels.de/?from=github.com).

It's a simple PHP script generating a .ical formated calendar with a up to 60 days preview for Sunrise/Sunset information, provided by [Sunrise Sunset](https://sunrise-sunset.org/).

[You can try it out here](https://sun.maxmichels.de/?from=github.com)

![Calendar preview](https://sun.maxmichels.de/screenshot-calendar.png)

## URL parameters

#### Usage
You can upload it to your host and enter the following url like so:

```url
https://yourdomain.com/sun-cal.php?city=München&days=30&detailedDesc=1&localTime=1
```

#### Options

Key | Values | task
--- | ------ | ---
`city` | `city name` or <br>`city name,state code` or <br>`city name,state code,country code` | Specify location
`lat` | `latitude of the location` | if `city` is not provided the location has to be specified by `lat` and `lon`
`lon` | `longtitude of the location`| if `city` is not provided the location has to be specified by `lat` and `lon`
`detailedDesc` | `0` or `1` | if set to `1` there will be more Information about sun in the notes of the event
`days` | `1` to `60` | Number of days which should be shown in the calendar. Default is 30, maximum is 60.
`localTime` | `0` or `1` | If set to `1` the time in title and description will be output as local time instead of UTC.

## System Requirements

- A calendar application that supports .ical
- A system the supports Unicode 7+ *(Released: 2014 June 16)*

*These are the emojis used so fare:*

#### Emojis in the Description

Your Browser | Emoji code
------------ | ----------
🌅 | `:sunrise:`
🌇  | `:city_sunset:`
🙌 | `:raised_hands:`

---
### Credits

This script is based on the Idea of [Weather-in-your-calendar](https://github.com/vejnoe/weather-in-your-calendar/) by [vejnoe](https://vejnoe.dk/) / [@vejnoe](https://github.com/vejnoe)