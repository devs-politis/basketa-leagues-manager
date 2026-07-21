<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Shortcode {
    

    public function __construct() {

        add_shortcode(
            'basketa_ticker',
            [$this, 'ticker']
        );

        add_shortcode(
            'basketa_leagues',
            [$this, 'ticker']
        );

		add_shortcode(
		    'basketa_standings',
		    [$this, 'standings']
		);

		$frontend_standings = new BLM_Frontend_Standings();

        add_action(
            'wp_ajax_blm_load_standings',
            [$frontend_standings, 'ajax']
        );

        add_action(
            'wp_ajax_nopriv_blm_load_standings',
            [$frontend_standings, 'ajax']
        );

    }

    public function ticker($atts = []) {

        wp_enqueue_style(
            'blm-ticker',
            BLM_URL . 'assets/css/ticker.css',
            [],
            BLM_VERSION
        );

        wp_enqueue_script(
            'blm-ticker',
            BLM_URL . 'assets/js/ticker.js',
            [],
            BLM_VERSION,
            true
        );

        $atts = shortcode_atts([
            'filter' => 'false',
            'class'  => ''
        ], $atts);

        $show_filter = filter_var(
            $atts['filter'],
            FILTER_VALIDATE_BOOLEAN
        );

        $settings = get_option(
            'blm_ticker_leagues',
            []
        );

        if (empty($settings)) {
		    return '';
		}

        $games = BLM_API::get_games();

		usort($games, function($a, $b) use ($settings){
		
		    $finished_statuses = [
		        'FT',
		        'AOT',
		        'POST',
		        'CANC',
		        'ABD',
		        'AWD',
		        'WO'
		    ];
		
		    $status_a = strtoupper(
		        $a['status']['short'] ?? ''
		    );
		
		    $status_b = strtoupper(
		        $b['status']['short'] ?? ''
		    );
		
		    $finished_a = in_array(
		        $status_a,
		        $finished_statuses
		    );
		
		    $finished_b = in_array(
		        $status_b,
		        $finished_statuses
		    );
		
		    if ($finished_a && !$finished_b) {
		        return 1;
		    }
		
		    if (!$finished_a && $finished_b) {
		        return -1;
		    }
		
		    $league_a =
		        $a['league']['id'] ?? 0;
		
		    $league_b =
		        $b['league']['id'] ?? 0;
		
		    $sort_a =
		        $settings[$league_a]['sort'] ?? 999;
		
		    $sort_b =
		        $settings[$league_b]['sort'] ?? 999;
		
		    return $sort_a <=> $sort_b;
		});

       	if (empty($games)) {
		    return '';
		}

       $visible_games = [];

foreach ($games as $game) {

    $league_id = $game['league']['id'] ?? 0;

    if (empty($settings[$league_id]['enabled'])) {
        continue;
    }

    $league_settings =
        $settings[$league_id] ?? [];

    if (!empty($league_settings['date_filter'])) {

        $today = current_time('Y-m-d');

        $start =
            $league_settings['start'] ?? '';

        $end =
            $league_settings['end'] ?? '';

        if (!empty($start) && $today < $start) {
            continue;
        }

        if (!empty($end) && $today > $end) {
            continue;
        }
    }

    $visible_games[] = $game;
}

if (empty($visible_games)) {
    return '';
}

$active_leagues = [];

foreach ($visible_games as $game) {

    $league_id =
        $game['league']['id'] ?? 0;

    $active_leagues[$league_id] = [
        'name' =>
            $game['league']['name']
            ?? ''
    ];
}

ob_start();

        ?>

        <?php if ($show_filter) : ?>

            <div class="blm-ticker-filter">

                <select id="blm-league-filter">

                    <option value="all">
                        All Leagues
                    </option>

                    <?php foreach ($active_leagues as $id => $league) : ?>

                        <option value="<?php echo esc_attr($id); ?>">
                            <?php echo esc_html($league['name']); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        <?php endif; ?>

			 <div class="<?php echo esc_attr($atts['class']); ?>">
			
			<?php
			
			foreach ($visible_games as $game) {
			
			    $league_id =
			        $game['league']['id'] ?? 0;
			
			    if (
			        empty(
			            $settings[$league_id]['enabled']
			        )
			    ) {
			        continue;
			    }
			
			    $league_settings =
			        $settings[$league_id] ?? [];
			
			    if (
			        !empty(
			            $league_settings['date_filter']
			        )
			    ) {
			
			        $today = current_time(
			            'Y-m-d'
			        );
			
			        $start =
			            $league_settings['start']
			            ?? '';
			
			        $end =
			            $league_settings['end']
			            ?? '';
			
			        if (
			            !empty($start)
			            &&
			            $today < $start
			        ) {
			            continue;
			        }
			
			        if (
			            !empty($end)
			            &&
			            $today > $end
			        ) {
			            continue;
			        }
			    }
			
			    $match_time = '';
			
			    if (!empty($game['timestamp'])) {
			
			        $match_time = wp_date(
			            'd/m H:i',
			            $game['timestamp']
			        );
			    }
			
			    $league_name =
			        $game['league']['name'] ?? '';
			
			    $league_logo =
			        $game['league']['logo'] ?? '';
			
			    $home_name =
			        $game['teams']['home']['name'] ?? '';
			
			    $away_name =
			        $game['teams']['away']['name'] ?? '';
			
			    $home_logo =
			        $game['teams']['home']['logo'] ?? '';
			
			    $away_logo =
			        $game['teams']['away']['logo'] ?? '';
			
			    $home_score =
			        $game['scores']['home']['total'] ?? '-';
			
			    $away_score =
			        $game['scores']['away']['total'] ?? '-';
			
			    $status =
			        $game['status']['short'] ?? '';
			
			?>

            <div
                class="blm-game"
                data-league="<?php echo esc_attr($league_id); ?>"
            >


			<div class="leauge-box-blm">
                <div class="blm-league">

                    <?php if (!empty($league_logo)) : ?>

                        <img
                            src="<?php echo esc_url($league_logo); ?>"
                            alt=""
                        >

                    <?php endif; ?>

                    <span>
                        <?php echo esc_html($league_name); ?>
                    </span>

                </div>

	            <div class="blm-date">

                    <?php echo esc_html(
                        $match_time
                    ); ?>

                </div>
			</div>

                <div class="blm-team">

                    <div class="blm-team-left">

                        <?php if (!empty($home_logo)) : ?>

                            <img
                                src="<?php echo esc_url($home_logo); ?>"
                                alt=""
                            >

                        <?php endif; ?>

                        <span>
                            <?php echo esc_html($home_name); ?>
                        </span>

                    </div>

                    <span class="blm-score">
                        <?php echo esc_html($home_score); ?>
                    </span>

                </div>

                <div class="blm-team">

                    <div class="blm-team-left">

                        <?php if (!empty($away_logo)) : ?>

                            <img
                                src="<?php echo esc_url($away_logo); ?>"
                                alt=""
                            >

                        <?php endif; ?>

                        <span>
                            <?php echo esc_html($away_name); ?>
                        </span>

                    </div>

                    <span class="blm-score">
                        <?php echo esc_html($away_score); ?>
                    </span>

                </div>

                <div class="blm-status">

                    <?php echo esc_html($status); ?>

                </div>

            </div>

<?php
}
?>

        </div>

    <?php

        return ob_get_clean();
    }

    public function standings($atts = [])
    {
        $frontend = new BLM_Frontend_Standings();

        return $frontend->render($atts);
    }

}

