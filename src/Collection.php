<?php
declare(strict_types=1);

namespace Orbital\MongoDb;

use \Orbital\MongoDb\Database;

class Collection extends Database {

    /**
     * Database collection
     * @var \MongoDB\Collection
     */
    private $_databaseCollection;

    /**
     * Retrieve collection
     * @param string $name
     * @param array $options
     * @return \MongoDB\Collection
     */
    public function getCollection(string $name, array $options = array()): \MongoDB\Collection {
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
    public function setDatabaseCollection(string $name, array $options = array()): void {
        $this->_databaseCollection = $this->getCollection(
            $name, $options
        );
    }

    /**
     * Retrieve collection
     * @return \MongoDB\Collection
     */
    public function getDatabaseCollection(): \MongoDB\Collection {
        return $this->_databaseCollection;
    }

    /**
     * Retrieve results from collection
     * @param array $filter
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function find(array $filter = array(), array $options = array()): \MongoDB\Driver\Cursor {

        $documents = $this->getDatabaseCollection()->find(
            $filter, $options
        );

        return $documents;
    }

    /**
     * Retrieve the first result from collection
     * @param array $filter
     * @param array $options
     * @return \MongoDB\Model\BSONDocument
     */
    public function findOne(array $filter = array(), array $options = array()): \MongoDB\Model\BSONDocument {

        $options['limit'] = 1;

        $documents = $this->getDatabaseCollection()->find(
            $filter, $options
        );

        if( $documents ){
            foreach( $documents as $item ){
                return $item;
            }
        }

        return new \MongoDB\Model\BSONDocument();
    }

    /**
     * Insert document on collection
     * @param array $document
     * @param array $options
     * @return \MongoDB\InsertOneResult
     */
    public function insertOne(array $document, array $options = array()): \MongoDB\InsertOneResult {
        return $this->getDatabaseCollection()->insertOne(
            $document, $options
        );
    }

    /**
     * Replace document data on collection
     * @param array $filter
     * @param array $document
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function replaceOne(array $filter, array $document, array $options = array()): \MongoDB\UpdateResult {
        return $this->getDatabaseCollection()->replaceOne(
            $filter, $document, $options
        );
    }

    /**
     * Count data in collection
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter, array $options = array()): int {
        return $this->getDatabaseCollection()->count(
            $filter, $options
        );
    }

    /**
     * Aggregate data on collection
     * @param array $pipeline
     * @param array $options
     * @return \Traversable
     */
    public function aggregate(array $pipeline, array $options = array()): \Traversable {
        return $this->getDatabaseCollection()->aggregate(
            $pipeline, $options
        );
    }

    /**
     * Retrieve distinct results on collection
     * @param string $field
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function distinct(string $field, array $filter, array $options = array()): array {
        return $this->getDatabaseCollection()->distinct(
            $field, $filter, $options
        );
    }

    /**
     * Remove document on collection
     * @param array $filter
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function deleteOne(array $filter, array $options = array()): \MongoDB\DeleteResult {
        return $this->getDatabaseCollection()->deleteOne(
            $filter, $options
        );
    }

}