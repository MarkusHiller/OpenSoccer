<?php

// AccountController
Router::add('/login', 'AccountController@login', false);
Router::add('/logout', 'AccountController@logout', true);
Router::add('/getLoginState', 'AccountController@getLoginState', false);
Router::add('/changePassword', 'AccountController@changePassword', true);
Router::add('/checkDataForRegistration', 'AccountController@checkDataForRegistration', false);
Router::add('/registerUser', 'AccountController@registerUser', false);
Router::add('/changeTeam', 'AccountController@changeTeam', true);

//CommonController
Router::add('/getTopmanager', 'CommonController@getTopmanager', false);
Router::add('/getInfocounts', 'CommonController@getInfocounts', false);

//GameController
Router::add('/getNextMatches', 'GameController@getNextMatches', true);

//OfficeController
Router::add('/getCentralData', 'OfficeController@getCentralData', true);
Router::add('/getProtocolData', 'OfficeController@getProtocolData', true);
Router::add('/getNotes', 'OfficeController@getNotes', true);
Router::add('/delNote', 'OfficeController@delNote', true);
Router::add('/saveNote', 'OfficeController@saveNote', true);
Router::add('/getSettingsData', 'OfficeController@getSettingsData', true);
Router::add('/searchForTeamOrManager', 'OfficeController@searchForTeamOrManager', true);

//TeamController
Router::add('/getSelectableTeams', 'TeamController@getSelectableTeams', true);
Router::add('/getNominationData', 'TeamController@getNominationData', true);


Router::add('/updateAufstellung', 'ApiController@updateAufstellung', true);
Router::add('/takeAufstellung', 'ApiController@takeAufstellung', true);

Router::add('/createPlayers', 'AdminController@createPlayers', true);