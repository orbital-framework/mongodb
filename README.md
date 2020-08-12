# Orbital MongoDB

Sample config for MongoDB usage:

```php
use \Orbital\Framework\App;
use \Orbital\Env\Env;

App::set('mongodb', array(
    'client' => array(
        'uri' => Env::get('MONGODB_URI'),
        'options' => array(),
        'driver' => array()
    ),
    'database' => array(
        'name' => Env::get('MONGODB_DATABASE'),
        'options' => array()
    )
));
```

Don't forget to append the following variables to your ``env.php`` file:

```php
array(
    # ...
    'MONGODB_URI' => 'mongodb://user:password@localhost:27017'
    'MONGODB_DATABASE' => 'my-database',
    # ...
);
```
