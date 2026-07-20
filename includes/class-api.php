<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_API {

    private $base_url = 'https://v1.basketball.api-sports.io';

    public function __construct() {

        add_action(
            'admin_init',
            [$this, 'register_settings']
        );
    }

    public function register_settings() {

        register_setting(
            'blm_settings_group',
            'blm_api_key'
        );

        register_setting(
            'blm_settings_group',
            'blm_ticker_leagues'
        );

        register_setting(
            'blm_settings_group',
            'blm_standings_leagues'
        );
    }

    public static function get_api_key() {

        return get_option(
            'blm_api_key',
            ''
        );
    }

    private function request(
        $endpoint,
        $params = []
    ) {

        $api_key = self::get_api_key();

        if (empty($api_key)) {
            return [];
        }

        $url =
            $this->base_url .
            $endpoint;

        if (!empty($params)) {

            $url .= '?' .
                http_build_query(
                    $params
                );
        }

        /*
         * Daily Counter Reset
         */

        $today =
            current_time('Y-m-d');

        if (
            get_option(
                'blm_api_day'
            ) !== $today
        ) {

            update_option(
                'blm_api_day',
                $today
            );

            update_option(
                'blm_api_calls_today',
                0
            );
        }

        /*
         * API Call Counter
         */

        $count = (int) get_option(
            'blm_api_calls_today',
            0
        );

        update_option(
            'blm_api_calls_today',
            $count + 1
        );

		update_option(
            'blm_last_endpoint',
            $endpoint
        );
		
		$response = wp_remote_get(
		    $url,
		    [
		        'headers' => [
		            'x-apisports-key'
		                => $api_key
		        ],
		        'timeout' => 10
		    ]
		);

        

        if (
            is_wp_error(
                $response
            )
        ) {

            update_option(
                'blm_api_status',
                'error'
            );

            update_option(
                'blm_api_last_error',
                $response->get_error_message()
            );

            return [];
        }

        update_option(
            'blm_last_api_call',
            current_time('mysql')
        );

        $body = json_decode(
            wp_remote_retrieve_body(
                $response
            ),
            true
        );

        if (
            !empty(
                $body['errors']
            )
        ) {

            update_option(
                'blm_api_status',
                'error'
            );

            update_option(
                'blm_api_last_error',
                print_r(
                    $body['errors'],
                    true
                )
            );

            return [];
        }

        update_option(
            'blm_api_status',
            'ok'
        );

        update_option(
            'blm_api_last_error',
            ''
        );

        return
            $body['response']
            ?? [];
    }

    public static function get_leagues() {

        $cache =
            get_transient(
                'blm_leagues'
            );

        if (
            $cache !== false
            &&
            !empty($cache)
        ) {
            return $cache;
        }

        $instance = new self();

        $leagues =
            $instance->request(
                '/leagues'
            );

        if (!empty($leagues)) {

            set_transient(
                'blm_leagues',
                $leagues,
                DAY_IN_SECONDS
            );
        }

        return $leagues;
    }

    public static function get_games(
        $date = null
    ) {

        $date =
            $date ?: date('Y-m-d');

        $cache_key =
            'blm_games_' .
            $date;

        $cache =
		    get_transient(
		        $cache_key
		    );
		
    if ($cache !== false) {
        return $cache;
    }

        $instance = new self();

        $games =
            $instance->request(
                '/games',
                [
                    'date' => $date
                ]
            );

        if (!empty($games)) {

            set_transient(
                $cache_key,
                $games,
                300
            );
        }

        return $games;
    }

    public static function get_standings(
        $league_id,
        $season
    ) {

        $cache_key =
            'blm_standings_' .
            $league_id .
            '_' .
            $season;

        $cache =
            get_transient(
                $cache_key
            );

        if ($cache !== false) {
		
		    return $cache;
		}

        $instance = new self();

        $standings =
            $instance->request(
                '/standings',
                [
                    'league' => $league_id,
                    'season' => $season
                ]
            );

        if (
            !empty($standings)
        ) {

            set_transient(
                $cache_key,
                $standings,
                HOUR_IN_SECONDS
            );
        }

        return $standings;
    }
}