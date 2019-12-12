# Orbital MongoDB

Sample config for MongoDB usage:

```php
use \Orbital\Framework\App;
use \Orbital\Framework\Request;

App::set('mongodb', array(
    'client' => array(
        'uri' => Request::env('MONGODB_URI', 'mongodb://localhost:27017'),
        'options' => array(),
        'driver' => array()
    ),
    'database' => array(
        'name' => Request::env('MONGODB_DATABASE', 'orbital'),
        'options' => array()
    )
));
```

Don't forget to append the following variables to your ``env.php`` file:

```php
array(
    # ...
    MONGODB_URI = 'mongodb://user:password@localhost:27017'
    MONGODB_DATABASE = 'my-database',
    # ...
);
```
