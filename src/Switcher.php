<?php

/**
 * Kamailio API for SIP user register or change password
 *
 * @category Library
 * @package  SipSwitcher
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Properietary http://myoperator.co
 * @link     https://gitlab.com/adesh.pandey/
 */

/**
 * Kamailio API for webrtc
 *
 * @category Class
 * @package  SipSwitcher
 * @author   Prabhat Kumar <prabhat.kumar@myoperator.co>
 * @license  Properietary http://myoperator.co
 * @link     https://gitlab.com/adesh.pandey/
 */

class Switcher
{
    private $_config = null;
    private $_sipID;
    private $_failCount;
    private $memc;
    private $_response = false;
    private $_sipFile;

    public function __construct()
    {
       $this->memc = new MemcachedAPI(); 
       $this->loadConfigFile();
       $this->getFailCount();
       $this->getSipID();
    }

    /**
     * Validating various config file
     *
     * @return boolean
     */
    public function loadConfigFile()
    {
        //checking config.php file
        if(file_exists(dirname(__FILE__) . '/../config.php')){
            include_once(dirname(__FILE__) . '/../config.php');
            $this->_config = $config;
        }else{
            $this->_response = array(
                'message' => "config.php file not Found.",
                'status' => false);
            return false;
        }
        //checking sip file 
        if(file_exists($this->_config['SIP_CONFIG_PATH'].$this->_config['SIP_CONFIG_FILE'])){
            $this->_sipFile = $this->_config['SIP_CONFIG_PATH'].$this->_config['SIP_CONFIG_FILE'];//main sip file
        }else{
            $this->_response = array(
                'message' => "Sip Config file not Found.",
                'status' => false);
            return false;
        }
        return true;
    }

    /**
     * Call failed Notify 
     *
     * @return void
     */
    public function callFailedNotify()
    {
        //increase counter
        if(!$this->increment('failcount')){
            return json_encode($this->_response);
        }        
        //check counter
        if($this->_failCount % $this->_config['THRESHOLD'] == 0){
            //switch sipid
            if(!$this->increment('sipid')){
                return json_encode($this->_response);
            }
            //switch sip
            $this->sipSwitch();
        }
        return json_encode($this->_response);
    }

    /**
     * Sip conf switches to main sip conf file
     * 
     * @return void
     */
    public function sipSwitch()
    {
        //sip file switch logic
        if(!empty($this->_config['SIP_FILES'][$this->_sipID]) && file_exists($this->_config['SIP_CONFIG_PATH'].$this->_config['SIP_FILES'][$this->_sipID])){
            // getting next sip file contents
            $sipContents = file_get_contents($this->_config['SIP_CONFIG_PATH'].$this->_config['SIP_FILES'][$this->_sipID]);
            file_put_contents($this->_sipFile, $sipContents);
            
            $this->_response = array(
                'message' => "Sip conf switched.",
                'status' => true);
            return true;

        }else if(!empty($this->_config['SIP_FILES'][1])){

            //reset failcount and sip id
            $this->setFailCount(1);
            $this->setSipID(1);

            //reset sip conf
            $sipContents = file_get_contents($this->_config['SIP_CONFIG_PATH'].$this->_config['SIP_FILES'][$this->_sipID]);
            file_put_contents($this->_sipFile, $sipContents);

            $this->_response = array(
                'message' => "Sip file reset.",
                'status' => true);
            return true;

        }else{
            $this->_response = array(
                'message' => "Sip Config file not Found.",
                'status' => false);
            return false;
        }
    }

    /**
     * diplaying the values
     *
     * @return void
     */
    public function display()
    {
        echo 'failcount'.$this->_failCount.'<br>';
        echo 'sipid'.$this->_sipID.'<br>';
        print_r($this->_config);
    }

    /**
     * Sets the fail count in memcache
     * @param value integer
     * @return boolean
     */
    public function setFailCount($value)
    {
        $response  = $this->memc->set('failcount', $value);
        if($response->status == 'success'){
            $this->_failCount = $value;
            return true;
        }
        $this->_response = array(
            'message' => "API responded ".$response->status,
            'status' => false);
        return false;
    }

    /**
     * Fetches the latest fail count value from memcache api
     *
     * @return integer
     */
    public function getFailCount()
    {
        $response = $this->memc->get('failcount');
        if($response->status == 'error'){
            $this->_response = array(
                'message' => "API responded ".$response->status,
                'status' => false);
            return false;
        }
        if($response->status=='success' && !$response->result->failcount){
            if($this->setFailCount(1)){
                return $this->_failCount;
            }
        }
        $this->_failCount = $response->result->failcount;
        return $this->_failCount;
    }

    /**
     * Sets the sip id in the memcache 
     * @param value string
     * @return boolean
     */
    public function setSipID($value)
    {
        $response  = $this->memc->set('sipid', $value);
        if($response->status == 'success'){
            $this->_sipID = $value;
            return true;
        }
        $this->_response = array(
            'message' => "API responded ".$response->status,
            'status' => false);
        return false;
    }

    /**
     * Gets the sip id from the memcache api
     *
     * @return integer
     */
    public function getSipID()
    {
        $response = $this->memc->get('sipid');
        if($response->status == 'error'){
            $this->_response = array(
                'message' => "API responded ".$response->status,
                'status' => false);
            return false;
        }
        if($response->status=='success' && !$response->result->sipid){
            if($this->setSipID(1)){
                return $this->_sipID;
            }
            return $this->_response;
        }
        $this->_sipID = $response->result->sipid;
        return $this->_sipID;
    }

    /**
     * Increments the value in the memcache server 
     * @param key string
     * @param value integer
     * @return boolean
     */
    public function increment($key=null, $value=1)
    {
        if(empty($key)){
            $this->_response = array(
                'message' => "Key can not be empty",
                'status' => false);
            return false;
        }

        $response = $this->memc->increment($key, $value);
        if($response->status=='success'){
            $key == 'sipid' ? $this->_sipID++ : $this->_failCount++;
            $this->_response = array(
                'message' => "Call failcount incremented",
                'status' => true);
           return true;
        }
        $this->_response = array(
            'message' => "API responded ".$response->status,
            'status' => false);
        return false;

    }


    public function output()
    {
        return json_encode($this->_response);
    }
}

?>