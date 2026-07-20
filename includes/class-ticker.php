<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Ticker {

    public function __construct() {

        add_action(
            'admin_post_blm_save_ticker',
            [$this, 'save']
        );
    }

	public function save() {
	
	    if (!current_user_can('manage_options')) {
	        wp_die('Access denied');
	    }
	
	    check_admin_referer(
	        'blm_save_ticker'
	    );
	
	   $saved = get_option(
		    'blm_ticker_leagues',
		    []
		);
		
		if (!is_array($saved)) {
		    $saved = [];
		}

	
	    /*
	     * ADD LEAGUE
	     */
	    if (!empty($_POST['add_league'])) {
	
	        $league_id = intval(
	            $_POST['add_league']
	        );
	
			$saved[$league_id] = [
			
			    'enabled' => 1,
			    'date_filter' => 0,
			    'start' => '',
			    'end' => '',
			    'sort' => count($saved) + 1
			
			];
	
	        update_option(
	            'blm_ticker_leagues',
	            $saved
	        );
	
	        wp_redirect(
	            admin_url(
	                'admin.php?page=blm-ticker'
	            )
	        );
	
	        exit;
	    }
	
	    /*
	     * REMOVE LEAGUE
	     */
	    if (!empty($_POST['remove_league'])) {
	
	        $league_id = intval(
	            $_POST['remove_league']
	        );
	
	        unset(
	            $saved[$league_id]
	        );
	
	        update_option(
	            'blm_ticker_leagues',
	            $saved
	        );
	
	        wp_redirect(
	            admin_url(
	                'admin.php?page=blm-ticker'
	            )
	        );
	
	        exit;
	    }
	
	    /*
	     * SAVE SETTINGS
	     */
	
	    $leagues = $_POST['leagues'] ?? [];
	
	    $data = [];
	
		foreach ($leagues as $league_id => $league) {
		
		    $start = sanitize_text_field(
		        $league['start'] ?? ''
		    );
		
		    $end = sanitize_text_field(
		        $league['end'] ?? ''
		    );
		
		    $today = current_time('Y-m-d');
		
		    // Δεν επιτρέπουμε start date στο παρελθόν
		    if (
		        !empty($start)
		        &&
		        $start < $today
		    ) {
		        $start = $today;
		    }
		
		    // Το end δεν μπορεί να είναι πριν το start
		    if (
		        !empty($end)
		        &&
		        !empty($start)
		        &&
		        $end < $start
		    ) {
		        $end = $start;
		    }
		
			$data[$league_id] = [
			
			    'enabled' => !empty(
			        $league['enabled']
			    ) ? 1 : 0,
			
			    'date_filter' => !empty(
			        $league['date_filter']
			    ) ? 1 : 0,
			
			    'start' => $start,
			
			    'end' => $end,
			
			    'sort' => intval(
			        $league['sort'] ?? 999
			    )
			
			];
		}
	
	    update_option(
	        'blm_ticker_leagues',
	        $data
	    );
	
	    wp_redirect(
	        admin_url(
	            'admin.php?page=blm-ticker&saved=1'
	        )
	    );
	
	    exit;
	}

    public static function get_saved() {

        return get_option(
            'blm_ticker_leagues',
            []
        );
    }

    public static function get_active() {
	
	    $saved = self::get_saved();
	
	    $today = current_time('Y-m-d');
	
	    $changed = false;
	
	    foreach ($saved as $id => $settings) {
	
	        if (
	            !empty($settings['enabled']) &&
	            !empty($settings['date_filter']) &&
	            !empty($settings['end']) &&
	            $today > $settings['end']
	        ) {
	
	            $saved[$id]['enabled'] = 0;
	
	            $changed = true;
	        }
	    }
	
	    if ($changed) {
	
	        update_option(
	            'blm_ticker_leagues',
	            $saved
	        );
	    }
	
	    $active = [];
	
	    foreach ($saved as $id => $settings) {
	
	        if (!empty($settings['enabled'])) {
	
	            $active[$id] = $settings;
	        }
	    }
	
	    return $active;
	}
}