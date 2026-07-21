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

