<?php

namespace Nasyrov\WordPress\MenuCache;

use stdClass;

class MenuCache
{
    /**
     * The cached menu keys.
     *
     * @var array
     */
    protected $keys;

    /**
     * The cached menu timestamp.
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Bootstrap the plugin.
     *
     * @return void
     */
    public static function boot()
    {
        $self = new static;

        add_filter('pre_wp_nav_menu', [$self, 'get'], 10, 2);
        add_filter('wp_nav_menu', [$self, 'put'], 10, 2);
        add_action('wp_update_nav_menu', [$self, 'flush']);
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
        if (empty($args->menu)) {
            return $output;
        }

        $this->keys[] = $key = $this->key($args);

        $data = get_transient($key);
        if (is_array($data) && $data['timestamp'] >= $this->timestamp()) {
            $output = $data['html'];
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
        $key = $this->key($args);
        if (!in_array($key, $this->keys)) {
            return $output;
        }

        $data = [
            'html'      => $output,
            'timestamp' => time(),
        ];

        set_transient($key, $data, 3600);

        return $output;
    }

    /**
     * Flush the menu cache.
     *
     * @return void
     */
    public function flush()
    {
        update_option('menu-cache-timestamp', time());
    }

    /**
     * Generate the cache key.
     *
     * @param stdClass $args
     *
     * @return string
     */
    protected function key($args)
    {
        $args = (array)$args;
        unset($args['menu']);

        return sprintf('menu-cache-%s', md5(json_encode($args)));
    }

    protected function timestamp()
    {
        if (is_null($this->timestamp)) {
            $this->timestamp = get_option('menu-cache-timestamp', 0);
        }

        return $this->timestamp;
    }
}
