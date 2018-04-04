<?php
/**
 * Plugin Name: Menu Cache
 * Plugin URI:  https://github.com/nasyrov/wordpress-mo-cache
 * Description: WordPress mu-plugin for a faster wp_nav_menu.
 * Author:      Evgenii Nasyrov
 * Author URI:  mailto:inasyrov@ya.ru
 * Version:     0.0.0
 */

// Require Composer auto loader if installed on it's own
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    require_once $composer;
}
