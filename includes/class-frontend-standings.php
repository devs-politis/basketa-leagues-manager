<?php

if (!defined('ABSPATH')) {
    exit;
}

class BLM_Frontend_Standings
{

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

            return array_slice(
                $league['seasons'],
                0,
                5
            );
        }

        return [];
    }

    private function get_available_leagues($enabled)
    {
        $available = [];

        foreach ($enabled as $id => $league) {

            $seasons = $this->get_league_seasons($id);

            foreach ($seasons as $season) {

                $standings = BLM_API::get_standings(
                    $id,
                    $season['season']
                );

                if (!empty($standings[0])) {

                    $league['default_season'] = $season['season'];

                    $available[$id] = $league;

                    break;
                }
            }
        }

        return $available;
    }

    public function ajax()
    {
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

    private function no_standings($message = 'No standings available.')
    {
        return '
            <div class="blm-no-standings">
                <p>' . esc_html($message) . '</p>
            </div>
        ';
    }

    public function render($atts = [])
{
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
        return $this->no_standings(
            'No leagues configured.'
        );
    }

    $enabled = array_filter(
        $saved,
        function ($league) {
            return !empty($league['enabled']);
        }
    );

    if (empty($enabled)) {
        return '';
    }

    uasort($enabled, function ($a, $b) {

        return
            ($a['sort'] ?? 999)
            <=>
            ($b['sort'] ?? 999);

    });

    $enabled = $this->get_available_leagues($enabled);

    if (empty($enabled)) {
        return $this->no_standings(
            'No active leagues.'
        );
    }

    $league_ids = array_keys($enabled);

    $league_id = intval(reset($league_ids));

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
        return $this->no_standings(
            'Standings are not available.'
        );
    }

    $all_leagues = BLM_API::get_leagues();

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

    $single_year_leagues = [
        120
    ];

    if (in_array($league_id, $single_year_leagues, true)) {

        $season_label = $season;

    } else {

        $season_label =
            $season . '-' . ($season + 1);

    }

    $nonce = wp_create_nonce(
        'blm_standings'
    );


    wp_enqueue_script(
    'blm-standings',
    BLM_URL . 'assets/js/standings.js',
    [],
    BLM_VERSION,
    true
    );

    wp_localize_script(
        'blm-standings',
        'blmStandings',
        [
            'ajax'  => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
        ]
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

        <?php

        $single_year_leagues = [
            120
        ];

        ?>

        <select id="blm-season">

            <?php foreach ($available_seasons as $s) : ?>

                <?php

                $label = in_array(
                    $league_id,
                    $single_year_leagues,
                    true
                )
                    ? $s['season']
                    : $s['season'] . '-' . date(
                        'Y',
                        strtotime($s['end'])
                    );

                ?>

                <option
                    value="<?php echo esc_attr($s['season']); ?>"
                    <?php selected($s['season'], $season); ?>
                >
                    <?php echo esc_html($label); ?>
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
                <?php echo esc_html($stage_name . ' • ' . $season_label); ?>
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

<?php

return ob_get_clean();
}












}