<?php

namespace Orbital\MongoDb\Model;

use \Orbital\Framework\App;
use \Orbital\MongoDb\Model\Client;
use \Exception;

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
     * @var resource
     */
    private $_database;

    /**
     * Set database connection
     * Also clear current connection
     * @param string $databaseName
     * @param array $databaseOptions
     * @param array $driverOptions
     * @return void
     */
    public function setDatabase($name, $options){

        $this->_databaseName = $name;
        $this->_databaseOptions = $options;

        $this->_database = NULL;

    }

    /**
     * Connect with database
     * @throws Exception
     * @return void
     */
    public function connectDatabase(){

        $this->connectClient();

        if( $this->_database ){
            return FALSE;
        }

        try{

            if( !$this->_databaseName ){

                $data = App::get('mongodb');
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
            die(__('Error connecting to database: '). $e->getMessage());
        }

    }

    /**
     * Retrieve database
     * @return \MongoDB\Database
     */
    public function getDatabase(){
        $this->connectDatabase();
        return $this->_database;
    }

}
