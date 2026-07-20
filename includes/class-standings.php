<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Standings {

    public function __construct() {

        add_action(
            'admin_post_blm_save_standings',
            [$this, 'save']
        );
    }

    public function save() {

        if (!current_user_can('manage_options')) {
            wp_die('Access denied');
        }

        check_admin_referer(
            'blm_save_standings'
        );

        $leagues =
            $_POST['leagues']
            ?? [];

        $data = [];

        foreach (
            $leagues
            as $league_id => $league
        ) {

            $data[$league_id] = [

                'enabled' => !empty(
                    $league['enabled']
                ) ? 1 : 0,

                'season' => intval(
                    $league['season']
                    ?? date('Y')
                ),

                'sort' => intval(
                    $league['sort']
                    ?? 999
                )
            ];
        }

        update_option(
            'blm_standings_leagues',
            $data
        );

        wp_redirect(
            admin_url(
                'admin.php?page=blm-standings&saved=1'
            )
        );

        exit;
    }

    public static function get_saved() {

        return get_option(
            'blm_standings_leagues',
            []
        );
    }

    public static function get_active() {

        $saved =
            self::get_saved();

        $active = [];

        foreach (
            $saved
            as $league_id => $league
        ) {

            if (
                !empty(
                    $league['enabled']
                )
            ) {

                $active[$league_id]
                    = $league;
            }
        }

        return $active;
    }
}