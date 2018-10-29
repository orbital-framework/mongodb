<?php

namespace Orbital\MongoDb\Model;

use \Orbital\Framework\App;
use \MongoDB\Client as MongoClient;
use \Exception;

class Client {

    /**
     * Client uri
     * @var string
     */
    private $_uri;

    /**
     * Client uri options
     * @var array
     */
    private $_uriOptions;

    /**
     * Client driver options
     * @var array
     */
    private $_driverOptions;

    /**
     * Client resource
     * @var object
     */
    private $_client;

    /**
     * Set client connection
     * Also clear current connection
     * @param string $uri
     * @param array $uriOptions
     * @param array $driverOptions
     * @return void
     */
    public function setConnection(
        $uri,
        $uriOptions = array(),
        $driverOptions = array()
        ){

        $this->_uri = $uri;
        $this->_uriOptions = $uriOptions;
        $this->_driverOptions = $driverOptions;

        $this->_client = NULL;

    }

    /**
     * Connect with client
     * @throws Exception
     * @return void
     */
    public function connectClient(){

        if( $this->_client ){
            return FALSE;
        }

        try{

            if( !$this->_uri ){

                $data = App::get('mongodb');
                $data = $data['client'];

                $uri = $data['uri'];
                $uriOptions = ( isset($data['options']) )
                              ? $data['options'] : array();
                $driverOptions = ( isset($data['driver']) )
                                 ? $data['driver'] : array();

                $this->setConnection(
                    $uri,
                    $uriOptions,
                    $driverOptions
                );

            }

            $this->_client = new MongoClient(
                $this->_uri,
                $this->_uriOptions,
                $this->_driverOptions
            );

        }catch( Exception $e ){
            die(__('Error connecting to database: '). $e->getMessage());
        }

    }

    /**
     * Retrieve client
     * @return \MongoDB\Client
     */
    public function getClient(){
        $this->connectClient();
        return $this->_client;
    }

}