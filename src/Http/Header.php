<?php


namespace Zimuoo\Jpgkopen\Http;


class Header
{
    /** @var array normalized key name map */
    private $data = array();

    /**
     * @param array $obj non-normalized header object
     */
    public function __construct($obj = array())
    {

        return $this;
    }
    public static function parseRawText($raw)
    {
        $headers = array();
        $headerLines = explode("\r\n", $raw);
        foreach ($headerLines as $line) {
            $headerLine = trim($line);
            $kv = explode(':', $headerLine);
            if (count($kv) <= 1) {
                continue;
            }
            // for http2 [Pseudo-Header Fields](https://datatracker.ietf.org/doc/html/rfc7540#section-8.1.2.1)
            if ($kv[0] == "") {
                $fieldName = ":" . $kv[1];
            } else {
                $fieldName = $kv[0];
            }
            $fieldValue = trim(substr($headerLine, strlen($fieldName . ":")));
            if (isset($headers[$fieldName])) {
                array_push($headers[$fieldName], $fieldValue);
            } else {
                $headers[$fieldName] = array($fieldValue);
            }
        }
        return $headers;
    }
}