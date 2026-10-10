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

Bundled companion cat video (`assets/gpl-cat/gpl-cat.mp4`):

- [Charming gray cat lounging indoors on a couch](https://www.pexels.com/video/charming-gray-cat-lounging-indoors-on-a-couch-34508552/) — [Pexels](https://www.pexels.com/)

## License

GPL-2.0-or-later
