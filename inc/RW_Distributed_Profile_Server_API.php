<?php

/**
 * Class RW_Distributed_Profile_Server_API
 *
 * Contains code for API
 *
 */

class RW_Distributed_Profile_Server_API {

    /**
     * Add API Endpoint
     *
     * @since   0.1
     * @access  public
     * @static
     * @return void
     */
    static public function add_endpoint() {
        $endpoint = RW_Distributed_Profile_Server_Options::get_endpoint();
        add_rewrite_rule( '^'. $endpoint .'/([^/]*)/?', 'index.php/?__rwdpsapi=1&data=$1', 'top');
        //var_dump('^'. $endpoint .'/([^/]*)/?', 'index.php/?__rwdpsapi=1&data=$1');
        flush_rewrite_rules();
    }

    /**
     * Add query vars to retrieve api cmds
     *
     * @since   0.1
     * @access  public
     * @static
     * @param   $vars   array
     * @return  array
     */
    static public function add_query_vars( $vars ) {
        $vars[] = '__rwdpsapi';
        $vars[] = 'data';
        return $vars;
    }

    /**
     * Parse requests
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  die if API request
     */
    static public function parse_request(){
        global $wp;
        if( isset( $wp->query_vars[ '__rwdpsapi' ] ) ) {
            RW_Distributed_Profile_Server_API::handle_request();
            exit;
        }
    }

    /**
     * Handle API Request
     *
     * @since   0.1
     * @access  public
     * @static
     * @return  void
     * @todo    sanitize input data
     */
    static protected function handle_request(){
        global $wp;
        $request = json_decode( stripslashes( $wp->query_vars[ 'data' ] ) );
        if( ! $request || !$request->cmd ) {
            self::send_response('Please send commands in json.');
        } else {
            apply_filters( 'rw_distributed_profile_server_cmd_parser', $request );
        }
    }


    /**
     *
     * @since   0.1
     * @access  public
     * @static
     * @param $msg
     * @param string $data
     */
    static protected function send_response($msg, $data = ''){
        $response[ 'message' ] = $msg;
        if( $data ) {
            $response['data'] = $data;
        }
        //header('content-type: application/json; charset=utf-8');
        echo json_encode( $response )."\n";
        exit;
    }

    /**
     *
     * @since   0.1
     * @access  public
     * @static
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_list_profile( $request ) {
        if ( 'list_profile' == $request->cmd ) {
            $answer = self::generate_profile_list();
            self::send_response( $answer );
        }
        return $request;
    }

    /**
     *
     * @since   0.1
     * @access  public
     * @static
     * @hook    rw_remote_auth_server_cmd_parser
     * @param $request
     * @return mixed
     */
    static public function cmd_get_profile( $request ) {
        if ( 'get_profile' == $request->cmd ) {
            $answer = self::generate_profile_data( $request->data->user_name  );
            self::send_response( $answer );
        }
        return $request;
    }

    /**
     *
     * @since   0.1
     * @access  public
     * @static
     * @hook    rw_remote_auth_server_cmd_parser
     * @param   $request
     * @return  mixed
     */
    static public function cmd_put_profile( $request ) {
        global $wpdb;

        if ( 'user_create' == $request->cmd ) {
            // Check userdate and create the new user
            $data = array(
                'user_login' => $request->data->user_name,
                'user_pass' => urldecode( $request->data->user_password ),
                'user_nicename' => $request->data->user_name,
            );

            $wpdb->insert( $wpdb->users, $data   );
            self::send_response( true );
        }
        return $request;
    }

