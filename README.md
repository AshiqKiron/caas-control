# CaaS Control

WordPress plugin scaffold for managing CaaS (Content as a Service) integrations.

## Structure

```
caas-control.php              Bootstrap & plugin headers
includes/                     Core classes (loader, activator, main plugin)
admin/                        Admin UI, assets, partials
public/                       Front-end assets
languages/                    Translation files (.pot, .po, .mo)
uninstall.php                 Cleanup on plugin delete
```

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Development

1. Place this folder in `wp-content/plugins/`.
2. Activate **CaaS Control** in the WordPress admin.
3. Open **CaaS Control** in the admin menu to view the settings placeholder.

## Attributions

Bundled companion cat videos (`assets/gpl-cat/`):

- Default officer sprite (`gpl-cat.mp4`) — [Charming gray cat lounging indoors on a couch](https://www.pexels.com/video/charming-gray-cat-lounging-indoors-on-a-couch-34508552/)
- Cat scratch (`cat-scratch.mp4`) — [A black and white cat is standing on its hind legs](https://www.pexels.com/video/a-black-and-white-cat-is-standing-on-its-hind-legs-19224529/)
- Aggressive (`cat-agreesive.mp4`) — [Tabby stray cat in the public park](https://www.pexels.com/video/tabby-stray-cat-in-the-public-park-19977378/)
- Laptop (`cat-laptop.mp4`) — [Calm cat resting on laptop keyboard](https://www.pexels.com/video/calm-cat-resting-on-laptop-keyboard-35609097/)
- 404 (`cat-confused.mp4`) — [A cat wearing a red shirt and standing on a table](https://www.pexels.com/video/a-cat-wearing-a-red-shirt-and-standing-on-a-table-20467066/)

All videos from [Pexels](https://www.pexels.com/).

## License

GPL-2.0-or-later
