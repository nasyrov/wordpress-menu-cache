<?php

namespace Nasyrov\WordPress\MenuCache;

use stdClass;

class MenuCache
{
    /**
     * The cache group pattern.
     *
     * @var string
     */
    const GROUP = 'menu-cache-%d-keys';

    /**
     * The cache key pattern.
     *
     * @var string
     */
    const KEY = 'menu-cache-%d-%s';

    /**
     * Bootstrap the plugin.
     *
     * @return void
     */
    public static function boot()
    {
        $self = new static;

        add_filter('pre_wp_nav_menu', [$self, 'get'], 20, 2);
        add_filter('wp_nav_menu', [$self, 'put'], 20, 2);
        add_action('wp_update_nav_menu', [$self, 'flush']);
        add_action('wp_delete_nav_menu', [$self, 'flush']);
    }

    /**
     * Get the menu cache.
     *
     * @param string   $output
     * @param stdClass $args
     *
     * @return string
     */
    public function get($output, $args)
    {
        // Get the nav menu based on the requested menu
        $menu = wp_get_nav_menu_object($args->menu);

        // Get the nav menu based on the theme_location
        if (!$menu && $args->theme_location) {
            $locations = get_nav_menu_locations();
            if (isset($locations[$args->theme_location])) {
                $menu = wp_get_nav_menu_object($locations[$args->theme_location]);
            }
        }

        // Get the first menu that has items
        if (!$menu && !$args->theme_location) {
            foreach (wp_get_nav_menus() as $_menu) {
                if (wp_get_nav_menu_items($_menu->term_id, ['update_post_term_cache' => false])) {
                    $menu = $_menu;
                    break;
                }
            }
        }

        // Set the menu
        if (empty($args->menu)) {
            $args->menu = $menu;
        }

        // We don't actually need the cache group of this menu,
        // but we need to make sure the cache is not out of sync
        if (false !== get_transient($this->group($args))) {
            if (false !== ($data = get_transient($this->key($args)))) {
                $output = $data;
            }
        }

        return $output;
    }

    /**
     * Put the menu cache.
     *
     * @param string   $output
     * @param stdClass $args
     *
     * @return string
     */
    public function put($output, $args)
    {
        // Generate the cache key
        $key = $this->key($args);

        // Store the menu output
        // Let the cache expire randomly
        $expiration = mt_rand(50, 60) * MINUTE_IN_SECONDS;
        set_transient($key, $output, $expiration);

        // Store the cached menu key
        $group = $this->group($args);
        $keys  = get_transient($group) ?: [];
        if (!isset($keys[$key])) {
            $keys[$key] = true;
        }
        set_transient($group, $keys);

        return $output;
    }

    /**
     * Flush the menu cache.
     *
     * @param int $menu_id
     *
     * @return void
     */
    public function flush($menu_id)
    {
        $group = sprintf(static::GROUP, $menu_id);

        // Flush the group
        $keys = get_transient($group) ?: [];
        delete_transient($group);

        // Flush the keys
        foreach ($keys as $key) {
            delete_transient($key);
        }
    }

    /**
     * Generate the group key.
     *
     * @param stdClass $args
     *
     * @return string
     */
    protected function group($args)
    {
        return sprintf(static::GROUP, $args->menu->term_id);
    }

    /**
     * Generate the key.
     *
     * @param stdClass $args
     *
     * @return string
     */
    protected function key($args)
    {
        global $wp_query;

        return sprintf(static::KEY, $args->menu->term_id, md5(json_encode($args) . $wp_query->query_vars_hash));
    }
}
