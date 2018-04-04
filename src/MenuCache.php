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
        add_filter('wp_nav_menu', [$self, 'store'], 20, 2);
        add_action('wp_update_nav_menu', [$self, 'flush']);
        add_action('wp_delete_nav_menu', [$self, 'flush']);
    }

    /**
     * Get the menu cache.
     *
     * @param string $output
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
        if (false !== get_transient($this->groupKey($args))) {
            if (false !== ($data = get_transient($this->key($args)))) {
                $output = $data;
            }
        }

        return $output;
    }

    /**
     * Store the menu cache.
     *
     * @param string $output
     * @param stdClass $args
     *
     * @return string
     */
    public function store($output, $args)
    {
        // We'll cache the menu output and let it expire randomly
        // so we don't regenerate all the menus on the same request
        $key = $this->key($args);

        set_transient($key, $output, mt_rand(50, 60) * MINUTE_IN_SECONDS);

        // We'll also store the menu cache key
        // so we can flush it easily
        $groupKey = $this->groupKey($args);

        $keys = get_transient($groupKey) ?: [];

        if (!isset($keys[$key])) {
            $keys[$key] = true;
        }

        set_transient($groupKey, $keys);

        return $output;
    }

    /**
     * Flush the menu cache.
     *
     * @param int $menuId
     *
     * @return void
     */
    public function flush($menuId)
    {
        $groupKey = sprintf(static::GROUP, $menuId);

        // Flush the group cache
        $keys = get_transient($groupKey) ?: [];
        delete_transient($groupKey);

        // Flush the keys cache
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
    protected function groupKey($args)
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
