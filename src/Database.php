<?php
declare(strict_types=1);

namespace Orbital\MongoDb;

use \Exception;
use \Orbital\Framework\App;
use \Orbital\MongoDb\Client;

class Database extends Client {

    /**
     * Database name
     * @var string
     */
    private $_databaseName;

    /**
     * Database options
     * @var array
     */
    private $_databaseOptions;

    /**
     * Database resource
     * @var \MongoDB\Database
     */
    private $_database;

    /**
     * Set database connection
     * Also clear current connection
     * @param string $databaseName
     * @param array $databaseOptions
     * @return void
     */
    public function setDatabase(string $name, array $options): void {

        $this->_databaseName = $name;
        $this->_databaseOptions = $options;
        $this->_database = null;

    }

    /**
     * Connect with database
     * @throws Exception
     * @return void
     */
    public function connectDatabase(): void {

        $this->connectClient();

        if( $this->_database ){
            return;
        }

        try{

            if( !$this->_databaseName ){

                $data = (array) App::get('mongodb');
                $data = $data['database'];

                $this->setDatabase(
                    $data['name'],
                    $data['options']
                );

            }

            $this->_database = $this->getClient()->selectDatabase(
                $this->_databaseName,
                $this->_databaseOptions
            );

        }catch( Exception $e ){
            die('Error connecting to database: '. $e->getMessage());
        }

    }

    /**
     * Retrieve database
     * @return \MongoDB\Database
     */
    public function getDatabase(): \MongoDB\Database {
        $this->connectDatabase();
        return $this->_database;
    }

}
