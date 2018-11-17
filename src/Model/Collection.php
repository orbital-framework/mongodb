<?php

namespace Orbital\MongoDb\Model;

use \Orbital\MongoDb\Model\Database;

class Collection extends Database {

    /**
     * Database collection
     * @var resource
     */
    private $_databaseCollection;

    /**
     * Retrieve collection
     * @param string $name
     * @param array $options
     * @return \MongoDB\Collection
     */
    public function getCollection($name, $options = array()){
        return $this->getDatabase()->selectCollection(
            $name, $options
        );
    }

    /**
     * Set active database collection
     * @param string $name
     * @param array $options
     * @return void
     */
    public function setDatabaseCollection($name, $options = array()){
        $this->_databaseCollection = $this->getCollection(
            $name, $options
        );
    }

    /**
     * Retrieve collection
     * @return \MongoDB\Collection
     */
    public function getDatabaseCollection(){
        return $this->_databaseCollection;
    }

    /**
     * Retrieve results from collection
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function find($filter = array(), $options = array()){

        $documents = $this->getDatabaseCollection()->find(
            $filter, $options
        );

        return $documents;
    }

    /**
     * Retrieve the first result from collection
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function findOne($filter = array(), $options = array()){

        $options['limit'] = 1;

        $documents = $this->getDatabaseCollection()->find(
            $filter, $options
        );

        if( $documents ){
            foreach( $documents as $item ){
                return $item;
            }
        }

        return array();
    }

    /**
     * Insert document on collection
     * @param array $document
     * @param array $options
     * @return object
     */
    public function insertOne($document, $options = array()){
        return $this->getDatabaseCollection()->insertOne(
            $document, $options
        );
    }

    /**
     * Replace document data on collection
     * @param array $filter
     * @param array $document
     * @param array $options
     * @return object
     */
    public function replaceOne($filter, $document, $options = array()){
        return $this->getDatabaseCollection()->replaceOne(
            $filter, $document, $options
        );
    }

    /**
     * Count data in collection
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function count($filter, $options = array()){
        return $this->getDatabaseCollection()->count(
            $filter, $options
        );
    }

    /**
     * Aggregate data on collection
     * @param array $pipeline
     * @param array $options
     * @return object
     */
    public function aggregate($pipeline, $options = array()){
        return $this->getDatabaseCollection()->aggregate(
            $pipeline, $options
        );
    }

    /**
     * Retrieve distinct results on collection
     * @param string $field
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function distinct($field, $filter, $options = array()){
        return $this->getDatabaseCollection()->distinct(
            $field, $filter, $options
        );
    }

    /**
     * Remove document on collection
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function deleteOne($filter, $options = array()){
        return $this->getDatabaseCollection()->deleteOne(
            $filter, $options
        );
    }

}