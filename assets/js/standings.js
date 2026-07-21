const blmNonce = blmStandings.nonce;

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
    blmStandings.ajax,
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