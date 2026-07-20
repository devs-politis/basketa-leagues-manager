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

		add_action(
		    'wp_ajax_blm_load_standings',
		    [$this, 'ajax_standings']
		);
		
		add_action(
		    'wp_ajax_nopriv_blm_load_standings',
		    [$this, 'ajax_standings']
		);

    }

    public function ticker($atts = []) {

        wp_enqueue_style(
            'blm-ticker',
            BLM_URL . 'assets/css/ticker.css',
            [],
            BLM_VERSION
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

        <script>
        document.addEventListener('change', function(e){

            if(e.target.id !== 'blm-league-filter'){
                return;
            }

            const league = e.target.value;

            document.querySelectorAll('.blm-game').forEach(function(card){

                if(league === 'all'){
                    card.style.display = '';
                    return;
                }

                card.style.display =
                    card.dataset.league === league
                        ? ''
                        : 'none';

            });

        });
        </script>

        <?php

        return ob_get_clean();
    }


public function standings($atts = []) {

    wp_enqueue_style(
        'blm-standings',
        BLM_URL . 'assets/css/standings.css',
        [],
        BLM_VERSION
    );

    $saved = get_option(
        'blm_standings_leagues',
        []
    );

    if (empty($saved)) {
        return '';
    }

    $enabled = array_filter(
        $saved,
        function($league){
            return !empty($league['enabled']);
        }
    );

    if (empty($enabled)) {
        return '';
    }

    uasort($enabled, function($a, $b){

        return
            ($a['sort'] ?? 999)
            <=>
            ($b['sort'] ?? 999);

    });

    $enabled = $this->get_available_leagues($enabled);

    if (empty($enabled)) {
        return '';
    }

    $league_ids =
        array_keys($enabled);

    $league_id =
        intval(
            reset($league_ids)
        );

    $season =
        intval(
            $enabled[$league_id]['season']
            ?? date('Y')
        );

	$standings = BLM_API::get_standings(
	    $league_id,
	    $season
	);


    if (empty($standings[0])) {
        return '';
    }

    $all_leagues =
        BLM_API::get_leagues();

    ob_start();
    ?>

    <div class="blm-standings-tabs">

        <?php foreach ($enabled as $id => $league) : ?>

            <?php

                $league_name = $id;
                $league_logo = '';

                foreach ($all_leagues as $l) {

                    if ($l['id'] == $id) {

                        $league_name = $l['name'];
                        $league_logo = $l['logo'] ?? '';

                        break;
                    }
                }

            ?>

            <button
                class="blm-tab <?php echo $id == $league_id ? 'active' : ''; ?>"
                data-league="<?php echo esc_attr($id); ?>"
                data-season="<?php echo esc_attr($league['season']); ?>"
            >

                <?php if (!empty($league_logo)) : ?>

                    <img
                        src="<?php echo esc_url($league_logo); ?>"
                        alt=""
                        class="blm-tab-logo"
                    >

                <?php endif; ?>

                <span>
                    <?php echo esc_html($league_name); ?>
                </span>

            </button>

        <?php endforeach; ?>

    </div>

    <div class="blm-standings-card">

        <div class="blm-standings-header">

            <h2>Regular Season</h2>

        </div>

        <table class="blm-standings-table">

            <thead>

                <tr>

                    <th>#</th>
                    <th class="club">Club</th>
                    <th>GP</th>
                    <th>W</th>
                    <th>L</th>
                    <th>Win%</th>
                    <th>L5</th>

                </tr>

            </thead>

            <tbody id="blm-standings-body">
                <?php echo $this->render_standings_rows($standings); ?>            
            </tbody>

        </table>

    </div>

    <script>

    const standingsCache = {};

    document
    .querySelectorAll('.blm-tab')
    .forEach(function(tab){

        tab.addEventListener(
            'click',
            function(){

            if (this.classList.contains('active')) {
                return;
            }

            const cacheKey =
                this.dataset.league + '_' + this.dataset.season;

            if (standingsCache[cacheKey]) {

                document.getElementById(
                    'blm-standings-body'
                ).innerHTML = standingsCache[cacheKey];

                document
                    .querySelectorAll('.blm-tab')
                    .forEach(function(t){
                        t.classList.remove('active');
                    });

                this.classList.add('active');

                return;
            }

				

                document
                .querySelectorAll('.blm-tab')
                .forEach(function(t){

                    t.classList.remove(
                        'active'
                    );

                });

                this.classList.add(
                    'active'
                );

                const league =
                    this.dataset.league;

                const season =
                    this.dataset.season;

                fetch(
                    '<?php echo admin_url('admin-ajax.php'); ?>',
                    {
                        method:'POST',
                        headers:{
                            'Content-Type':
                            'application/x-www-form-urlencoded'
                        },
                        body:
                            'action=blm_load_standings'
                            + '&league=' + league
                            + '&season=' + season
                    }
                )
                .then(r => r.json())
                .then(data => {

                if (data.success) {

                    standingsCache[cacheKey] = data.data;

                    document
                        .getElementById(
                            'blm-standings-body'
                        )
                        .innerHTML = data.data;
                }

            });

            }
        );

    });

    </script>

    <?php

    return ob_get_clean();
}

public function ajax_standings() {

    $league_id =
        intval($_POST['league'] ?? 0);

    $season =
        intval($_POST['season'] ?? date('Y'));

    $standings = BLM_API::get_standings(
        $league_id,
        $season
    );

    if (empty($standings[0])) {

        wp_send_json_error();

    }

    wp_send_json_success(
        $this->render_standings_rows($standings)
    );
}

private function get_available_leagues($enabled) {

    $available = [];

    foreach ($enabled as $id => $league) {

        $season = intval(
            $league['season'] ?? date('Y')
        );

        $standings = BLM_API::get_standings(
            $id,
            $season
        );

        if (!empty($standings[0])) {
            $available[$id] = $league;
        }
    }

    return $available;
}

private function render_standings_rows($standings) {

    ob_start();

    foreach ($standings[0] as $team) {

        $played =
            $team['games']['played']
            ?? 0;

        $wins =
            $team['games']['win']['total']
            ?? 0;

        $losses =
            $team['games']['lose']['total']
            ?? 0;

        $win_pct =
            $team['games']['win']['percentage']
            ?? 0;

        $form =
            $team['form']
            ?? '-';

        ?>

        <tr
            class="<?php
                echo ($team['position'] == 7)
                    ? 'blm-playin-start'
                    : (
                        $team['position'] == 11
                            ? 'blm-eliminated-start'
                            : ''
                    );
            ?>"
        >

            <td>
                <?php echo esc_html($team['position']); ?>
            </td>

            <td class="club">

                <div class="blm-team">

                    <img
                        src="<?php echo esc_url($team['team']['logo']); ?>"
                        alt=""
                    >

                    <span>
                        <?php echo esc_html($team['team']['name']); ?>
                    </span>

                </div>

            </td>

            <td><?php echo esc_html($played); ?></td>
            <td><?php echo esc_html($wins); ?></td>
            <td><?php echo esc_html($losses); ?></td>
            <td><?php echo esc_html($win_pct); ?></td>
            <td><?php echo esc_html($form); ?></td>

        </tr>

        <?php
    }

    return ob_get_clean();
}
	
}