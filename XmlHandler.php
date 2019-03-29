<?php

namespace XmlHandler;

use SimpleXMLElement;

class XmlParser
{

    /**
     * @var object $jsonData
     * @var string $xmlInstance
     */
    protected $jsonData, $xmlInstance;

    /**
     * Flag to verify first child of the array of fields
     * @var boolean
     */
    private $first = true;

    /**
     * Process key used to insert in the message table
     * @var string
     *
     */
    private $keyProcess = '';

    /**
     * XmlParser Constructor
     *
     * Receives the JSON data and name of the XML template and call the main method to convert to XML
     *
     * @param mixed[] $data Array with data to convert to XML
     * @param string $name Name of XML
     * @param Type array
     *
     * @return void
     */
    public function __construct($data = null, $name = null)
    {
        if (isset($data) && isset($name)) {
            $this->xmlInstance = new SimpleXMLElement('<Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>');
            $this->xmlInstance->addChild('Header');
            $body = $this->xmlInstance->addChild('Body');
            $this->convertJson2Xml($data, $body);
        }
    }

    /**
     * Convert JSON to XML
     *
     * According to data defined in the construct
     *
     * @param array $data
     * @param object $xmlInstance
     *
     * @return void
     */
    public function convert2Xml($data, $xmlInstance)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            if (is_array($value)) {

                $subnode = $xmlInstance->addChild($key);
                if($this->first) {
                    $this->keyProcess = $key;
                    $subnode->addAttribute("p1:edp","","https://edp.pt/SwitchingElectricity/GAR_ORD/v1");
                    $this->first = false;
                }
                $this->convertJson2Xml($value, $subnode);
            } else {
                if (strpos($key, '@') !== false) {
                    $xmlInstance->addAttribute(str_replace('@', '', $key), $value);
                } else {
                    $xmlInstance->addChild("$key", htmlspecialchars("$value"));
                }
            }
        }
    }

    /**
     * Returns JSON converted to XML
     *
     * instance $xmlInstance as XML string
     *
     * @return string Returns a XML
     */
    public function build()
    {
        return $this->xmlInstance->asXML();
    }

    /**
     * Unbuild
     *
     * @param string $xmlString
     *
     * @return array
     */
    public function unbuild($xmlString)
    {
        $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);

        return json_decode($json, true);
    }
}