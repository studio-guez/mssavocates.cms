<?php

return [
    'page.update:before' => function ($page, $values, $strings) {
        // Vérifier si c'est un article
        if ($page->intendedTemplate()->name() !== 'article') {
            return;
        }

        // Vérifier si show_in_hero est activé
        if (isset($values['show_in_hero']) && $values['show_in_hero'] === true) {
            // Compter combien d'articles ont déjà show_in_hero activé
            $heroArticlesCount = page('actualites')
                ->children()
                ->listed()
                ->filterBy('show_in_hero', 'true')
                ->not($page) // Exclure l'article actuel
                ->count();

            // Si on a déjà 2 articles, on empêche l'activation
            if ($heroArticlesCount >= 2) {
                throw new Exception('Maximum 2 articles peuvent être affichés dans le hero de la page d\'accueil. Veuillez désactiver un autre article avant d\'activer celui-ci.');
            }
        }

        // Vérifier si show_in_carousel est activé
        if (isset($values['show_in_carousel']) && $values['show_in_carousel'] === true) {
            // Compter combien d'articles ont déjà show_in_carousel activé
            $carouselArticlesCount = page('actualites')
                ->children()
                ->listed()
                ->filterBy('show_in_carousel', 'true')
                ->not($page) // Exclure l'article actuel
                ->count();

            // Si on a déjà 5 articles, on empêche l'activation
            if ($carouselArticlesCount >= 5) {
                throw new Exception('Maximum 5 articles peuvent être affichés dans le carrousel de la page d\'accueil. Veuillez désactiver un autre article avant d\'activer celui-ci.');
            }
        }
    },

    'page.create:before' => function ($page, $input) {
        // Même validation pour la création d'un nouvel article
        if ($page->intendedTemplate()->name() !== 'article') {
            return;
        }

        if (isset($input['show_in_hero']) && $input['show_in_hero'] === true) {
            $heroArticlesCount = page('actualites')
                ->children()
                ->listed()
                ->filterBy('show_in_hero', 'true')
                ->count();

            if ($heroArticlesCount >= 2) {
                throw new Exception('Maximum 2 articles peuvent être affichés dans le hero de la page d\'accueil.');
            }
        }

        if (isset($input['show_in_carousel']) && $input['show_in_carousel'] === true) {
            $carouselArticlesCount = page('actualites')
                ->children()
                ->listed()
                ->filterBy('show_in_carousel', 'true')
                ->count();

            if ($carouselArticlesCount >= 5) {
                throw new Exception('Maximum 5 articles peuvent être affichés dans le carrousel de la page d\'accueil.');
            }
        }
    }
];
