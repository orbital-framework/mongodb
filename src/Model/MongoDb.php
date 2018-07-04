<?php

namespace Orbital\MongoDb\Model;

use \MongoDB\Client;
use \Exception;
use \App;

class MongoDb {

    /**
     * Database connection
     * @var string
     */
    private $_databaseConnection;

    /**
     * Database name
     * @var string
     */
    private $_databaseName;

    /**
     * Database resource
     * @var resource
     */
    private $_database;

    /**
     * Set database connection and clear connection
     * @param string $connection
     * @param string $database
     * @return void
     */
    public function setConnection($connection, $database){

        $this->_databaseConnection = $connection;
        $this->_databaseName = $database;

        $this->_database = NULL;

    }

    /**
     * Connect with database
     * @throws Exception
     * @return object
     */
    public function connect(){

        if( $this->_database ){
            return FALSE;
        }

        try{

            if( !$this->_databaseConnection OR !$this->_databaseName ){
                $data = App::get('mongodb');
                $this->setConnection($data['connection'], $data['database']);
            }

            $client = new Client(
                $this->_databaseConnection
            );

            $this->_database = $client->{$this->_databaseName};

        }catch( Exception $e ){
            die(__('Error connecting to database: '). $e->getMessage());
        }

    }

    /**
     * Retrieve database object
     * @param string $collection
     * @return \MongoDB\Database
     */
    public function getDb($collection = NULL){

        $this->connect();

        if( $collection ){
            return $this->_database->{$collection};
        }

        return $this->_database;
    }

    /**
     * Retrieve results from collection
     * @param string $collection
     * @param array $query
     * @param array $sort
     * @param int $limit
     * @param int $skip
     * @return object
     */
    public function findInCollection($collection, $query = array(), $sort = array(), $limit = NULL, $skip = NULL){
        $this->connect();

        $options = array();

        if( $sort ){ $options['sort'] = $sort; }
        if( $limit ){ $options['limit'] = $limit; }
        if( $skip ){ $options['skip'] = $skip; }

        $documents = $this->_database->{$collection}->find(
            $query, $options
        );

        return $documents;
    }

    /**
     * Retrieve the first result from collection
     * @param string $collection
     * @param array $query
     * @param array $sort
     * @param int $skip
     * @return object
     */
    public function findOneInCollection($collection, $query = array(), $sort = array(), $skip = NULL){
        $this->connect();

        $limit = 1;
        $documents = $this->findInCollection($collection, $query, $sort, $limit, $skip);

        if( $documents ){
            foreach( $documents as $item ){
                return $item;
            }
        }

        return array();
    }

    /**
     * Insert document data on collection
     * @param string $collection
     * @param array $document
     * @return object
     */
    public function insertInCollection($collection, $document){
        $this->connect();
        return $this->_database->{$collection}->insertOne($document);
    }

    /**
     * Update document data on collection
     * @param string $collection
     * @param array $query
     * @return object
     */
    public function replaceInCollection($collection, $query, $document){
        $this->connect();
        return $this->_database->{$collection}->replaceOne($query, $document);
    }

    /**
     * Count data in collection
     * @param string $collection
     * @param array $query
     * @return object
     */
    public function countInCollection($collection, $query){
        $this->connect();
        return $this->_database->{$collection}->count($query);
    }

    /**
     * Aggregate data on collection
     * @param string $collection
     * @param array $query
     * @return object
     */
    public function aggregateInCollection($collection, $query){
        $this->connect();
        return $this->_database->{$collection}->aggregate($query);
    }

    /**
     * Retrieve distinct results on collection
     * @param string $collection
     * @param string $field
     * @param array $query
     * @return object
     */
    public function distinctInCollection($collection, $field, $query){
        $this->connect();
        return $this->_database->{$collection}->distinct($field, $query);
    }

    /**
     * Remove results on collection
     * @param string $collection
     * @param array $query
     * @return object
     */
    public function deleteInCollection($collection, $query){
        $this->connect();
        return $this->_database->{$collection}->deleteOne($query);
    }

}