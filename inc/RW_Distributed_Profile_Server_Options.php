<?php

/**
 * Class RW_Distributed_Profile_Server_Options
 *
 * Contains some helper code for plugin options
 *
 */

class RW_Distributed_Profile_Server_Options {


    /**
     * Register all settings
     *
     * Register all the settings, the plugin uses.
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function register_settings() {
        register_setting( 'rw_distributed_profile_server_options', 'rw_distributed_profile_server_options_endpoint_url' );
        register_setting( 'rw_distributed_profile_server_options', 'rw_distributed_profile_server_options_whitelist_active' );
        register_setting( 'rw_distributed_profile_server_options', 'rw_distributed_profile_server_options_whitelist' );
        register_setting( 'rw_distributed_profile_server_options', 'rw_distributed_profile_server_options_enable_remote_update' );
    }

    /**
     * Add a settings link to the  pluginlist
     *
     * @since   0.1
     * @access  public
     * @static
     * @param   string array links under the pluginlist
     * @return  array
     */
    static public function plugin_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=' . RW_Distributed_Profile_Server::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Get the API Endpoint
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  string
     */
    static public function get_endpoint() {
        if ( defined ( 'RW_DISTRIBUTED_PROFILE_SERVER_API_ENDPOINT' ) ) {
            return RW_DISTRIBUTED_PROFILE_SERVER_API_ENDPOINT;
        } else {
            return get_option( 'rw_distributed_profile_server_options_endpoint_url', RW_Distributed_Profile_Server::$api_endpoint_default );
        }
    }

    /**
     * Generate the options menu page
     *
     * Generate the options page under the options menu
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     */
    static public function options_menu() {
        add_options_page( 'Distributed Profile Server',  __('Distributed Profile Server', RW_Distributed_Profile_Server::$textdomain ), 'manage_options',
            RW_Distributed_Profile_Server::$plugin_base_name, array( 'RW_Distributed_Profile_Server_Options', 'create_options' ) );
    }

    /**
     * Generate the options page for the plugin
     *
     * @since   0.1
     * @access  public
     * @static
     *
     * @return  void
     */
    static public function create_options() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        $endpoint_url = get_option( 'rw_distributed_profile_server_options_endpoint_url', RW_Distributed_Profile_Server::$api_endpoint_default );
        $endpoint_disabled = '';
        if ( defined ( 'RW_DISTRIBUTED_PROFILE_SERVER_API_ENDPOINT' ) ) {
            // Endpoint is set in wp_config
            $endpoint_url = RW_DISTRIBUTED_PROFILE_SERVER_API_ENDPOINT;
            $endpoint_disabled = ' disabled ';
        }
        ?>
        <div class="wrap"  id="rwdistributedprofileserveroptions">
            <h2><?php _e( 'Distributed Profile Server Options', RW_Distributed_Profile_Server::$textdomain ); ?></h2>
            <p><?php _e( 'Settings for Distributed Profile Server', RW_Distributed_Profile_Server::$textdomain ); ?></p>
            <form method="POST" action="options.php"><fieldset class="widefat">
                    <?php
                    settings_fields( 'rw_distributed_profile_server_options' );
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rw_distributed_profile_server_options_endpoint_url"><?php _e( 'API Endpoint URL', RW_Distributed_Profile_Server::$textdomain ); ?></label>
                            </th>
                            <td>
                                <input id="rw_distributed_profile_server_options_endpoint_url" class="regular-text" type="text" value="<?php echo $endpoint_url; ?>" aria-describedby="endpoint_url-description" name="rw_distributed_profile_server_options_endpoint_url" <?php echo $endpoint_disabled; ?>>
                                <p id="endpoint_url-description" class="description"><?php _e( 'Endpoint URL for API request.', RW_Distributed_Profile_Server::$textdomain); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="rw_distributed_profile_server_options_whitelist_active"><?php _e( 'Whitelist active', RW_Distributed_Profile_Server::$textdomain ); ?></label>
                            </th>
                            <td>
                                <label for="rw_distributed_profile_server_options_whitelist_active">
                                    <input id="rw_distributed_profile_server_options_whitelist_active" type="checkbox" value="1" <?php if ( get_option( 'rw_distributed_profile_server_options_whitelist_active' ) == 1 ) echo " checked "; ?>   name="rw_distributed_profile_server_options_whitelist_active">
                                    <?php _e( 'Activate the whitelist. Only whitelisted hosts can access the API.', RW_Distributed_Profile_Server::$textdomain); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="rw_distributed_profile_server_options_whitelist"><?php _e( 'Whitelist', RW_Distributed_Profile_Server::$textdomain ); ?></label>
                            </th>
                            <td>
                                <textarea rows="10" aria-describedby="whitelist-description" id="rw_distributed_profile_server_options_whitelist" name="rw_distributed_profile_server_options_whitelist" class="large-text code"><?php echo get_option( 'rw_distributed_profile_server_options_whitelist'); ?></textarea>
                                <p id="whitelist-description" class="description"><?php _e( 'Whitelisted hosts can access the API. One hostname or ip per line.', RW_Distributed_Profile_Server::$textdomain); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="rw_distributed_profile_server_options_enable_remote_update"><?php _e( 'Enable remote update', RW_Distributed_Profile_Server::$textdomain ); ?></label>
                            </th>
                            <td>
                                <label for="rw_distributed_profile_server_options_enable_remote_update">
                                    <input id="rw_distributed_profile_server_options_enable_remote_update" type="checkbox" value="1" <?php if ( get_option( 'rw_distributed_profile_server_options_enable_remote_update')  == 1 ) echo " checked "; ?>   name="rw_distributed_profile_server_options_enable_remote_update">
                                    <?php _e( 'Allow remote profile clients to update profile data.', RW_Distributed_Profile_Server::$textdomain); ?></label>
                            </td>
                        </tr>

                    </table>

                    <br/>
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes' )?>" />
            </form>
        </div>
    <?php
    }
}