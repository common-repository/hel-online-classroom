<?php
/**
 * Plugin Name:       HEL BBB Online Classroom: AI-powered Online Classrooms
 * Description:       AI-powered Online Classrooms that improve learning and reduce drop-offs
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * php version        7.0
 * Version:           1.0.3
 * Author:            @higheredlab
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hel-bbb-online-classroom
 *
 * @category Plugin
 *
 * @package HELBBBOnlineClassroom
 *
 * @author @higheredlab
 *
 * @license GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @link https://higheredlab.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// set global variable for current user.

global $current_logged_in_wp_user;

add_action( 'admin_menu', 'hel_bbb_init_menu' );

/**
 * Init Admin Menu.
 *
 * @return void
 */
function hel_bbb_init_menu() {
	// phpcs:disable
	add_menu_page( __( 'Online Classroom', 'hel-bbb-online-classroom' ), __( 'Online Classroom', 'hel-bbb-online-classroom' ), 'manage_options', 'hel', 'hel_bbb_admin_page', 'dashicons-welcome-learn-more', '2.1' );

}


add_action( 'admin_init', 'hel_bbb_register_plugin_settings' );

/**
 * Add Site meta to store bbb settings
 *
 * @return void
 */
function hel_bbb_register_plugin_settings() {
	// Register the settings
	register_setting( 'hel-plugin-settings', 'hel_settings' );
}

/**
 * Init Admin Page.
 *
 * @return void
 */
function hel_bbb_admin_page() {
	// phpcs:disable
	require_once plugin_dir_path( __FILE__ ) . 'templates/app.php';
}


add_action( 'admin_enqueue_scripts', 'hel_bbb_admin_enqueue_scripts' );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function hel_bbb_admin_enqueue_scripts() {
	wp_enqueue_style( 'bbb-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );
	wp_enqueue_script( 'bbb-script', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-element' ), '1.0.0', true );
}


// Shortcode function
function hel_bbb_join_class_shortcode() {
    if (isset($_GET['id']) && !isset($_GET['join_name'])) {
        $id = sanitize_text_field($_GET['id']);
        $access_code = isset($_GET['access_code']) ? sanitize_text_field($_GET['access_code']) : '';
        ?>
        <style>
            #joinClassForm {
                max-width: 400px;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ccc;
                border-radius: 10px;
                background-color: #f9f9f9;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            #joinClassForm label {
                display: block;
                margin-bottom: 10px;
                font-weight: bold;
            }
            #joinClassForm input[type="text"] {
                width: calc(100% - 22px);
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-sizing: border-box;
            }
            #joinClassForm button {
                width: 100%;
                padding: 10px;
                background-color: #0073aa;
                border: none;
                border-radius: 5px;
                color: white;
                font-size: 16px;
                cursor: pointer;
            }
            #joinClassForm button:hover {
                background-color: #005f8d;
            }
        </style>
        <form id="joinClassForm" method="GET" action="">
            <label for="join_name">Please enter your name:</label>
            <input type="text" id="join_name" name="join_name" required>
            <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
            <input type="hidden" name="access_code" value="<?php echo esc_attr($access_code); ?>">
            <button type="submit">Join</button>
        </form>
        <script type="text/javascript">
            document.getElementById("joinClassForm").addEventListener("submit", function(event) {
                var joinName = document.getElementById("join_name").value;
                if (!joinName) {
                    event.preventDefault();
                    alert("Name is required to join the class.");
                } else {
                    var form = document.getElementById("joinClassForm");
                    form.action = "/wp-json/hel-bbb-online-classroom/v1/join-class";
                }
            });
        </script>
        <?php
        exit;
    }
}

add_shortcode('hel_bbb_join_class', 'hel_bbb_join_class_shortcode');

// Function to create the page
function hel_bbb_create_join_class_page() {
    // Check if the page already exists
    $page_title = 'Join Session';
    $page_check = get_page_by_title($page_title);

    if (!isset($page_check->ID)) {
        // Create post object
        $page = array(
            'post_title'    => $page_title,
            'post_content'  => '[hel_bbb_join_class]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
        );

        // Insert the post into the database
        wp_insert_post($page);
    }
}

// Register activation hook
register_activation_hook(__FILE__, 'hel_bbb_create_join_class_page');

