<?php

// AccountController
Router::add('/login', 'AccountController@login', false);

Router::add('/updateAufstellung', 'ApiController@updateAufstellung', true);
Router::add('/takeAufstellung', 'ApiController@takeAufstellung', true);

Router::add('/createPlayers', 'AdminController@createPlayers', true);