# Sun in your Calendar   ⛅️ 26°

This is the code powering the [Sun in your Calendar](https://sun.maxmichels.de/?from=github.com).

It's a simple PHP script generating a .ical formated calendar with a up to 60 days preview for Sunrise/Sunset information, provided by [Sunrise Sunset](https://sunrise-sunset.org/).

[You can try it out here](https://sun.maxmichels.de/?from=github.com)

![Calendar preview](https://sun.maxmichels.de/screenshot-calendar.png)

## URL parameters

#### Usage
You can upload it to your host and enter the following url like so:

```url
https://yourdomain.com/sun-cal.php?city=München&days=30&detailedDesc=1
```

#### Options

Key | Values
--- | ------
`city` | `city name` or <br>`city name,state code` or <br>`city name,state code,country code`
`lat` | `latitude of the location`
`lon` | `longtitude of the location`
`detailedDesc` | `0` or `1`
`days` | `number of days`

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
