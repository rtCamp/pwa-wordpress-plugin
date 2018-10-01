<p align="center">
<a href="https://rtcamp.com" target="_blank"><img width="200"src="https://rtcamp.com/wp-content/uploads/2018/04/rtcamp-logo-1.svg"></a>
</p>

# PWA WordPress Plugin
WordPress Plugin to enable PWA features.

#### Setup

Once the plugin installed, go to `Appearance > Customize > PWA WordPress` to setup options.

#### Web App Manifest
Web app manifest example output:

```json
{
  "background_color": "#ffffff",
  "description": "Just another WordPress site",
  "display": "standalone",
  "lang": "en-US",
  "name": "Site Name",
  "short_name": "WordPress",
  "start_url": "https://example.com",
  "theme_color": "#000000",
  "icons": [
    {
      "src": "https://example.com/wp-content/uploads/2018/09/cropped-icon-512-1-48x48.png",
      "sizes": "48x48"
    },
    {
      "src": "https://example.com/wp-content/uploads/2018/09/cropped-icon-512-1-192x192.png",
      "sizes": "192x192"
    },
    {
      "src": "https://example.com/wp-content/uploads/2018/09/cropped-icon-512-1-256x256.png",
      "sizes": "256x256"
    },
    {
      "src": "https://example.com/wp-content/uploads/2018/09/cropped-icon-512-1.png",
      "sizes": "512x512"
    }
  ]
}
```

From above entries, following fields can be manage from customizer options:

- `name` - Default will be `get_bloginfo( 'name' )`
- `short_name` - Default will be `get_bloginfo( 'name' )`
- `description` - Default will be `get_bloginfo( 'description' )`
- `theme_color` - Default will be `#000000`
- `background_color` - Default will be `#ffffff`

#### Filters:

- `pwa_ready_manifest` To manage above manifest entries.
- `pwa_wp_plugin_get_theme_color` Control theme color.
- `pwa_wp_plugin_get_background_color` Control background color.

A `rel="manifest"` with link eg. `https://example.com/?pwa_wp_plugin_manifest=1` will be added to `wp_head`.

Icon will be fetch from site icon. Go to `Appearance > Customize > Site Identity > Site Icon` to setup icons.

In Progress.
