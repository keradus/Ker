<?php

namespace Ker\API;

/**
 * Abstrakcyjna klasa klienta API.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright wlasnosc prywatna, wszelkie prawa zastrzezone, zakaz rozpowszechniania
 * @abstract
 */

##### WERSJA ROZWOJOWA #####

abstract class Client
{

    protected $apiVersion;
    protected $requestAcceptedCodes = array(200, 201);
    protected $requestAcceptedTransport = array(
        "json" => "application/json",
        "soap" => "application/soap+xml",
    );
    protected $requestCurlHandler;
    protected $requestUrl;
    protected $requestLastResult;
    protected $requestLastResultInfo;
    protected $requestDataType;
    protected $requestResultHeaders;
    protected $requestTransport;
    protected $requestTransportContentType;
    protected $requestCurlOpts = array(
        CURLINFO_HEADER_OUT => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(),
    );

    public function __construct($_url, $_transport = "json", $_apiVersion = null)
    {
        $this->requestUrl = $_url;

        $this->requestCurlHandler = curl_init($_url);
        if ($this->requestCurlHandler === false) {
            throw new ApiException("cUrl initialize error");
        }

        $this->requestCurlOpts[CURLOPT_HEADERFUNCTION] = array($this, "parseRequestHeaders");

        $this->setRequestCurlOption($this->requestCurlOpts);
        $this->setRequestTransport($_transport);

        if ($_apiVersion !== null) {
            $this->apiVersion = $_apiVersion;
            $this->setRequestHeader("X-Api-Version", $this->apiVersion);
        }
    }

    public function __destruct()
    {
        curl_close($this->requestCurlHandler);
    }

    public function getRequestLastResult()
    {
        return $this->requestLastResult;
    }

    public function getRequestLastResultInfo()
    {
        return $this->requestLastResultInfo;
    }

    protected function parseRequestHeaders($_ch, $_header)
    {
        $header = explode(":", $_header, 2);
        if (count($header) === 2) {
            $this->requestResultHeaders[trim($header[0])] = trim($header[1]);
        }

        return strlen($_header);
    }

    protected function sendRequest_clearState()
    {
        $this->requestResultHeaders = array();
        $this->requestLastResult = null;
        $this->requestLastResultInfo = null;
    }

    public function sendRequest_decodeData_json($_data)
    {
        return Helpers::jsonDecode($_data);
    }

    public function sendRequest_decodeData_soap($_data)
    {
        $data = null;

        $xml = simplexml_load_string($_data);
        if ($xml === false) {
            throw new ApiException("Decoding SOAP error");
        }

        $ns = $xml->getNamespaces(true);
        $soap = $xml->children($ns["soap"]);

        foreach ($soap->Body->children($ns["m"]) as $key => $item) {
            $data = Helpers::soapXmlToArray($item);
            break;
        }

        return $data;
    }

    protected function sendRequest_decodeData($_data)
    {
        $callback = __FUNCTION__ . "_" . $this->requestTransport;

        return $this->$callback($_data);
    }

    public function sendRequest_encodeData_json(array $_)
    {
        return Helpers::jsonEncode($_);
    }

    public function sendRequest_encodeData_soap(array $_)
    {
        return Helpers::soapGenerateMessage(array(
                    "method" => $_["method"],
                    "methodData" => $_["params"],
                    "module" => $_["module"],
                    "url" => $this->requestUrl,
        ));
    }

    protected function sendRequest_encodeData(array $_)
    {
        $callback = __FUNCTION__ . "_" . $this->requestTransport;

        return $this->$callback($_);
    }

    public function sendRequest($_data = array())
    {
        $this->sendRequest_clearState();

        $this->setRequestCurlOption(CURLOPT_POSTFIELDS, $this->sendRequest_encodeData($_data));

        $ch = $this->requestCurlHandler;

        $data = curl_exec($ch);
        if ($data === false) {
            throw new ApiException(curl_error($ch), curl_errno($ch));
        }

        $info = curl_getinfo($ch);
        if ($info === false) {
            throw new ApiException(curl_error($ch), curl_errno($ch));
        }
        $info = array_merge($info, $this->requestResultHeaders);
        $this->requestLastResultInfo = $info;

        if (!in_array($info["http_code"], $this->requestAcceptedCodes)) {
            throw new ApiException("Response http_code error: " . $info["http_code"] . (isset($info["X-Api-Error"]) ? ", reason: " . $info["X-Api-Error"] : ""), $info["http_code"]);
        }

        if ($info["content_type"] !== $this->requestTransportContentType) {
            throw new ApiException("Response content_type mismatched: " . $info["content_type"] . (isset($info["X-Api-Error"]) ? ", reason: " . $info["X-Api-Error"] : ""));
        }

        if ($this->apiVersion !== NULL) {
            $responseVersion = (isset($info["X-Api-Version"]) ? $info["X-Api-Version"] : null);
            if ($responseVersion !== $this->apiVersion) {
                throw new ApiException("Response ApiVersion incorrect: " . $responseVersion);
            }
        }

        $this->requestLastResult = $this->sendRequest_decodeData($data);

        return $this->requestLastResult;
    }

    public function setRequestCurlOption($_opts, $_value = null)
    {
        // umozliwiamy podanie tablicy lub pary nazwa-wartosc
        $opts = (is_array($_opts) ? $_opts : array($_opts => $_value));

        foreach ($opts as $name => $value) {
            // nie ustawiam jednoczesnie wszystkich opcji by miec kontrole na ktorej opcji wystapil blad
            if (!curl_setopt($this->requestCurlHandler, $name, $value)) {
                throw new ApiException("Error while setting option: `$name`=`$value`");
            }
        }
    }

    public function setRequestHeader($_name, $_value = null)
    {
        $headers = $this->requestCurlOpts[CURLOPT_HTTPHEADER];
        $headers[] = ($_value === null ? $_name : "$_name: $_value");
        $this->requestCurlOpts[CURLOPT_HTTPHEADER] = $headers;

        $this->setRequestCurlOption(CURLOPT_HTTPHEADER, $headers);
    }

    protected function setRequestTransport($_transport)
    {
        if ($this->requestTransport !== null) {
            new ApiException("Transport already set");
        }

        if (!isset($this->requestAcceptedTransport[$_transport])) {
            throw new ApiException("Unknown transport: $_transport");
        }

        $this->requestTransportContentType = $this->requestAcceptedTransport[$_transport];
        $this->setRequestHeader("Accept", $this->requestTransportContentType);
        $this->requestTransport = $_transport;
    }

}
