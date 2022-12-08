<?php
declare(strict_types=1);

namespace Orbital\MongoDb;

use \Orbital\Framework\Entity;
use \Orbital\Generator\Uuid;
use \Orbital\MongoDb\Collection;

class Document extends Collection {

    /**
     * Document collection
     * @access protected
     * @var string
     */
    protected $_collection;

    /**
     * Class for unique object
     * @access protected
     * @var string
     */
    protected $_unique = null;

    /**
     * Register object loaded status
     * @access protected
     * @var boolean
     */
    protected $_loaded = false;

    /**
     * Primary key for object
     * @access protected
     * @var string
     */
    protected $_primaryKey = 'uuid';

    /**
     * Key normalization for object
     * @access protected
     * @var string
     */
    protected $_normalizeKey = Entity::NORMALIZE_SNAKE_CASE;

    /**
     * Object data
     * @access protected
     * @var Entity
     */
    protected $_object = null;

    /**
     * CONSTRUCTOR
     * @param array $filter
     * @param array $options
     * @return void
     */
    public function __construct(array $filter = null, array $options = array()) {

        if( !$this->_unique ){
            $this->_unique = get_class($this);
        }

        if( !$this->_collection ){
            $this->_collection = strtolower( get_class($this) );
        }

        $this->setDatabaseCollection($this->_collection);

        if( !is_null($filter) ){
            $this->findOne($filter, $options);
        }

    }

    /**
     * Retrieve object data
     * @return Entity
     */
    public function getObject(): Entity {

        if( is_null($this->_object) ){
            $this->_object = new Entity($this->_normalizeKey);
        }

        return $this->_object;
    }

    /**
     * Magic method __toString
     * Returns primary key value
     * @return string
     */
    public function __toString(): string {
        return (string) $this->getData($this->_primaryKey);
    }

    /**
     * Magic method __get
     * @param string $item
     * @return mixed
     */
    public function __get(string $item): mixed {
        return $this->getData($item);
    }

    /**
     * Magic method __set
     * @param string $item
     * @param mixed $value
     * @return void
     */
    public function __set(string $item, mixed $value): void {
        $this->setData($item, $value);
    }

    /**
     * Magic method __isset
     * @param string $item
     * @return boolean
     */
    public function __isset(string $item): bool {
        return $this->hasData($item);
    }

    /**
     * Magic method __unset
     * @param string $item
     * @return void
     */
    public function __unset(string $item): void {
        $this->unsData($item);
    }

    /**
     * Magic method __call
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call(string $name, mixed $arguments): mixed {
        $callable = array($this->getObject(), $name);
        return \call_user_func_array($callable, $arguments);
    }

    /**
     * Return if object exists
     * @return boolean
     */
    public function exists(): bool {
        return ( (boolean) $this->__toString() ) ? true : false;
    }

    /**
     * Convert array data to unique object collection class
     * @param array $data
     * @return object
     */
    public function toUnique(array $data): object {

        if( $this->_unique
            AND class_exists($this->_unique) ){

            $document = new $this->_unique;
            $document->_loaded = true;
            $document->addData( (array) $data );
            $document->cleanChanges();

            return $document;
        }

        return (object) $data;
    }

    /**
     * Retrieve the first result of a query
     * @param string|array $filter
     * @param array $options
     * @return \MongoDB\Model\BSONDocument
     */
    public function findOne(string|array $filter = array(), array $options = array()): \MongoDB\Model\BSONDocument {

        if( !is_array($filter) ){
            $_filter = array();
            $_filter[ $this->_primaryKey ] = $filter;
            $filter = $_filter;
        }

        $options['limit'] = 1;

        $this->reset();
        $filter = $this->normalizeKeys($filter);
        $document = parent::findOne($filter, $options);

        if( $document ){
            $this->_loaded = true;
            $this->addData( (array) $document );
            $this->cleanChanges();
        }

        return $document;
    }

    /**
     * Execute query and retrieve cursor results
     * @param array $filter
     * @param array $options
     * @return \MongoDB\Driver\Cursor
     */
    public function find(array $filter = array(), array $options = array()): \MongoDB\Driver\Cursor {
        $this->reset();
        $filter = $this->normalizeKeys($filter);
        return parent::find($filter, $options);
    }

    /**
     * Count object from collection
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter, array $options = array()): int {
        $this->reset();
        $filter = $this->normalizeKeys($filter);
        return parent::count($filter, $options);
    }

    /**
     * Retrieve aggregate data from collection
     * @param array $pipeline
     * @param array $options
     * @return \Traversable
     */
    public function aggregate(array $pipeline, array $options = array()): \Traversable {
        $this->reset();
        $pipeline = $this->normalizeKeys($pipeline);
        return parent::aggregate($pipeline, $options);
    }

    /**
     * Retrieve distinct field data from collection
     * @param string $field
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function distinct(string $field, array $filter, array $options = array()): array {
        $this->reset();
        $field = $this->normalizeKey($field);
        $filter = $this->normalizeKeys($filter);
        return parent::distinct($field, $filter, $options);
    }

    /**
     * Insert or update object on collection
     * @return mixed
     */
    public function save(): mixed {

        if( !$this->getChanges() ){
            return true;
        }

        // Insert
        if( !$this->__toString()
            OR !$this->_loaded ){

            $primaryKey = Uuid::generateShort();
            $this->setData($this->_primaryKey, $primaryKey);

            $options = array();
            $result = parent::insertOne(
                $this->toArray(),
                $options
            );

        // Update
        }else{

            $filter = array();
            $filter[ $this->_primaryKey ] = $this->__toString();
            $filter = $this->normalizeKeys($filter);
            
            $options = array();

            $result = parent::replaceOne(
                $filter,
                $this->toArray(),
                $options
            );

        }

        $this->_loaded = true;
        // $this->reload();

        return $result;
    }

    /**
     * Remove object from collection
     * @return \MongoDB\DeleteResult
     */
    public function delete(): \MongoDB\DeleteResult {

        $filter = array();
        $filter[ $this->_primaryKey ] = $this->__toString();
        $filter = $this->normalizeKeys($filter);

        $options = array();

        $this->reset();
        return parent::deleteOne($filter, $options);
    }

    /**
     * Reload object data
     * @return self
     */
    public function reload(): self {
        $this->findOne( $this->__toString() );
        return $this;
    }

    /**
     * Reset object data
     * @return self
     */
    public function reset(): self {

        $this->cleanData();
        $this->_loaded = false;

        return $this;
    }

}