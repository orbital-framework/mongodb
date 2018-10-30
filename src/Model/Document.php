<?php

namespace Orbital\MongoDb\Model;

use \Orbital\MongoDb\Helper\Utils;
use \Orbital\MongoDb\Model\Collection;
use \Orbital\Framework\Object;

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
    protected $_unique = FALSE;

    /**
     * Register object loaded status
     * @access protected
     * @var boolean
     */
    protected $_loaded = FALSE;

    /**
     * Primary key for object
     * @access protected
     * @var string
     */
    protected $_primaryKey = 'uuid';

    /**
     * Object data
     * @access protected
     * @var Object
     */
    protected $_object = NULL;

    /**
     * CONSTRUCTOR
     * @param mixed $filter
     * @return void
     */
    public function __construct($filter = NULL, $sort = array(), $skip = NULL){

        if( !$this->_unique ){
            $this->_unique = get_class($this);
        }

        if( !$this->_collection ){
            $this->_collection = strtolower( get_class($this) );
        }

        $this->setDatabaseCollection($this->_collection);

        if( !is_null( $filter ) ){
            $this->findOne($filter, $sort, $skip);
        }

    }

    /**
     * Retrieve object data
     * @return object
     */
    public function getObject(){

        if( $this->_object === NULL ){
            $this->_object = new Object;
        }

        return $this->_object;
    }

    /**
     * Magic method __toString
     * Returns primary key value
     * @return string
     */
    public function __toString(){
        return (string) $this->getData($this->_primaryKey);
    }

    /**
     * Magic method __get
     * @param string $item
     * @return mixed
     */
    public function __get($item){
        return $this->getData($item);
    }

    /**
     * Magic method __set
     * @param string $item
     * @param mixed $value
     * @return void
     */
    public function __set($item, $value){
        return $this->setData($item, $value);
    }

    /**
     * Magic method __isset
     * @param string $item
     * @return boolean
     */
    public function __isset($item){
        return $this->hasData($item);
    }

    /**
     * Magic method __unset
     * @param string $item
     * @return void
     */
    public function __unset($item){
        return $this->unsData($item);
    }

    /**
     * Magic method __call
     * @param string $name
     * @param mixed $arguments
     * @return void
     */
    public function __call($name, $arguments){
        $callable = array($this->getObject(), $name);
        return \call_user_func_array($callable, $arguments);
    }

    /**
     * Return if object exists
     * @return boolean
     */
    public function exists(){
        return ( (boolean) $this->__toString() ) ? TRUE : FALSE;
    }

    /**
     * Convert array data to unique object collection class
     * @param array $data
     * @return object
     */
    public function toUnique($data){

        if( $this->_unique
            AND class_exists($this->_unique) ){

            $document = new $this->_unique;
            $document->_loaded = TRUE;
            $document->addData( (array) $data );

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
    public function findOne(
        $filter = array(),
        $sort = array(),
        $skip = NULL
        ){

        if( !is_array($filter) ){
            $_filter = array();
            $_filter[ $this->_primaryKey ] = $filter;
            $filter = $_filter;
        }

        $options = array();
        $options['limit'] = 1;
        if( $sort ){ $options['sort'] = $sort; }
        if( $skip ){ $options['skip'] = $skip; }

        $this->reset();
        $document = parent::findOne($filter, $options);

        if( $document ){
            $this->_loaded = TRUE;
            $this->addData( (array) $document );
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
    public function find(
        $filter = array(),
        $sort = array(),
        $limit = NULL,
        $skip = NULL
        ){

        $options = array();
        if( $sort ){ $options['sort'] = $sort; }
        if( $limit ){ $options['limit'] = $limit; }
        if( $skip ){ $options['skip'] = $skip; }

        $this->reset();
        return parent::find($filter, $options);
    }

    /**
     * Count object from collection
     * @param array $filter
     * @param array $sort
     * @param int $limit
     * @param int $skip
     * @return int
     */
    public function count(
        $filter = array(),
        $sort = array(),
        $limit = NULL,
        $skip = NULL
        ){

        $options = array();
        if( $sort ){ $options['sort'] = $sort; }
        if( $limit ){ $options['limit'] = $limit; }
        if( $skip ){ $options['skip'] = $skip; }

        $this->reset();
        return parent::count($filter, $options);
    }

    /**
     * Retrieve aggregate data from collection
     * @param array $pipeline
     * @param array $options
     * @return object
     */
    public function aggregate($pipeline, $options = array()){
        $this->reset();
        return parent::aggregate($pipeline, $options);
    }

    /**
     * Retrieve distinct field data from collection
     * @param string $field
     * @param array $filter
     * @param array $options
     * @return object
     */
    public function distinct($field, $filter, $options = array()){
        $this->reset();
        return parent::distinct($field, $filter, $options);
    }

    /**
     * Insert or update object on collection
     * @return mixed
     */
    public function save(){

        if( !$this->getChanges() ){
            return TRUE;
        }

        // Insert
        if( !$this->__toString()
            OR !$this->_loaded ){

            $helper = new Utils;
            $primaryKey = $helper->generateShortUUID();

            $this->setData(
                $this->_primaryKey, $primaryKey
            );

            $options = array();

            $result = parent::insertOne(
                $this->toArray(),
                $options
            );

        // Update
        }else{

            $filter = array();
            $filter[ $this->_primaryKey ] = $this->__toString();
            $options = array();

            $result = parent::replaceOne(
                $filter,
                $this->toArray(),
                $options
            );

        }

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

        $options = array();

        $this->reset();
        return parent::deleteOne($filter, $options);
    }

    /**
     * Reload object data
     * @return object
     */
    public function reload(){
        $this->findOne( $this->__toString() );
        return $this;
    }

    /**
     * Reset object data
     * @return object
     */
    public function reset(){

        $this->cleanData();
        $this->_loaded = FALSE;

        return $this;
    }

}