// Register update hook
add_action('upgrader_process_complete', 'hel_bbb_plugin_update', 10, 2);
function hel_bbb_plugin_update($upgrader_object, $options) {
    $current_plugin_path_name = plugin_basename(__FILE__);
    if ($options['action'] == 'update' && $options['type'] == 'plugin') {
        foreach ($options['plugins'] as $plugin) {
            if ($plugin == $current_plugin_path_name) {
                hel_bbb_create_join_class_page();
            }
        }
    }
}

// Register uninstall hook
register_uninstall_hook(__FILE__, 'hel_bbb_remove_join_class_page');

// Function to delete the page upon plugin uninstallation
function hel_bbb_remove_join_class_page() {
    // Find the page by title
    $page = get_page_by_title('Join Class');

    // If the page exists, delete it
    if ($page) {
        wp_delete_post($page->ID, true);
    }
}



register_activation_hook( __FILE__, "hel_bbb_plugin_activation" );

/**
 * On plugin activation create a  db table
 *
 * @return void
 */
function hel_bbb_plugin_activation() {


	// Insert DB Tables
	// WP Globals
	global $table_prefix, $wpdb;

	// Customer Table
	$hel_bbb_online_classroom = $table_prefix . 'hel_bbb_online_classroom';

	error_log( "====== Trying to add table $hel_bbb_online_classroom ======" );
	// Create Customer Table if not exist
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $hel_bbb_online_classroom ) ) != $hel_bbb_online_classroom ) {

		// Query - Create Table
		$sql = "CREATE TABLE `$hel_bbb_online_classroom` (";
		$sql .= " `id` int(11) NOT NULL auto_increment, ";
		$sql .= " `name` varchar(500) NOT NULL, ";
		$sql .= " `bbb_id` varchar(500) NOT NULL, ";
		$sql .= " `record` boolean DEFAULT 1, ";
		$sql .= " `presentation` varchar(500) NOT NULL, ";
		$sql .= " `access_code` varchar(500) DEFAULT NULL, ";

		// mute users on join
		$sql .= " `mute_user_on_join` boolean DEFAULT 0, ";

		// Require moderator approval before joining
		$sql .= " `require_moderator_approval` boolean DEFAULT 0, ";

		// All users join as moderators
		$sql .= " `all_users_join_as_moderator` boolean DEFAULT 0, ";

		// Branding settings
		//logo
		$sql .= " `logo_url` varchar(500) DEFAULT NULL, ";

		//logout url
		$sql .= " `logout_url` varchar(500) DEFAULT NULL, ";

		//color
		$sql .= " `primary_color` varchar(500) DEFAULT NULL, ";

		//welcome message
		$sql .= " `welcome_message` varchar(500) DEFAULT NULL, ";

		//advanced settings
		// Enable moderator to unmute users
		$sql .= " `enable_moderator_to_unmute_users` boolean DEFAULT 0, ";

		// Skip audio check
		$sql .= " `skip_check_audio` boolean DEFAULT 0, ";

		//Disable listen only mode
		$sql .= " `disable_listen_only_mode` boolean DEFAULT 0, ";

		// Enable user's private chats
		$sql .= " `enable_user_private_chats` boolean DEFAULT 0, ";

		//class Layout
		$sql .= " `class_layout` varchar(500) DEFAULT NULL, ";

		//addtional join params
		$sql .= " `additional_join_params` varchar(500) DEFAULT NULL, ";

		// sessions count
		$sql .= " `sessions_count` int(11) NOT NULL DEFAULT 0, ";

		// last session
		$sql .= " `last_session` TIMESTAMP, ";

		// created at
		$sql .= " `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, ";
		// updated at

		$sql .= " `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ";
		$sql .= " PRIMARY KEY `customer_id` (`id`) ";

		// get wpdb charset
		$charset_collate = $wpdb->get_charset_collate();

		$sql .= ")";

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

		// Create Table
		dbDelta( $sql );

		error_log( "====== Table $hel_bbb_online_classroom created ======" );
	} else {
		error_log( "====== Table $hel_bbb_online_classroom already exists ======" );
	}
}




// Register uninstall hook
register_uninstall_hook(__FILE__, 'hel_bbb_plugin_uninstall_cleanup');

/**
 * On plugin uninstall, drop the db table.
 */
