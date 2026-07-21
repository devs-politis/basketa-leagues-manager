<?php

if (!defined('ABSPATH')) {
    exit;
}

$leagues = BLM_API::get_leagues();

$saved = get_option(
    'blm_standings_leagues',
    []
);

?>

<div class="wrap">

    <h1>Standings Manager</h1>

    <div class="blm-sticky-tools">

        <input
            type="text"
            id="blm-league-search"
            placeholder="Search league..."
            style="width:300px;"
        >

        <label>

            <input
                type="checkbox"
                id="blm-enabled-only"
            >

            Show enabled only

        </label>

    </div>

    <?php if (!empty($_GET['saved'])) : ?>

        <div class="notice notice-success">
            <p>Settings saved.</p>
        </div>

    <?php endif; ?>

    <style>

    .blm-sticky-tools{
        display:flex;
        gap:20px;
        align-items:center;
        margin-bottom:20px;
    }

    #blm-standings-table th,
    #blm-standings-table td{
        vertical-align:middle;
    }

    #blm-standings-table img{
        width:20px;
        height:20px;
    }

    </style>

    <form
        method="post"
        action="<?php echo admin_url('admin-post.php'); ?>"
    >

        <input
            type="hidden"
            name="action"
            value="blm_save_standings"
        >

        <?php wp_nonce_field(
            'blm_save_standings'
        ); ?>

        <table
            class="widefat fixed striped"
            id="blm-standings-table"
        >

            <thead>

                <tr>

                    <th width="80">
                        Enable
                    </th>

                    <th>
                        League
                    </th>

                    <th width="180">
                        Default Season
                    </th>

                    <th width="100">
                        Sort
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($leagues as $league) :

                $league_id =
                    $league['id'];

                $settings =
                    $saved[$league_id]
                    ?? [];


            ?>

                <tr
                    class="blm-league-row"
                    data-enabled="<?php echo !empty($settings['enabled']) ? '1' : '0'; ?>"
                >

                    <td>

                        <input
                            type="checkbox"
                            name="leagues[<?php echo esc_attr($league_id); ?>][enabled]"
                            value="1"
                            <?php checked(
                                !empty(
                                    $settings['enabled']
                                )
                            ); ?>
                        >

                    </td>

                    <td>

                        <div style="display:flex;align-items:center;gap:10px;">

                            <?php if (!empty($league['logo'])) : ?>

                                <img
                                    src="<?php echo esc_url($league['logo']); ?>"
                                    alt=""
                                >

                            <?php endif; ?>

                            <div>

                                <strong>
                                    <?php echo esc_html(
                                        $league['name']
                                    ); ?>
                                </strong>

                                <br>

                                <small style="color:#666;">
                                    League ID:
                                    <?php echo intval(
                                        $league_id
                                    ); ?>
                                </small>

                            </div>

                        </div>

                    </td>

                    <td>

                   <?php
                    echo '<pre>';
                    var_dump(array_column($league['seasons'] ?? [], 'season'));
                    echo '</pre>';
                    ?>

                        <select name="leagues[<?php echo esc_attr($league_id); ?>][season]">

                            <?php
                            $seasons = array_slice(
                                $league['seasons'] ?? [],
                                0,
                                5
                            );

                            foreach ($seasons as $season) :
                            ?>

                                <option
                                    value="<?php echo esc_attr($season['season']); ?>"
                                    <?php selected(
                                        $settings['season'] ?? '',
                                        $season['season']
                                    ); ?>
                                >
                                    <?php echo esc_html($season['season']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </td>

                    <td>

                        <input
                            type="number"
                            name="leagues[<?php echo esc_attr($league_id); ?>][sort]"
                            value="<?php echo esc_attr(
                                $settings['sort']
                                ?? 999
                            ); ?>"
                            style="width:80px;"
                        >

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <p style="margin-top:20px;">

            <button
                class="button button-primary"
            >
                Save Standings
            </button>

        </p>

    </form>

</div>

<script>

function filterStandings() {

    const search =
        document
        .getElementById(
            'blm-league-search'
        )
        .value
        .toLowerCase();

    const enabledOnly =
        document
        .getElementById(
            'blm-enabled-only'
        )
        .checked;

    document
    .querySelectorAll(
        '.blm-league-row'
    )
    .forEach(function(row){

        const text =
            row.innerText
            .toLowerCase();

        const enabled =
            row.dataset.enabled === '1';

        let visible =
            text.includes(search);

        if (
            enabledOnly &&
            !enabled
        ) {
            visible = false;
        }

        row.style.display =
            visible
            ? ''
            : 'none';

    });
}

document.addEventListener(
    'input',
    function(e){

        if (
            e.target.id ===
            'blm-league-search'
        ) {
            filterStandings();
        }
    }
);

document.addEventListener(
    'change',
    function(e){

        if (
            e.target.id ===
            'blm-enabled-only'
        ) {
            filterStandings();
        }
    }
);

</script>