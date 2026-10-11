# Image Alt Text Auditor

A WordPress plugin that audits your media library for images missing alt text and lets you fix them inline, without leaving the admin.

## Features

- **Dashboard widget** — shows a count of images missing alt text at a glance; links directly to the audit page
- **Dedicated admin page** under Media → Alt Text Audit
- **Inline editing** — type alt text and save it without leaving the audit list
- **Keyboard support** — press Enter in any alt text field to save
- **Pagination** — handles large media libraries cleanly (20 per page)
- **Auto-removes rows** on save with a fade animation, so the list updates live
- **All-clear state** — friendly confirmation message when the audit is clean

## Installation

1. Upload the `image-alt-text-auditor` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins → Installed Plugins**
3. Navigate to **Media → Alt Text Audit** to run your first audit

## How It Works

The plugin uses `WP_Query` with a `meta_query` to find all `attachment` posts of type `image` where the `_wp_attachment_image_alt` meta key is either missing or set to an empty string.

Saving alt text uses a nonce-protected AJAX handler that calls `update_post_meta()` with the standard `_wp_attachment_image_alt` key — the same key WordPress itself uses in the media library editor.

## WordPress Concepts Demonstrated

| Concept | Where Used |
|---|---|
| `WP_Query` with `meta_query` | `class-iata-query.php` |
| Admin menu registration (`add_media_page`) | `class-iata-admin.php` |
| Asset enqueueing with `wp_localize_script` | `class-iata-admin.php` |
| Dashboard widget API | `class-iata-dashboard-widget.php` |
| Nonce-protected AJAX (`wp_ajax_*`) | `class-iata-ajax.php` |
| Capability checks (`current_user_can`) | Both admin classes |
| `update_post_meta` / `_wp_attachment_image_alt` | `class-iata-ajax.php` |
| Internationalization (`__()`, `_n()`, `esc_html_e`) | Throughout |
| `paginate_links()` | `class-iata-admin.php` |

## File Structure

```
image-alt-text-auditor/
├── image-alt-text-auditor.php       # Plugin bootstrap, constants, includes
├── README.md
├── assets/
│   ├── css/
│   │   └── admin.css                # Admin page + dashboard widget styles
│   └── js/
│       └── admin.js                 # Inline save via AJAX, Enter-key support
└── includes/
    ├── class-iata-query.php         # WP_Query abstraction for missing-alt lookups
    ├── class-iata-admin.php         # Menu page, asset enqueue, audit table render
    ├── class-iata-dashboard-widget.php  # Dashboard widget count + link
    └── class-iata-ajax.php          # AJAX handler for saving alt text
```

## Requirements

- WordPress 6.0+
- PHP 8.0+

## License

GPL-2.0-or-later — see [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html)
