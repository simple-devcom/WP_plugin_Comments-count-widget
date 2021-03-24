<?php
/*
 * Plugin Name: Users Comments widget
 * Plugin URI:
 * Description: Custom Users widgets
 * Version: 1.0.0
 * Author: Oleksii Krekoten
 * Author URI: https://alexsvg.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: userswidget
 * Domain Path: /languages
 *
 * Network: true
 */

 define('_PLUGIN_DIR', trailingslashit( WP_PLUGIN_DIR . '/users-widget'));

 add_action( 'wp_enqueue_scripts', 'userswidge_scripts' );
  function userswidge_scripts () {
      wp_register_style( 'userswidge_style', plugins_url('styles/style.css',__FILE__ ));
      wp_enqueue_style( 'userswidge_style' );
      wp_enqueue_script( 'userswidge_scripts', plugins_url('js/app.js',__FILE__ ));
  }

  function userswidge_style_admin() {
      wp_enqueue_style( 'userswidge_style_admin', plugins_url('styles/admin.css', __FILE__) );
  }
  add_action( 'admin_enqueue_scripts', 'userswidge_style_admin' );

  register_activation_hook( __FILE__, 'userswidget_activate' );

  require _PLUGIN_DIR . '/widget.php';

  function userswidget_activate(){

  }
