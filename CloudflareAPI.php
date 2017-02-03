<?php


class CloudflareAPI
{

    private $email;
    private $token_api;
    private $zone_identifier;

    /**
     * CloudflareAPI constructor.
     * @param $email string email of your account
     * @param $token_api string token api of your account
     * @param $zone_identifier string zone identifier of your domain
     */
    public function __construct($email, $token_api, $zone_identifier)
    {
        $this->email = $email;
        $this->token_api = $token_api;
        $this->zone_identifier = $zone_identifier;
    }

    /**
     * @param $type string type of record
     * @param $name string name of record
     * @param $content string content
     * @param $proxied boolean use proxy
     * @return string error or id of record
     */
    public function createRecord($type, $name, $content, $proxied = false)
    {
        $result = $this->doRequest('POST', '/zones/' . $this->zone_identifier . '/dns_records', ['type' => $type, 'name' => $name, 'content' => $content, 'proxied' => $proxied]);
        $json = json_decode($result, true);
        if ($json['success'])
            return $json['result']['id'];
        else
            return 'error';
    }

    /**
     * @param $id string id of record
     * @param $type string type of record (A, SRV, ...)
     * @param $name string name of record
     * @param $content string content
     * @param $proxied boolean use proxy
     * @return string error or id of record
     */
    public function updateRecord($id, $type, $name, $content, $proxied = false)
    {
        $result = $this->doRequest('PUT', '/zones/' . $this->zone_identifier . '/dns_records/' . $id, ['type' => $type, 'name' => $name, 'content' => $content, 'proxied' => $proxied]);
        $json = json_decode($result, true);
        if ($json['success'])
            return $json['result']['id'];
        else
            return 'error';
    }

    /**
     * @param $id string id of record
     * @return boolean success
     */
    public function deleteRecord($id)
    {
        $result = $this->doRequest('DELETE', '/zones/' . $this->zone_identifier . '/dns_records/' . $id, null);
        $json = json_decode($result, true);
        return $json['success'];
    }

    /**
     * @param $id string id of record
     * @return null|array informations
     */
    public function getRecordInfo($id)
    {
        $result = $this->doRequest('GET', '/zones/' . $this->zone_identifier . '/dns_records/' . $id, null);
        $json = json_decode($result, true);
        if ($json['success'])
            return $json['result'];
        else
            return null;
    }

    /**
     * @param $url string Url
     * @param $json array json array
     * @param $type string type of request (GET, POST, ...)
     * @return mixed
     */
    private function doRequest($type, $url, $json)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4' . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Auth-Email: ' . $this->email,
            'X-Auth-Key: ' . $this->token_api,
            'Content-Type: application/json'
        ));

        if ($json != null)
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));

        return curl_exec($ch);
    }
}