<?php
/**
 * Plugin Name: tra.cm Wordpress Plugin
 * Plugin URI: https://github.com/tracm/wordpress
 * Description: Easily add your tra.cm URL shortening account to your Wordpress web site.
 * Version: 1.0
 * Author: tra.cm, LLC.
 * Author URI: http://tra.cm
 * License: GPL2
 */
 /*  Copyright 2015 tra.cm  (email : sales@tra.cm)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Include tra.cm API library
require_once "includes/TracmAPIExchange.php";

// hook for adding admin menus
add_action('admin_menu', 'tracm_add_pages');

// settings link
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tracm_add_action_links');

// register shortcode
add_shortcode('tracm', 'tracm_handler');

// admin menu for settings
function tracm_add_pages() {
    add_options_page(__('Tracm','menu-test'), __('Tracm','menu-test'), 'manage_options', 'tracmsettings', 'tracm_settings_page');
}

// settings link next to the plugin
function tracm_add_action_links($links) {
    $mylinks = array('<a href="' . admin_url('options-general.php?page=tracmsettings') . '">Settings</a>');
    return array_merge($links, $mylinks);
}

// displays the page content for modifying the settings
function tracm_settings_page() {

    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
    
    // variables for the field and option names 
    $hidden_field_name = 'tracm_submit_hidden';
    
    $opt_name1 = 'tracm_api_key';
    $data_field_name1 = 'tracm_api_key';
    
    $opt_name2 = 'tracm_api_secret';
    $data_field_name2 = 'tracm_api_secret';

    // Read in existing option value from database
    $opt_val1 = get_option($opt_name1);
    $opt_val2 = get_option($opt_name2);

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if($_POST[$hidden_field_name] == 'Y') {
        // Read their posted values
        $opt_val1 = $_POST[$data_field_name1];
        $opt_val2 = $_POST[$data_field_name2];

        // Save the posted value in the database
        update_option($opt_name1, $opt_val1);
        update_option($opt_name2, $opt_val2);

        // Put a settings updated message on the screen
    ?>
    <div class="updated"><p><strong><?php _e('Tracm settings saved.', 'menu-test' ); ?></strong></p></div>
    <?php

        }

        // Now display the settings editing screen

        echo '<div class="wrap">';

        // header

         echo "<h2>" . __('Tracm', 'menu-test') . "</h2>";

        // settings form
        
        ?>

    <form name="form1" method="post" action="">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <p><?php _e("API Key:", 'menu-test' ); ?> 
    <input type="text" name="<?php echo $data_field_name1; ?>" value="<?php echo $opt_val1; ?>" size="40">
    </p><hr />
    
    <p><?php _e("API Secret:", 'menu-test' ); ?> 
    <input type="text" name="<?php echo $data_field_name2; ?>" value="<?php echo $opt_val2; ?>" size="40">
    </p><hr />

    <p class="submit">
    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
    </p>

    </form>
    </div>

    <?php
 
}

// collects the links for this account via the Tracm API
function tracm_handler($atts, $content = null) {

    // attribute defaults
    $a = shortcode_atts(array(
        'username' => 'null',
        'page'  => '1',
    ), $atts);
    
    $settings = array(
        'apiKey'       => get_option('tracm_api_key'),
        'apiSecretKey' => get_option('tracm_api_key')
    );

    $tracm = new TracmAPIExchange($settings);

    $tracm->setGetFields(array('page' => $a['page']));

    $result = $tracm->makeRequest('https://tra.cm/api/view/'.$a['username']);
    
    $data = json_decode($result);
    
    foreach ($data->links as $link) {
        $output .= $link->name . "<br />";
        $output .= "<br /><br />";
    }
    
    return $output;
}
