<?php

if (!defined('ABSPATH')) {
    exit;
}

$active = [];

$games = BLM_API::get_games();

$games_today = [];

if (!empty($games)) {

    foreach ($games as $game) {

        $league_id =
            $game['league']['id'] ?? 0;

        if (!isset($games_today[$league_id])) {

            $games_today[$league_id] = 0;
        }

        $games_today[$league_id]++;
    }
}

$active_ids = BLM_Ticker::get_active();


foreach ($leagues as $league) {

    $id = $league['id'];

    if (isset($active_ids[$id])) {

        $active[] = $league;
    }
}

usort($active, function($a, $b) use ($saved){

    $sort_a =
        $saved[$a['id']]['sort'] ?? 999;

    $sort_b =
        $saved[$b['id']]['sort'] ?? 999;

    return $sort_a <=> $sort_b;
});
?>

<div class="wrap">

<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">

    <input
        type="hidden"
        name="action"
        value="blm_save_ticker"
    >

    <?php wp_nonce_field('blm_save_ticker'); ?>

    <h1>Ticker Manager</h1>

<div class="blm-grid">

    <!-- ACTIVE LEAGUES -->

    <div class="blm-box">

        <h2>Active Leagues</h2>

        <?php if (!empty($active)) : ?>
			<div id="blmSortableLeagues">

            <?php foreach ($active as $league) :

                $id = $league['id'];

                $settings =
                    $saved[$id] ?? [];

                $count =
                    $games_today[$id] ?? 0;

            ?>

            <div class="blm-card">

                <div class="blm-card-header">

                    <strong>

                        <span class="blm-drag-handle">
						☰
						</span>
						
						<?php echo esc_html(
						    $league['name']
						); ?>

                    </strong>

                    <span style="
                        margin-left:auto;
                        color:#666;
                    ">
                        #<?php echo esc_html($id); ?>
                    </span>

                </div>

                <div style="
                    margin-bottom:15px;
                    font-size:13px;
                ">

                    <?php if ($count > 0) : ?>

                        🟢 <?php echo $count; ?>
                        games today

                    <?php else : ?>

                        ⚪ No games today

                    <?php endif; ?>

                </div>

                <input
                    type="hidden"
                    name="leagues[<?php echo $id; ?>][enabled]"
                    value="1"
                >

				<input
				    type="hidden"
				    class="blm-sort-order"
				    name="leagues[<?php echo $id; ?>][sort]"
				    value="<?php echo esc_attr(
				        $settings['sort'] ?? 999
				    ); ?>"
				>

                <p>

                    <label>

                        <input
                            type="checkbox"
                            name="leagues[<?php echo $id; ?>][date_filter]"
                            value="1"
                            <?php checked(
                                !empty(
                                    $settings['date_filter']
                                )
                            ); ?>
                        >

                        Enable Date Filter

                    </label>

                </p>
				
				<div
				    class="blm-date-range"
				    style="<?php echo empty($settings['date_filter']) ? 'display:none;' : ''; ?>"
				>
	                <p>
	
	                    <label>
	                        Show From
	                    </label>
	
						<input
						    type="date"
						    min="<?php echo current_time('Y-m-d'); ?>"
						    name="leagues[<?php echo $id; ?>][start]"
						    value="<?php echo esc_attr(
						        $settings['start']
						        ?? ''
						    ); ?>"
						>
	
	                </p>
	
	                <p>
	
	                    <label>
	                        Show Until
	                    </label>
	
						<input
						    type="date"
						    min="<?php echo current_time('Y-m-d'); ?>"
						    name="leagues[<?php echo $id; ?>][end]"
						    value="<?php echo esc_attr(
						        $settings['end']
						        ?? ''
						    ); ?>"
						>
	
	                </p>
				</div>

                <button
                    type="submit"
                    name="remove_league"
                    value="<?php echo $id; ?>"
                    class="button button-secondary"
                >
                    Remove
                </button>

            </div>

            <?php endforeach; ?>
			</div>
        <?php else : ?>

            <p>No active leagues.</p>

        <?php endif; ?>

    </div>

    <!-- LEAGUE DIRECTORY -->

    <div class="blm-box">

        <h2>League Directory</h2>

        <input
            type="text"
            id="blmLeagueSearch"
            class="blm-search"
            placeholder="Search league..."
        >

        <div
            id="blmLeagueList"
            class="blm-league-list"
        >

            <?php foreach ($leagues as $league) :

                $id = $league['id'];

                $count =
                    $games_today[$id] ?? 0;

                $is_enabled =
                    !empty(
                        $saved[$id]['enabled']
                    );

            ?>

            <div
                class="blm-item blm-search-item"
                data-name="<?php echo esc_attr(
                    strtolower(
                        $league['name']
                    )
                ); ?>"
            >

                <div>

                    <strong>

                        <?php echo esc_html(
                            $league['name']
                        ); ?>

                    </strong>

                    <br>

                    <small>

                        #<?php echo esc_html($id); ?>

                        <?php if ($count > 0) : ?>

                            • 🟢 <?php echo $count; ?>
                            games

                        <?php else : ?>

                            • ⚪ No games

                        <?php endif; ?>

                    </small>

                </div>

                <?php if ($is_enabled) : ?>

                    <span style="
                        color:#00a32a;
                        font-weight:600;
                    ">
                        Active
                    </span>

                <?php else : ?>

                    <button
                        type="submit"
                        name="add_league"
                        value="<?php echo $id; ?>"
                        class="button button-primary"
                    >
                        Add
                    </button>

                <?php endif; ?>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>	

<p style="margin-top:20px;">

    <button
        type="submit"
        class="button button-primary button-large"
    >
        Save Changes
    </button>

</p>

	

</form>
