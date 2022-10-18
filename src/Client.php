<?php
declare(strict_types=1);

namespace Orbital\MongoDb;

use \Exception;
use \Orbital\Framework\App;

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
     * @var \MongoDB\Client
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
        string $uri,
        array $uriOptions = array(),
        array $driverOptions = array()
    ): void {

        $this->_uri = $uri;
        $this->_uriOptions = $uriOptions;
        $this->_driverOptions = $driverOptions;
        $this->_client = null;

    }

    /**
     * Connect with client
     * @throws Exception
     * @return void
     */
    public function connectClient(): void {

        if( $this->_client ){
            return;
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

            $this->_client = new \MongoDB\Client(
                $this->_uri,
                $this->_uriOptions,
                $this->_driverOptions
            );

        }catch( Exception $e ){
            die('Error connecting to database: '. $e->getMessage());
        }

    }

    /**
     * Retrieve client
     * @return \MongoDB\Client
     */
    public function getClient(): \MongoDB\Client {
        $this->connectClient();
        return $this->_client;
    }

}