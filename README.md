# iimagemap

DokuWiki plugin for making an interactive map (with markers) from an image.

The plugin uses [Panzoom.js](https://github.com/anvaka/panzoom) for creating the map.

## Usage

Syntax:
```
{{iimagemap>image_url|Title}}
[[link|icon.svg|Label @ x,y,width]]
...
{{<iimagemap}}
```

Example:

```
{{iimagemap>_media/0:world_map.jpg|Map of the World}}
[[https://en.wikipedia.org/wiki/Secret_Location|_media/0:marker.svg|Secret location @ 150,400,50]]
{{<iimagemap}}
```

## Attribution

This plugin is heavily based on and inspired by [imapmarkers](https://github.com/kgitthoene/dokuwiki-plugin-imapmarkers/).

