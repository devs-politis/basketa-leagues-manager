<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Shortcode {
    
    private function get_league_seasons($league_id)
    {
        $leagues = BLM_API::get_leagues();

        foreach ($leagues as $league) {

            if ($league['id'] != $league_id) {
                continue;
            }

            if (empty($league['seasons'])) {
                return [];
            }

            echo '<pre>';
                print_r($league['seasons']);
                echo '</pre>';
                exit;

            rsort($seasons);

            return array_slice(
                $seasons,
                0,
                5
            );
        }

        return [];
    }

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
    $enabled[$league_id]['default_season']
    ?? date('Y');

    $available_seasons =
        $this->get_league_seasons(
            $league_id
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

    $league_name = '';
    $league_logo = '';

    foreach ($all_leagues as $league) {

        if ($league['id'] == $league_id) {

            $league_name = $league['name'];
            $league_logo = $league['logo'] ?? '';

            break;
        }

    }

    $stage_name =
        $standings[0][0]['group']['name']
        ?? 'Standings';

    $season_label = $season;

    $nonce = wp_create_nonce(
        'blm_standings'
    );

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
                class="blm-tab league-<?php echo esc_attr(sanitize_title($league_name)); ?> <?php echo $id == $league_id ? 'active' : ''; ?>"
                data-league="<?php echo esc_attr($id); ?>"
                data-season="<?php echo esc_attr($league['default_season']); ?>"
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

        <div class="blm-season-selector">

            <label for="blm-season">
                Season
            </label>

            <select id="blm-season">

                <?php foreach ($available_seasons as $s) : ?>

                    <option
                        value="<?php echo esc_attr($s); ?>"
                        <?php selected($s, $season); ?>
                    >
                        <?php echo esc_html($s); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="blm-standings-header">

        <?php if (!empty($league_logo)) : ?>

            <img
                src="<?php echo esc_url($league_logo); ?>"
                alt=""
                class="blm-header-logo"
            >

        <?php endif; ?>

        <div class="blm-header-text">

            <h2>
                <?php echo esc_html($league_name); ?>
            </h2>

            <p>
                <?php
                echo esc_html(
                    $stage_name . ' • ' . $season_label
                );
                ?>
            </p>

        </div>

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
                <?php
                echo $this->render_standings_rows(
                    $standings,
                    $league_id
                );
                ?>
            </tbody>

        </table>

    </div>

    <script>

    const blmNonce =
    '<?php echo esc_js($nonce); ?>';

    const standingsCache = {};

    const params =
        new URLSearchParams(
            window.location.search
        );

    const requestedLeague =
        params.get('league');


    function loadStandings(league, season, clickedTab = null) {

    const cacheKey = league + '_' + season;

    if (standingsCache[cacheKey]) {

        document.getElementById(
            'blm-standings-body'
        ).innerHTML = standingsCache[cacheKey];

        if (clickedTab) {

            document
                .querySelectorAll('.blm-tab')
                .forEach(function(t){
                    t.classList.remove('active');
                });

            clickedTab.classList.add('active');
        }

        const url = new URL(window.location);

        url.searchParams.set('league', league);
        url.searchParams.set('season', season);

        window.history.replaceState({}, '', url);

        return;
    }

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
                + '&league=' + encodeURIComponent(league)
                + '&season=' + encodeURIComponent(season)
                + '&nonce=' + encodeURIComponent(blmNonce)
        }
    )
    .then(r => r.json())
    .then(data => {

        if (!data.success) {
            return;
        }

        standingsCache[cacheKey] = data.data;

        document.getElementById(
            'blm-standings-body'
        ).innerHTML = data.data;

        if (clickedTab) {

            document
                .querySelectorAll('.blm-tab')
                .forEach(function(t){
                    t.classList.remove('active');
                });

            clickedTab.classList.add('active');
        }

        const url = new URL(window.location);

        url.searchParams.set('league', league);
        url.searchParams.set('season', season);

        window.history.replaceState({}, '', url);

    });

}

document
.querySelectorAll('.blm-tab')
.forEach(function(tab){

    tab.addEventListener(
        'click',
        function(){

            if (this.classList.contains('active')) {
                return;
            }

            const league =
                this.dataset.league;

            const season =
                this.dataset.season;

            loadStandings(
                league,
                season,
                this
            );

        }
    );

});

const seasonSelect =
    document.getElementById(
        'blm-season'
    );

if (seasonSelect) {

    seasonSelect.addEventListener(
        'change',
        function(){

            const activeTab =
                document.querySelector(
                    '.blm-tab.active'
                );

            if (!activeTab) {
                return;
            }

            activeTab.dataset.season =
                this.value;

            loadStandings(
                activeTab.dataset.league,
                this.value,
                activeTab
            );

        }
    );
}
                
    if (requestedLeague) {

        const tab =
            document.querySelector(
                '.blm-tab[data-league="' +
                requestedLeague +
                '"]'
            );

        if (tab && !tab.classList.contains('active')) {
            tab.click();
        }

    }

    </script>

    <?php

    return ob_get_clean();
}

public function ajax_standings() {

    check_ajax_referer(
        'blm_standings',
        'nonce'
    );

    $league_id =
        intval($_POST['league'] ?? 0);

    $season =
    sanitize_text_field(
        $_POST['season'] ?? ''
    );

    $standings = BLM_API::get_standings(
        $league_id,
        $season
    );

    if (empty($standings[0])) {
        wp_send_json_error();
    }

    wp_send_json_success(
        $this->render_standings_rows(
            $standings,
            $league_id
        )
    );
}

private function get_available_leagues($enabled)
{
    $available = [];

    foreach ($enabled as $id => $league) {

        $seasons = $this->get_league_seasons($id);

        foreach ($seasons as $season) {

            $standings = BLM_API::get_standings(
                $id,
                $season
            );

            if (!empty($standings[0])) {

                $league['default_season'] = $season;

                $available[$id] = $league;

                break;
            }
        }
    }

    return $available;
}

private function render_standings_rows(
    $standings,
    $league_id = 0
) {

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

        <?php

        $row_class = '';

        if ($league_id == 120) {

            if ($team['position'] == 7) {
                $row_class = 'blm-playin-start';
            }

            if ($team['position'] == 11) {
                $row_class = 'blm-eliminated-start';
            }
        }

        ?>

        <tr class="<?php echo esc_attr($row_class); ?>">

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