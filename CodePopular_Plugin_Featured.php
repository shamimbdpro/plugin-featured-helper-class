<?php 

/**
 * CodePopular_Plugin_Featured
 */
if ( ! class_exists( 'CodePopular_Plugin_Featured' ) ) {
  class CodePopular_Plugin_Featured {

    /**
     * Initialize Hooks.
     *
     * @return void
     */
    static function init(){

      if (is_admin()) {
        add_filter('install_plugins_table_api_args_featured', array(__CLASS__, 'featured_plugins_tab'));
      }
        
    }

    
    /**
     * Add our plugins to recommended list.
     *
     * @param [type] $res
     * @param [type] $action
     * @param [type] $args
     * @return void
     */
    static function plugins_api_result($res, $action, $args) {
      remove_filter('plugins_api_result', array(__CLASS__, 'plugins_api_result'), 10, 1);

      // Add plugin list which you want to show as feature in dashboard. 

      $res = self::add_plugin_favs('wp-maximum-upload-file-size', $res);
      $res = self::add_plugin_favs('unlimited-theme-addons', $res);

      return $res;
    }
    
    
    /**
     * Helper function for adding plugins to fav list.
     *
     * @param [type] $args
     * @return void
     */
    static function featured_plugins_tab($args) {
      add_filter('plugins_api_result', array(__CLASS__, 'plugins_api_result'), 10, 3);

      return $args;
    }


    /**
     * Add single plugin to list of favs.
     *
     * @param [type] $plugin_slug
     * @param [type] $res
     * @return void
     */
    static function add_plugin_favs($plugin_slug, $res) {
      if (!empty($res->plugins) && is_array($res->plugins)) {
        foreach ($res->plugins as $plugin) {
          if (is_object($plugin) && !empty($plugin->slug) && $plugin->slug == $plugin_slug) {
            return $res;
          }
        } // foreach
      }

      if ($plugin_info = get_transient('wf-plugin-info-' . $plugin_slug)) {
        array_unshift($res->plugins, $plugin_info);
      } else {
        $plugin_info = plugins_api('plugin_information', array(
          'slug'   => $plugin_slug,
          'is_ssl' => is_ssl(),
          'fields' => array(
              'banners'           => true,
              'reviews'           => true,
              'downloaded'        => true,
              'active_installs'   => true,
              'icons'             => true,
              'short_description' => true,
          )
        ));
        if (!is_wp_error($plugin_info)) {
          $res->plugins[] = $plugin_info;
          set_transient('wf-plugin-info-' . $plugin_slug, $plugin_info, DAY_IN_SECONDS * 7);
        }
      }

      return $res;
    }

  }
}


/**
 * Initialize Class.
 */
add_action('init', array('CodePopular_Plugin_Featured', 'init'));
