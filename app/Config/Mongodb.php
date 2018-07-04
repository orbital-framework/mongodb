<?php

use \App;

// Development
if( ENVIRONMENT == 'development' ):

    App::set('mongodb', array(
        'connection' => 'mongodb://mongodb:27017',
        'database' => 'orbital'
    ));

// Staging
elseif( ENVIRONMENT == 'staging' ):

    App::set('mongodb', array(
        'connection' => 'mongodb://user:pass@localhost:27017',
        'database' => 'orbital'
    ));

// Production
else:

    App::set('mongodb', array(
        'connection' => 'mongodb://user:pass@localhost:27017',
        'database' => 'orbital'
    ));

endif;