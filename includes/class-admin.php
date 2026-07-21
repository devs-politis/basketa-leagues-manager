<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Admin {

	public function __construct() {
	
	    add_action('admin_menu', [$this, 'menu']);
	
	    add_action(
	        'admin_enqueue_scripts',
	        [$this, 'assets']
	    );

		add_action(
		    'admin_init',
		    [$this, 'clear_cache']
		);
	
	}

	public function assets($hook)
	{
		if (strpos($hook, 'blm-ticker') === false) {
			return;
		}

		wp_enqueue_style(
			'blm-admin-ticker',
			BLM_URL . 'assets/css/admin-ticker.css',
			[],
			BLM_VERSION
		);

		wp_enqueue_script(
			'jquery-ui-sortable'
		);

		wp_enqueue_script(
			'blm-admin-ticker',
			BLM_URL . 'assets/js/admin-ticker.js',
			['jquery', 'jquery-ui-sortable'],
			BLM_VERSION,
			true
		);
	}

    public function menu() {

        add_menu_page(
            'Basketa',
            'Basketa',
            'manage_options',
            'blm-dashboard',
            [$this, 'dashboard'],
            'dashicons-awards',
            30
        );

        add_submenu_page(
            'blm-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'blm-dashboard',
            [$this, 'dashboard']
        );

        add_submenu_page(
            'blm-dashboard',
            'Ticker',
            'Ticker',
            'manage_options',
            'blm-ticker',
            [$this, 'ticker_page']
        );

        add_submenu_page(
            'blm-dashboard',
            'Standings',
            'Standings',
            'manage_options',
            'blm-standings',
            [$this, 'standings_page']
        );

        add_submenu_page(
            'blm-dashboard',
            'API Settings',
            'API Settings',
            'manage_options',
            'blm-settings',
            [$this, 'settings_page']
        );
    }

    public function dashboard() {
        ?>
        <div class="wrap">
            <h1>Basketa Manager</h1>

			<?php

			$status = get_option(
			    'blm_api_status',
			    'unknown'
			);
			
			$error = get_option(
			    'blm_api_last_error',
			    ''
			);
			
			$last_call = get_option(
			    'blm_last_api_call',
			    'Never'
			);
			
			$calls = get_option(
			    'blm_api_calls_today',
			    0
			);

			$last_endpoint = get_option(
			    'blm_last_endpoint',
			    'none'
			);

			$cache_test = get_option(
			    'blm_cache_test',
			    'No Data'
			);
			
			?>
			
			<div class="notice notice-info">
			
			    <h2>API Status</h2>

<p>
    <strong>Cache:</strong>
    <?php echo esc_html($cache_test); ?>
</p>
			
			    <p>
			        <strong>Status:</strong>
			
			        <?php if ($status === 'ok') : ?>
			
			            <span style="color:green;">
			                Connected
			            </span>
			
			        <?php else : ?>
			
			            <span style="color:red;">
			                Error
			            </span>
			
			        <?php endif; ?>
			    </p>
			
			    <p>
			        <strong>Calls Today:</strong>
			        <?php echo esc_html($calls); ?>
			    </p>
			
			    <p>
			        <strong>Last Call:</strong>
			        <?php echo esc_html($last_call); ?>
			    </p>

				<p>
				    <strong>Last Endpoint:</strong>
				    <?php echo esc_html($last_endpoint); ?>
				</p>
			
			    <?php if (!empty($error)) : ?>
			
			        <p>
			            <strong>Error:</strong><br>
			            <?php echo esc_html($error); ?>
			        </p>
			
			    <?php endif; ?>
			
			</div>

			<form method="post">
			
			    <?php wp_nonce_field(
			        'blm_clear_cache'
			    ); ?>
			
			    <input
			        type="hidden"
			        name="blm_clear_cache"
			        value="1"
			    >
			
			    <button
			        class="button button-secondary"
			    >
			        Clear Cache
			    </button>
			
			</form>

            <div class="notice notice-success">
                <p>Plugin ενεργό.</p>
            </div>
        </div>
        <?php
    }

	public function clear_cache() {

	    if (
	        empty(
	            $_POST['blm_clear_cache']
	        )
	    ) {
	        return;
	    }
	
	    check_admin_referer(
	        'blm_clear_cache'
	    );
	
	    global $wpdb;
	
	    $wpdb->query(
	        "DELETE FROM {$wpdb->options}
	        WHERE option_name
	        LIKE '_transient_blm_%'"
	    );
	
	    $wpdb->query(
	        "DELETE FROM {$wpdb->options}
	        WHERE option_name
	        LIKE '_transient_timeout_blm_%'"
	    );
	}

    public function settings_page() {
        ?>
        <div class="wrap">

            <h1>API Settings</h1>

            <form method="post" action="options.php">

                <?php settings_fields('blm_settings_group'); ?>

                <table class="form-table">
                    <tr>
                        <th>API Sports Key</th>
                        <td>
                            <input
                                type="text"
                                name="blm_api_key"
                                value="<?php echo esc_attr(get_option('blm_api_key')); ?>"
                                class="regular-text"
                            >
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>

            </form>

        </div>
        <?php
    }

    public function ticker_page() {

        $leagues = BLM_API::get_leagues();

        $saved = get_option(
            'blm_ticker_leagues',
            []
        );

        include BLM_PATH . 'templates/ticker-manager.php';
    }

    public function standings_page() {

        include BLM_PATH . 'templates/standings-manager.php';
    }
}