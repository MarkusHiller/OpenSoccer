<?php

// AccountController
Router::add('/login', 'AccountController@login', false);
Router::add('/getLoginState', 'AccountController@getLoginState', false);

//CommonController
Router::add('/getTopmanager', 'CommonController@getTopmanager', false);

Router::add('/updateAufstellung', 'ApiController@updateAufstellung', true);
Router::add('/takeAufstellung', 'ApiController@takeAufstellung', true);

Router::add('/createPlayers', 'AdminController@createPlayers', true);