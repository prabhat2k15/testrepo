<?php
class MemcachedAPI
{
    public $_apiURL;
    public $_token = 'j6h@!!3K(x*';
    public $_data;

    public function get($key=null)
    {
        if(empty($key)){
            return false;
        }
        $this->_data = array(
            "key"=>$key,
            "auth_token"=>$this->_token
        );
        $this->_apiURL = 'https://us.engine.myoperator.co/memcache/get';
        return json_decode($this->hitAPI());
    }

    public function set($key=null, $value=null)
    {
        if(empty($key)){
            return false;
        }
        $this->_data = array(
            "key"=>$key,
            "value"=>$value,
            "auth_token"=>$this->_token
        );

        $this->_apiURL = 'https://us.engine.myoperator.co/memcache/update';
        return json_decode($this->hitAPI());
    }

    public function increment($key=null, $value=1)
    {
        if(empty($key)){
            return false;
        }
        if(empty($value)){
            $value = 1;
        }
        $this->_data = array(
            "key"=>$key,
            "value"=>$value,
            "auth_token"=>$this->_token
        );

        $this->_apiURL = 'https://us.engine.myoperator.co/memcache/increment';
        return json_decode($this->hitAPI());
    }

    public function hitAPI($url=null, $data=null, $method='post')
    {
        if(empty($url)){
            $url = $this->_apiURL;
        }
        if(empty($data)){
            $data = json_encode($this->_data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data='.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}

?>