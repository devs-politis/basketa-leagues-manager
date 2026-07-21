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