    /**
     * Generates a array of profile groups and fields
     *
     * @since   0.1
     * @access  public
     * @static
     * @return array
     */
    static public function generate_profile_list() {
        $profiles = array();

        // WordPress default profile data
        $profiles[ 'wordpress' ][ 'user_nicename' ] = 'user_nicename';
        $profiles[ 'wordpress' ][ 'user_email' ] = 'user_email';
        $profiles[ 'wordpress' ][ 'user_url' ] = 'user_url';
        $profiles[ 'wordpress' ][ 'display_name' ] = 'display_name';
        $profiles[ 'wordpress' ][ 'nickname' ] = 'nickname';
        $profiles[ 'wordpress' ][ 'first_name' ] = 'first_name';
        $profiles[ 'wordpress' ][ 'last_name' ] = 'last_name';
        $profiles[ 'wordpress' ][ 'description' ] = 'description';

        // BuddyPress profile data
        if ( function_exists( 'bp_xprofile_get_groups' ) ) {
            $groups = apply_filters( 'rw_distributed_profile_server_bp_profile_field_list', bp_xprofile_get_groups( array (
                'fetch_fields'  => true,
                'fetch_field_data' => true,
                'fetch_visibility_level' => true,
            ) ) );


            foreach ( $groups as $groupObj ) {
                $profiles[ 'buddypress' ][ $groupObj->name ][ 'name' ] = $groupObj->name;
                $profiles[ 'buddypress' ][ $groupObj->name ][ 'description' ] = $groupObj->description;
                $profiles[ 'buddypress' ][ $groupObj->name ][ 'group_order' ] = $groupObj->group_order;
                $profiles[ 'buddypress' ][ $groupObj->name ][ 'can_delete' ] = $groupObj->can_delete;
                foreach ( $groupObj->fields as $fieldObj ) {
                    $field  = new BP_XProfile_Field( $fieldObj->id);
                    $fieldoptions = $field->get_children();
                    $optionsarray = array();
                    foreach ( $fieldoptions as $option ) {
                        $optionsarray[] = array(
                           'type' => $option->type,
                            'name' => $option->name,
                            'description' => $option->description,
                            'is_required' => $option->is_required,
                            'is_default_option' => $option->is_default_option,
                            'can_delete' => $option->can_delete
                        );
                    }
                    $profiles[ 'buddypress' ][ $groupObj->name ][ 'fields' ][] = array(
                        'name' => $fieldObj->name,
                       'description' => $fieldObj->description,
                       'type' => $fieldObj->type,
                       'is_required' => $fieldObj->is_required,
                       'visibility_level' => $fieldObj->visibility_level,
                        'options' => $optionsarray
                    );

                }
            }
        }
        return ( $profiles );
    }

    /**
     * Generates a array of profile data from a user
     * @since   0.1
     * @access  public
     * @static
     * @param $user_name
     */
    static public function generate_profile_data( $user_name  ) {
        $profiles = array();
        $userObj = get_user_by( 'slug', $user_name );
        if ( is_object( $userObj ) ) {
            // WordPress default profile data
            $profiles[ 'wordpress' ][ 'user_nicename' ] = get_the_author_meta( 'user_nicename', $userObj->ID );
            $profiles[ 'wordpress' ][ 'user_email' ] = get_the_author_meta( 'user_email', $userObj->ID );
            $profiles[ 'wordpress' ][ 'user_url' ] = get_the_author_meta( 'user_url', $userObj->ID );
            $profiles[ 'wordpress' ][ 'display_name' ] = get_the_author_meta( 'display_name', $userObj->ID );
            $profiles[ 'wordpress' ][ 'nickname' ] = get_the_author_meta( 'nickname', $userObj->ID );
            $profiles[ 'wordpress' ][ 'first_name' ] = get_the_author_meta( 'first_name', $userObj->ID );
            $profiles[ 'wordpress' ][ 'last_name' ] = get_the_author_meta( 'last_name', $userObj->ID );
            $profiles[ 'wordpress' ][ 'description' ] = get_the_author_meta( 'description', $userObj->ID );

            // BuddyPress profile data
            if ( function_exists( 'bp_xprofile_get_groups' ) ) {
                $groups = apply_filters( 'rw_distributed_profile_server_bp_profile_field_data_list', bp_xprofile_get_groups( array (
                    'fetch_fields'  => true,
                    'fetch_visibility_level' => true,
                ) ) );

                foreach ( $groups as $groupObj ) { // All groups
                    foreach ( $groupObj->fields as $fieldObj ) { // All fields
                        $profiles[ 'buddypress' ][ $groupObj->name ][ $fieldObj->name ][ 'value' ] = maybe_serialize( xprofile_get_field_data( $fieldObj->id, $userObj->ID, 'array' ) );
                        $profiles[ 'buddypress' ][ $groupObj->name ][ $fieldObj->name ][ 'visibility' ] = xprofile_get_field_visibility_level( $fieldObj->id, $userObj->ID ) ;
                    }
                }
            }
        } else {
            return false;
        }
        return ( $profiles );
    }
}