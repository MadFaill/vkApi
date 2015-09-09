<?php

namespace VkApi;

class Vk
{
    private $apiVersion = '5.37';
    private $accessToken;

    private static $connection;

    public function __destruct()
    {
        if (is_resource(static::$connection)) {
            curl_close(static::$connection);
        }
    }

    public function api($method, array $query = array())
    {
        /* Generate query string from array */
        foreach ($query as $param => $value) {
            if (is_array($value)) {
                // implode values of each nested array with comma
                $query[$param] = implode(',', $value);
            }
        }
        $query['access_token'] = $this->accessToken;

        if (empty($query['v'])) {
            $query['v'] = $this->apiVersion;
        }

        $url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($query);
        $result = json_decode($this->curl($url), true);

        return $result;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }

    protected function curl($url)
    {
        // create curl resource
        if (!is_resource(static::$connection)) {
            static::$connection = curl_init();
        }

        // set url
        curl_setopt(static::$connection, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt(static::$connection, CURLOPT_RETURNTRANSFER, TRUE);
        // disable SSL verifying
        curl_setopt(static::$connection, CURLOPT_SSL_VERIFYPEER, FALSE);
        // $output contains the output string
        $result = curl_exec(static::$connection);
        if (!$result) {
            $errno = curl_errno(static::$connection);
            $error = curl_error(static::$connection);
        }

        if (isset($errno) && isset($error)) {
            throw new \Exception($error, $errno);
        }

        return $result;
    }
}