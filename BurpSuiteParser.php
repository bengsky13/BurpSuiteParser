<?php declare(strict_types=1);
class BurpSuiteParser
{
    private $ch;
    private $headers;
    private $head;
    private $body;
    private $ssl = true;
    public function __construct()
    {
        $this->ch = curl_init();
    }
    public function setRequest($request)
    {
        curl_close($this->ch);
        $this->ch = curl_init();
        $tmp = explode("\n\n", str_replace("\r", "", $request));
        $post = $tmp[1];
        $settings = explode("\n", $tmp[0]);
        $first = explode(" ", $settings[0]);
        $path = $first[1];
        $custom = $first[0];
        unset($settings[0]);
        $protocol = $this->ssl ? "https://" : "http://";
        $url = $protocol . str_replace("Host: ", "", $settings[1]) . $path;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $custom);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $settings);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        if ($custom !== "GET") {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post);
        }
        $result = curl_exec($this->ch);
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $head = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        $this->setHead($head);

        $this->setBody($body);
    }
    public function setSsl($isSSL = true)
    {
        $this->ssl = $isSSL;
    }
    private function setBody($result)
    {
        $this->body = $result;
    }
    public function getBody()
    {
        return $this->body;
    }
    private function setHead($result)
    {
        $this->head = $result;
    }
    public function getHead()
    {
        return $this->head;
    }
}