function hel_bbb_plugin_uninstall_cleanup() {
    global $wpdb;

    // Table Name
    $table_name = $wpdb->prefix . 'hel_bbb_online_classroom';

    error_log("====== BigBlueButton online classroom plugin uninstalled. Deleting Table $table_name ======");

    // Drop the table if it exists
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}




/**
 * Initi api endpoint to save bbb settings
 *
 * @return void
 */

function hel_bbb_create_api_endpoint() {
	global $current_logged_in_wp_user;
	$data = wp_get_current_user();
	$current_logged_in_wp_user = clone $data;

	// route for getting settings
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/get-settings/',
		array(
			'methods' => 'GET',
			'callback' => 'hel_bbb_get_settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for saving settings
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/save-settings/',
		array(
			'methods' => 'POST',
			'callback' => 'hel_bbb_save_settings_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting classes

	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/get-classes/',
		array(
			'methods' => 'GET',
			'callback' => 'hel_bbb_get_classes_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for creating a new class
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/create-class/',
		array(
			'methods' => 'POST',
			'callback' => 'hel_bbb_create_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for editing a class
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/edit-class/',
		array(
			'methods' => 'POST',
			'callback' => 'hel_bbb_edit_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for deleting a class
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/delete-class/',
		array(
			'methods' => 'DELETE',
			'callback' => 'hel_bbb_delete_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for starting a class
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/start-class/',
		array(
			'methods' => 'POST',
			'callback' => 'hel_bbb_start_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for joing a class
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/join-class/',
		array(
			'methods' => 'GET',
			'callback' => 'hel_bbb_join_class_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for getting a class recording
	register_rest_route(
		'hel-bbb-online-classroom/v1',
		'/get-recordings/',
		array(
			'methods' => 'GET',
			'callback' => 'hel_bbb_get_recording_request',
			'permission_callback' => '__return_true',
		)
	);

	// route for uploading a logo
	register_rest_route(
        'hel-bbb-online-classroom/v1',
        '/upload-logo/',
        array(
            'methods' => 'POST',
            'callback' => 'hel_bbb_upload_logo_request',
            'permission_callback' => '__return_true',
        )
    );
}
add_action( 'rest_api_init', 'hel_bbb_create_api_endpoint' );


/**
 * Save BBB settings.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_save_settings_request( WP_REST_Request $request ) {
	// Retrieve only the necessary data from the request.
	$settings = $request->get_body(); 

	// Sanitize the data as needed.
	$settings = sanitize_text_field( $settings );

	// Update the option with the sanitized data.
	update_option( 'hel_settings', $settings );

	// Prepare the payload object.
	$payload = array(
		"data" => $settings,
	);

	// Return the REST response.
	return rest_ensure_response( $payload );
}

/**
 * Get BBB settings.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_get_settings_request( WP_REST_Request $request ) {
	// Retrieve the settings directly from the option.
	$settings = get_option( 'hel_settings' );

	$settings = sanitize_text_field( $settings );

	// Prepare the payload object.
	$payload = array(
		"data" => json_decode( $settings ),
	);

	// Return the REST response.
	return rest_ensure_response( $payload );
}


/**
 * Handle get class.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_get_classes_request( WP_REST_Request $request ) {
	global $wpdb;
	// Check if URL query param 'id' is present.
	$id = absint( $request->get_param( 'id' ) );

	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';
	// Set null value.
	$classes = null;

	if ( $id ) {
		// Use prepared statement to prevent SQL injection.
		$classes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $hel_bbb_online_classroom WHERE id = %d", $id ) );
	} else {
		// Order by updated_at desc.
		$classes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $hel_bbb_online_classroom ORDER BY updated_at DESC"));
	}

	// Payload object.
	$payload = array(
		'data' => $classes,
	);

	return rest_ensure_response( $payload );
}


/**
 * Handle Create class request
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_create_class_request( WP_REST_Request $request ) {
	// Retrieve only the necessary data from the request body.
	$request_body = $request->get_body();

	// Sanitize the data as needed.
	$class_data = sanitize_text_field( $request_body );

	// Decode the JSON data.
	$class_data = json_decode( $class_data );

	// Extract the specific properties needed.
	$properties = array(
		'name',
		'bbb_id',
		'record',
		'presentation',
		'access_code',
		'mute_user_on_join',
		'require_moderator_approval',
		'all_users_join_as_moderator',
		'logo_url',
		'logout_url',
		'primary_color',
		'welcome_message',
		'enable_moderator_to_unmute_users',
		'skip_check_audio',
		'disable_listen_only_mode',
		'enable_user_private_chats',
		'class_layout',
		'additional_join_params',
	);

	$class_data_array = array();

	// Loop through the properties and extract them from the class_data object.
	foreach ( $properties as $property ) {
		if ( isset( $class_data->$property ) ) {
			$class_data_array[ $property ] = $class_data->$property;
		}
	}

	// Call the hel_bbb_add_class function with the extracted data.
	$class = hel_bbb_add_class( $class_data_array );

	// Prepare the payload object.
	$payload = array(
		"data" => $class,
	);

	// Return the REST response.
	return rest_ensure_response( $payload );
}


/**
 * Handle delete class request.
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response|WP_Error $response Response object or error.
 */
function hel_bbb_delete_class_request( WP_REST_Request $request ) {
	global $wpdb;

	$id = absint( $request->get_param( 'id' ) );

	// Check if the ID is empty.
	if ( empty( $id ) ) {
		return new WP_Error( 'bad_request', 'ID is required', array( 'status' => 400 ) );
	}

	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';
	$result = $wpdb->delete( $hel_bbb_online_classroom, array( 'id' => $id ) );

	// Check if the delete operation was successful.
	if ( false === $result ) {
		return new WP_Error( 'delete_error', 'Error deleting class', array( 'status' => 500 ) );
	}

	// Return 200 response.
	return rest_ensure_response( null )->set_status( 200 );
}


/**
 * Handle edit class request
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_edit_class_request( WP_REST_Request $request ) {
	global $wpdb;
	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';

	// Get the ID from the request parameters.
	$id = absint( $request->get_param( 'id' ) );

	// Retrieve only the necessary data from the request body.
	$request_body = $request->get_body();

	$class_data = json_decode( sanitize_text_field( $request_body ) );

	// List of valid properties to update.
	$valid_properties = array(
		'name',
		'record',
		'presentation',
		'access_code',
		'mute_user_on_join',
		'require_moderator_approval',
		'all_users_join_as_moderator',
		'logo_url',
		'logout_url',
		'primary_color',
		'welcome_message',
		'enable_moderator_to_unmute_users',
		'skip_check_audio',
		'disable_listen_only_mode',
		'enable_user_private_chats',
		'class_layout',
		'additional_join_params',
	);

	// Prepare the data array for updating.
	$update_data = array();
	foreach ( $valid_properties as $property ) {
		if ( isset( $class_data->$property ) ) {
			$update_data[ $property ] = $class_data->$property;
		}
	}

	// Perform the database update.
	$wpdb->update(
		$hel_bbb_online_classroom,
		$update_data,
		array( 'id' => $id )
	);

	// Return a 200 response.
	return rest_ensure_response( null )->set_status( 200 );
}


/**
 * Handle start class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */

function hel_bbb_start_class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';
	// get id from request
	$id = absint( $request->get_param( 'id' ) );

	// get bbb settings
	$settings = sanitize_text_field( get_option( 'hel_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$bbb_class = $wpdb->get_results($wpdb->prepare("SELECT * FROM $hel_bbb_online_classroom WHERE id = %d", $id));
	$bbb_class = $bbb_class[0];
	$create_meeting_params = array(
		'name' => $bbb_class->name,
		'meetingID' => $bbb_class->bbb_id,
		'record' => $bbb_class->record == 1 ? 'true' : 'false',
		"muteOnStart" => $bbb_class->mute_user_on_join == 1 ? 'true' : 'false',
		"logo" => $bbb_class->logo_url,
		"logoutURL" => $bbb_class->logout_url,
		"meetingLayout" => $bbb_class->class_layout,
		"allowModsToUnmuteUsers" => $bbb_class->enable_moderator_to_unmute_users == 1 ? 'true' : 'false',
		"welcome" => $bbb_class->welcome_message,
	);

	try {
		// parse additional join params as json and add to create_meeting_params

		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$create_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {
		error_log( "====== Error parsing additional join params $e ======" );
	}

	$query = http_build_query( $create_meeting_params );
	$action_url = hel_bbb_get_url( 'create', $query, $bbb_url, $bbb_secret );
	$presentation = $bbb_class->presentation;
	$presentation_body = "";

	// if presentation url is present then  pre upload presentation
	if ( $presentation ) {
		$presentation_body = "<modules><module name='presentation'><document url='$presentation' filename='presentation.pdf'/></module></modules>";
	}

	// make post api call to create meeting
	$req_body = array(
		'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
	);

	if ( $presentation_body ) {
		$req_body['body'] = $presentation_body;
	}

	$response = wp_remote_post(
		$action_url,
		$req_body
	);

	// response is in xml format
	$response = wp_remote_retrieve_body( $response );
	$response = simplexml_load_string( $response );
	if ( $response->returncode == "FAILED" ) {
		return new WP_Error( 'bad_request', $response->message, array( 'status' => 400 ) );
	}

	// update last session
	$wpdb->update(
		$hel_bbb_online_classroom,
		array(
			'last_session' => current_time( 'mysql' ),
			'sessions_count' => $bbb_class->sessions_count + 1,
		),
		array( 'id' => $id )
	);

	// get current user name
	$current_user_name = $current_logged_in_wp_user->display_name;
	if ( ! $current_user_name ) {
		$current_user_name = $current_logged_in_wp_user->user_login;
	}
	// get user avatar
	$current_user_avatar = get_avatar_url( $current_logged_in_wp_user->ID, array( 'size' => 96 ) );


	// Join Params
	$join_meeting_params = array(
		// get user name from session
		'meetingID' => $bbb_class->bbb_id,
		'role' => 'MODERATOR',
	);

	// add username if present
	if ( $current_user_name ) {
		$join_meeting_params['fullName'] = $current_user_name;
	} else {
		$join_meeting_params['fullName'] = 'Moderator';
	}

	// add avatarURL if present
	if ( $current_user_avatar ) {
		$join_meeting_params['avatarURL'] = $current_user_avatar;
	}

	$brand_color = $bbb_class->primary_color;

	$css = "
    :root{
        --color-primary: $brand_color;

        --btn-primary-active-bg: var(--color-primary);
        --btn-primary-hover-bg: var(--color-primary);
        --color-success: var(--color-primary);
        --btn-primary-bg:var(--color-primary);
        --btn-default-color:var(--color-primary);
        }
        #message-input, #message-input-wrapper{
            background: #fff !important;
        }
        .icon-bbb-upload{
            color: none !important;
        }

          button.select {
            background-color: var(--color-primary) !important;

          }
    ";

	// remove new lines fron $css
	$css = str_replace( array( "\r", "\n" ), '', $css );

	$join_meeting_params["userdata-bbb_listen_only_mode"] = $bbb_class->disable_listen_only_mode == 1 ? 'false' : 'true';
	$join_meeting_params["lockSettingsDisablePrivateChat"] = $bbb_class->enable_user_private_chats == 0 ? 'false' : 'true';
	$join_meeting_params['userdata-bbb_skip_check_audio'] = $bbb_class->skip_check_audio == 1 ? 'true' : 'false';
	$join_meeting_params["meetingLayout"] = $bbb_class->class_layout ? $bbb_class->class_layout : 'SMART_LAYOUT';

	if ( $bbb_class->primary_color ) {
		$join_meeting_params['userdata-bbb_custom_style'] = $css;
	}

	try {
		// parse additional join params as json and add to create_meeting_params
		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$join_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {

	}





	$query = http_build_query( $join_meeting_params );
	$action_url = hel_bbb_get_url( 'join', $query, $bbb_url, $bbb_secret );

	$payload = array(
		"data" => $action_url,
	);
	return rest_ensure_response( $payload );
}

/**
 * Handle join class request
 *
 * @param WP_REST_Request $request wordpress rest request object
 *
 * @return WP_REST_Response $payload response object
 */

function hel_bbb_join_class_request( WP_REST_Request $request ) {
	global $wpdb;
	global $current_logged_in_wp_user;

	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';
	// get id from request
	$id = absint( $request->get_param( 'id' ) );
	$join_name = sanitize_text_field( $request->get_param( 'join_name' ) );
	$access_code = sanitize_text_field( $request->get_param( 'access_code' ) );


	// get bbb settings
	$settings = sanitize_text_field( get_option( 'hel_settings' ) );
	$settings = json_decode( $settings );
	$bbb_url = $settings->bbbServerUrl;
	$bbb_secret = $settings->bbbServerSecret;
	$sql = $wpdb->prepare("SELECT * FROM $hel_bbb_online_classroom WHERE id = %d", $id);
    $bbb_class = $wpdb->get_results($sql);
	  // Check for SQL errors
    if ( $wpdb->last_error ) {
        return new WP_Error( 'database_error', 'Database error occurred', array( 'status' => 500 ) );
    }
	$bbb_class = $bbb_class[0];

	if ( $bbb_class->access_code && $bbb_class->access_code != $access_code ) {
		return new WP_Error( 'bad_request', 'Access code is incorrect', array( 'status' => 400 ) );
	}

	// get current user name
	$current_user_name = $join_name ? $join_name : $current_logged_in_wp_user->display_name;
	if ( ! $current_user_name ) {
		$current_user_name = $current_logged_in_wp_user->user_login ? $current_logged_in_wp_user->user_login : 'User-' . rand( 1, 1000 );
	}
	// get user avatar
	$current_user_avatar = get_avatar_url( $current_logged_in_wp_user->ID, array( 'size' => 96 ) );


	$join_meeting_params = array(
		// get user name from session
		'meetingID' => $bbb_class->bbb_id,
		'role' => $bbb_class->all_users_join_as_moderator == '1' ? 'MODERATOR' : 'VIEWER',
	);
	$brand_color = $bbb_class->primary_color;

	$css = "
    :root{
        --color-primary: $brand_color;
        --btn-primary-active-bg: var(--color-primary);
        --btn-primary-hover-bg: var(--color-primary);
        --color-success: var(--color-primary);
        --btn-primary-bg:var(--color-primary);
        --btn-default-color:var(--color-primary);
        }
        #message-input, #message-input-wrapper{
            background: #fff !important;
        }
        .icon-bbb-upload{
            color: none !important;
        }
        button.select {
            background-color: var(--color-primary) !important;

          }
    ";

	// remove new lines fron $css
	$css = str_replace( array( "\r", "\n" ), '', $css );


	$join_meeting_params["userdata-bbb_listen_only_mode"] = $bbb_class->disable_listen_only_mode == 1 ? 'false' : 'true';
	$join_meeting_params["lockSettingsDisablePrivateChat"] = $bbb_class->enable_user_private_chats == 0 ? 'false' : 'true';
	$join_meeting_params['userdata-bbb_skip_check_audio'] = $bbb_class->skip_check_audio == 1 ? 'true' : 'false';
	$join_meeting_params["meetingLayout"] = $bbb_class->class_layout ? $bbb_class->class_layout : 'SMART_LAYOUT';

	if ( $bbb_class->primary_color ) {
		$join_meeting_params['userdata-bbb_custom_style'] = $wpdb->prepare( "%s", $css );
	}

	try {
		// parse additional join params as json and add to create_meeting_params
		if ( $bbb_class->additional_join_params ) {
			$additional_args = json_decode( $bbb_class->additional_join_params );
			foreach ( $additional_args as $key => $value ) {
				$join_meeting_params[ $key ] = $value;
			}
		}

	} catch (Exception $e) {

	}


	// add username if present
	if ( $current_user_name ) {
		$join_meeting_params['fullName'] = $current_user_name;
	} else {
		$join_meeting_params['fullName'] = 'Moderator';
	}

	// add avatarURL if present
	if ( $current_user_avatar ) {
		$join_meeting_params['avatarURL'] = $current_user_avatar;
	}

	$query = http_build_query( $join_meeting_params );
	$action_url = hel_bbb_get_url( 'join', $query, $bbb_url, $bbb_secret );
	wp_redirect( $action_url );
	exit;
}

/**
 * Handle get class recording request
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */
function hel_bbb_get_recording_request( WP_REST_Request $request ) {
    // Get BBB settings
    $settings = get_option( 'hel_settings' );
    
    // Check if settings exist
    if ( ! $settings ) {
        return new WP_Error( 'bad_request', 'BBB settings not found', array( 'status' => 404 ) );
    }

    // Sanitize the data if needed.
    $settings = sanitize_text_field( $settings );

    // Decode the JSON data.
    $settings = json_decode( $settings );

    // Check if JSON decoding was successful
    if ( ! $settings ) {
        return new WP_Error( 'bad_request', 'Error decoding BBB settings', array( 'status' => 404 ) );
    }

    // Extract BBB settings
    $bbb_url = isset( $settings->bbbServerUrl ) ? $settings->bbbServerUrl : '';
    $bbb_secret = isset( $settings->bbbServerSecret ) ? $settings->bbbServerSecret : '';

    // Check if required settings are present
    if ( empty( $bbb_url ) || empty( $bbb_secret ) ) {
        return new WP_Error( 'bad_request', 'Incomplete BBB settings', array( 'status' => 404 ) );
    }

    // Get recordings parameters
    $get_recordings_params = array(
        'meetingID' => sanitize_text_field( $request->get_param( 'meetingID' ) ),
    );

    // Build query
    $query = http_build_query( $get_recordings_params );

    // Build action URL
    $action_url = hel_bbb_get_url( 'getRecordings', $query, $bbb_url, $bbb_secret );

    // Make remote request
    $response = wp_remote_get( $action_url );

    // Check for errors in the remote request
    if ( is_wp_error( $response ) ) {
        return $response;
    }

    // Retrieve the body of the response
    $response_body = wp_remote_retrieve_body( $response );

    // Load XML response
    $response_xml = simplexml_load_string( $response_body );

    // Check if the return code is "FAILED"
    if ( isset( $response_xml->returncode ) && $response_xml->returncode == "FAILED" ) {
        return new WP_Error( 'bad_request', $response_xml->message, array( 'status' => 404 ) );
    }

    // Prepare the payload object
    $payload = array(
        'data' => isset( $response_xml->recordings ) ? $response_xml->recordings : array(),
    );

    // Return the REST response
    return rest_ensure_response( $payload );
}

/**
 * Handle upload logo request
 *
 * @param WP_REST_Request $request WordPress REST request object.
 *
 * @return WP_REST_Response $payload Response object.
 */

function hel_bbb_upload_logo_request(WP_REST_Request $request) {
    if (empty($_FILES['file'])) {
        return new WP_Error('no_file', 'No file uploaded', array('status' => 400));
    }

    $file = $_FILES['file'];
    $file_name = sanitize_file_name($file['name']);
    $uploaded_file = wp_handle_upload($file, array('test_form' => false));

    if (isset($uploaded_file['error'])) {
        return new WP_Error('upload_error', $uploaded_file['error'], array('status' => 500));
    }

    $file_type = wp_check_filetype($uploaded_file['file']);
    $attachment_data = array(
        'post_mime_type' => $file_type['type'],
        'post_title'     => sanitize_text_field($file_name),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );

    $attachment_id = wp_insert_attachment($attachment_data, $uploaded_file['file']);

    if (is_wp_error($attachment_id)) {
        return $attachment_id;
    }

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $uploaded_file['file']);
    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

    $attachment_url = wp_get_attachment_url($attachment_id);
    return rest_ensure_response(array('url' => $attachment_url));
}


/**
 * Helper function got generate bbb url
 *
 * @param string $action     bbb action
 * @param string $query      bbb params
 * @param string $bbb_url    bbb url
 * @param string $bbb_secret bbb secret
 *
 * @return string $url       bbb action url
 */
function hel_bbb_get_url( $action, $query, $bbb_url, $bbb_secret ) {
	$checksum = sha1( $action . $query . $bbb_secret );

	// if bbb_url is not ends with / then add it
	if ( substr( $bbb_url, -1 ) != '/' ) {
		$bbb_url .= '/';
	}
	$url = $bbb_url . $action . '?' . $query . '&checksum=' . $checksum;
	return $url;
}


/**
 * Create an entry in hel_bbb_online_classroom table
 *
 * @param array $data class data
 *
 * @return array $newClass class data
 */
function hel_bbb_add_class( $data ) {
	global $wpdb;
	$hel_bbb_online_classroom = $wpdb->prefix . 'hel_bbb_online_classroom';
	$wpdb->insert(
		$hel_bbb_online_classroom,
		$data
	);
	$id = $wpdb->insert_id;
	// Prepare and execute a parameterized query to retrieve the inserted record
    $newClass = $wpdb->get_results($wpdb->prepare("SELECT * FROM $hel_bbb_online_classroom WHERE id = %d", $id));
	// return created class
	return $newClass[0];
}
