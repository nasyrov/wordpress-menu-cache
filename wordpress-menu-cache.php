<?php
/**
 * Plugin Name: Menu Cache
 * Plugin URI:  https://github.com/nasyrov/wordpress-mo-cache
 * Description: WordPress mu-plugin for a faster wp_nav_menu.
 * Author:      Evgenii Nasyrov
 * Author URI:  mailto:inasyrov@ya.ru
 * Version:     1.0.0
 */

use Nasyrov\WordPress\MenuCache\MenuCache;

// Require Composer auto loader if installed on it's own
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}

MenuCache::boot();
