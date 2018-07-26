<?php

namespace Orbital\MongoDb\Model;

use \Orbital\MongoDb\Helper\Utils;
use \Orbital\MongoDb\Model\Collection;

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
        $results = parent::find($filter, $options);

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

            $options = array();

            $result = parent::insertOne(
                $this->_object,
                $options
            );

        // Update
        }else{

            $filter = array();
            $filter[ $this->_primaryKey ] = $this->__toString();
            $options = array();

            $result = parent::replaceOne(
                $filter,
                $this->_object,
                $options
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

        $options = array();

        $this->reset();
        return parent::deleteOne($filter, $options);
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