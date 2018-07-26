<?php

use \App;

// Development
if( ENVIRONMENT == 'development' ):

    App::set('mongodb', array(
        'client' => array(
            'uri' => 'mongodb://user:pass@localhost:27017',
            'options' => array(),
            'driver' => array()
        ),
        'database' => array(
            'name' => 'orbital',
            'options' => array()
        )
    ));

// Staging
elseif( ENVIRONMENT == 'staging' ):

    App::set('mongodb', array(
        'client' => array(
            'uri' => 'mongodb://user:pass@localhost:27017',
            'options' => array(),
            'driver' => array()
        ),
        'database' => array(
            'name' => 'orbital',
            'options' => array()
        )
    ));

// Production
else:

    App::set('mongodb', array(
        'client' => array(
            'uri' => 'mongodb://user:pass@localhost:27017',
            'options' => array(),
            'driver' => array()
        ),
        'database' => array(
            'name' => 'orbital',
            'options' => array()
        )
    ));

endif;