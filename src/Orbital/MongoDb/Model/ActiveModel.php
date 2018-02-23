<?php

namespace Orbital\MongoDb\Model;

use Orbital\MongoDb\Helper\Utils;

class ActiveModel extends MongoDb {

    /**
     * Database collection
     * @access protected
     * @var string
     */
    protected $_collection;

    /**
     * Class for unique object
     * @access protected
     * @var string
     */
    protected $_unique = FALSE;

    /**
     * Primary key for object
     * @access protected
     * @var string
     */
    protected $_primaryKey = 'uuid';

    /**
     * Object data
     * @access protected
     * @var array
     */
    protected $_object = array();

    /**
     * Object original data
     * @access protected
     * @var array
     */
    protected $_objectOriginal = array();

    /**
     * Object data changes
     * @access protected
     * @var array
     */
    protected $_objectChanges = array();

    /**
     * Register object loaded status
     * @access protected
     * @var boolean
     */
    protected $_loaded = FALSE;

    /**
     * Register object saved status
     * @access protected
     * @var boolean
     */
    protected $_saved = FALSE;

    /**
     * CONSTRUCTOR
     * @param mixed $filter
     */
    public function __construct($filter = NULL, $sort = array(), $skip = NULL){

        if( !$this->_unique ){
            $this->_unique = get_class($this);
        }

        if( !$this->_collection ){
            $this->_collection = strtolower( get_class($this) );
        }

        if( !is_null( $filter ) ){
            $this->findOne($filter, $sort, $skip);
        }

    }

    /**
     * Magic method __get
     * @param string $item
     * @return mixed
     */
    public function __get($item){

        if( array_key_exists($item, $this->_object) ){
            return $this->_object[$item];
        }

    }

    /**
     * Magic method __set
     * @param string $item
     * @param mixed $value
     * @return void
     */
    public function __set($item, $value){

        if( isset($this->_object[$item]) ){

            if( $this->_object[$item] != $value ){

                $this->_objectOriginal[$item] = $this->_object[$item];
                $this->_objectChanges[$item] = $value;

            }

        }else{
            $this->_objectChanges[$item] = $value;
        }

        $this->_object[$item] = $value;
        $this->_saved = FALSE;

    }

    /**
     * Magic method __isset
     * @param string $item
     * @return boolean
     */
    public function __isset($item){
        return isset($this->_object[$item]);
    }

    /**
     * Magic method __unset
     * @param string $item
     * @return void
     */
    public function __unset($item){

        if( isset($this->_object[$item]) ){

            $this->_objectOriginal[$item] = $this->_object[$item];
            $this->_objectChanges[$item] = NULL;

            unset($this->_object[$item]);

        }

    }

    /**
     * Magic method __toString
     * Returns primary key value
     * @return string
     */
    public function __toString(){
        return ($this->{$this->_primaryKey}) ? (string) $this->{$this->_primaryKey} : '0';
    }

    /**
     * Return if object exists
     * @return boolean
     */
    public function exists(){
        return ( (boolean) $this->__toString() ) ? TRUE : FALSE;
    }

    /**
     * Retrieve database collection
     * @return object
     */
    public function getCollection(){
        return parent::getDb($this->_collection);
    }

    /**
     * Retrieve object data
     * @return array
     */
    public function getObjectData(){
        return $this->_object;
    }

    /**
     * Retrieve object original data
     * @param boolean $clean
     * @return array
     */
    public function getObjectOriginal($clean = FALSE){

        $object = $this->_objectOriginal;

        if( $clean ){
            unset($object[ $this->_primaryKey ]);
        }

        return $object;
    }

    /**
     * Retrieve object data changes
     * @param boolean $clean
     * @return array
     */
    public function getObjectChanges($clean = FALSE){

        $object = $this->_objectChanges;

        if( $clean ){
            unset($object[ $this->_primaryKey ]);
        }

        return $object;
    }

    /**
     * Convert array data to unique object collection class
     * @param array $data
     * @return object
     */
    public function toUnique($data){

        if( $this->_unique AND class_exists($this->_unique) ){

            $document = new $this->_unique;
            $document->_object = (array) $data;
            $document->_loaded = TRUE;

            return $document;
        }

        return (object) $data;
    }

    /**
     * Retrieve the first result of a query
     * @param array $filter
     * @param array $sort
     * @param int $skip
     * @return mixed
     */
    public function findOne($filter = array(), $sort = array(), $skip = NULL){

        if( !is_array($filter) ){
            $_filter = array();
            $_filter[ $this->_primaryKey ] = $filter;
            $filter = $_filter;
        }

        $this->reset();
        $document = parent::findOneInCollection($this->_collection, $filter, $sort, $skip);

        if( $document ){
            $this->_object = (array) $document;
            $this->_loaded = TRUE;
        }

        return $this;
    }

    /**
     * Execute query and retrieve cursor results
     * @param array $filter
     * @param array $sort
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function find($filter = array(), $sort = array(), $limit = NULL, $skip = NULL){

        $this->reset();
        $results = parent::findInCollection($this->_collection, $filter, $sort, $limit, $skip);

        if( $results ){
            $this->_loaded = TRUE;
            $this->_object = $results;
        }else{
            $this->_object = array();
        }

        return $this->_object;
    }

    /**
     * Count object from collection
     * @param array $filter
     * @return int
     */
    public function count($filter = array()){
        $this->reset();
        return parent::countInCollection($this->_collection, $filter);
    }

    /**
     * Retrieve aggregate data from collection
     * @param array $query
     * @return int
     */
    public function aggregate($query = array()){
        $this->reset();
        return parent::aggregateInCollection($this->_collection, $query);
    }

    /**
     * Retrieve distinct field data from collection
     * @param string $field
     * @param array $filter
     * @return int
     */
    public function distinct($field, $filter = array()){
        $this->reset();
        return parent::distinctInCollection($this->_collection, $field, $filter);
    }

    /**
     * Insert or update object on collection
     * @return mixed
     */
    public function save(){

        if( !$this->getObjectChanges() ){
            $this->_saved = TRUE;
            return TRUE;
        }

        // Insert
        if( !$this->{$this->_primaryKey}
            OR !$this->_loaded ){

            $helper = new Utils;
            $primaryKey = $helper->generateShortUUID();

            $this->{$this->_primaryKey} = $primaryKey;
            $result = parent::insertInCollection(
                $this->_collection, $this->_object
            );

        // Update
        }else{

            $filter = array();
            $filter[ $this->_primaryKey ] = $this->__toString();
            $result = parent::replaceInCollection(
                $this->_collection, $filter, $this->_object
            );

        }

        $this->_saved = TRUE;
        // $this->reload();

        return $result;
    }

    /**
     * Remove object from collection
     * @return mixed
     */
    public function delete(){

        $filter = array();
        $filter[ $this->_primaryKey ] = $this->__toString();
        $this->reset();

        return parent::deleteInCollection($this->_collection, $filter);
    }

    /**
     * Reload object data
     * @return object
     */
    public function reload(){
        $this->findOne("{$this}");
        return $this;
    }

    /**
     * Reset object data
     * @return object
     */
    public function reset(){

        $this->_object = array();
        $this->_objectOriginal = array();
        $this->_objectChanges = array();
        $this->_loaded = FALSE;
        $this->_saved = FALSE;

        return $this;
    }

}