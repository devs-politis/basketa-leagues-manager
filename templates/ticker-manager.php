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

    <style>

    .blm-grid{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:20px;
        margin-top:20px;
    }
		

	.blm-league-list{
	    max-height:700px;
	    overflow:auto;
	}

    .blm-box{
        background:#fff;
        border:1px solid #ddd;
        border-radius:10px;
        padding:20px;
    }

    .blm-box h2{
        margin-top:0;
        margin-bottom:20px;
    }

    .blm-search{
        width:100%;
        padding:10px;
        margin-bottom:15px;
    }

    .blm-card{
        border:1px solid #ddd;
        border-radius:10px;
        padding:15px;
        margin-bottom:15px;
        background:#fafafa;
    }

    .blm-card-header{
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:15px;
    }

    .blm-card-header img{
        width:28px;
        height:28px;
    }

    .blm-card-body p{
        margin:0 0 15px;
    }

    .blm-card-body label{
        display:block;
        font-weight:600;
        margin-bottom:5px;
    }

    .blm-card-body input[type=text],
    .blm-card-body input[type=date]{
        width:100%;
    }

    .blm-sort{
        width:100px !important;
    }

	.blm-date-range {
		display: flex;
    	gap: 30px;
	}

	.blm-date-range p {
		display: flex;
	    flex-direction: column;
	    gap: 3px;
	}

    .blm-btn{
        border:none;
        border-radius:6px;
        padding:8px 12px;
        cursor:pointer;
    }

    .blm-remove{
        background:#d63638;
        color:#fff;
    }

    .blm-add{
        background:#2271b1;
        color:#fff;
    }

    .blm-item{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:10px;
        border-bottom:1px solid #eee;
    }

    .blm-item:last-child{
        border-bottom:none;
    }

	.blm-drag-handle{
	    cursor:move;
	    margin-right:8px;
	    font-size:16px;
	}
	
	.ui-sortable-helper{
	    box-shadow:0 5px 20px rgba(0,0,0,.15);
	}
	
	.ui-sortable-placeholder{
	    border:2px dashed #2271b1;
	    visibility:visible !important;
	    background:#f5f5f5;
	    height:120px;
	}

    </style>

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
<script>

document.addEventListener(
    'DOMContentLoaded',
    function(){

        const search =
            document.getElementById(
                'blmLeagueSearch'
            );

        if(!search){
            return;
        }

        search.addEventListener(
            'keyup',
            function(){

                const value =
                    this.value.toLowerCase();

                document
                    .querySelectorAll(
                        '.blm-search-item'
                    )
                    .forEach(function(item){

                        item.style.display =
                            item.dataset.name
                            .includes(value)
                            ? ''
                            : 'none';

                    });

            }
        );

    }
);

</script>

<script>

document.addEventListener(
    'change',
    function(e){

        if(
            e.target.type !== 'date'
        ){
            return;
        }

        const card =
            e.target.closest('.blm-card');

        if(!card){
            return;
        }

        const dates =
            card.querySelectorAll(
                'input[type="date"]'
            );

        if(dates.length < 2){
            return;
        }

        const start = dates[0];
        const end   = dates[1];

        end.min = start.value;

        if(
            end.value &&
            start.value &&
            end.value < start.value
        ){
            end.value = start.value;
        }

    }
);

document.addEventListener('change', function(e){

    if(
        e.target.name &&
        e.target.name.includes('[date_filter]')
    ){

        const card =
            e.target.closest('.blm-card');

        if(!card){
            return;
        }

        const range =
            card.querySelector(
                '.blm-date-range'
            );

        if(!range){
            return;
        }

        range.style.display =
            e.target.checked
                ? 'block'
                : 'none';
    }

});
</script>

<script>

jQuery(function($){

    if(typeof $.fn.sortable === 'undefined'){
        return;
    }

    $('#blmSortableLeagues').sortable({

        handle: '.blm-drag-handle',

        update: function(){

            $('#blmSortableLeagues .blm-card').each(function(index){

                $(this)
                    .find('.blm-sort-order')
                    .val(index + 1);

            });

        }

    });

});

</script>