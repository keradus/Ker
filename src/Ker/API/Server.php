<?php

namespace Ker\API;

/**
 * Abstrakcyjna klasa serwera API.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @copyright wlasnosc prywatna, wszelkie prawa zastrzezone, zakaz rozpowszechniania
 * @abstract
 */

##### WERSJA ROZWOJOWA #####

abstract class Server
{

    protected $apiVersion;
    protected $requestAcceptedTransport = array(
        "application/json" => "json",
        "application/soap+xml" => "soap",
    );
    protected $requestUrl;
    protected $requestTransport;
    protected $requestHeaders;
    protected $requestErrorDefaultCode = 500;
    protected $requestErrorStatuses = array(
        406 => "406 Not Acceptable",
        500 => "500 Internal Server Error",
        501 => "501 Not Implemented",
    );

    public function __construct($_url, $_apiVersion = null)
    {
        $this->requestUrl = $_url;

        if ($_apiVersion !== NULL) {
            $this->apiVersion = $_apiVersion;
        }

        $this->requestHeaders = getallheaders();
    }

    protected function negotiateRequestApiVersion()
    {
        if ($this->apiVersion === NULL) {
            return;
        }

        $requestVersion = (isset($this->requestHeaders["X-Api-Version"]) ? $this->requestHeaders["X-Api-Version"] : null);
        if ($requestVersion !== $this->apiVersion) {
            throw new ApiException("Unknown apiVersion", 500);
        }

        $version = $this->apiVersion;
        header("X-Api-Version: $version");
    }

    protected function negotiateRequestContentType()
    {
        $requestAccept = (isset($this->requestHeaders["Accept"]) ? $this->requestHeaders["Accept"] : null);
        if ($requestAccept === NULL || !isset($this->requestAcceptedTransport[$requestAccept])) {
            throw new ApiException("Unknown transport", 406);
        }
        $this->requestTransport = $this->requestAcceptedTransport[$requestAccept];
        header("Content-Type: $requestAccept");
    }

    protected function processRequestParams_json($_data)
    {
        return Helpers::jsonDecode($_data, 500);
    }

    protected function processRequestParams_soap($_data)
    {
        $data = array();

        $xml = simplexml_load_string($_data);
        if ($xml === false) {
            throw new ApiException("Decoding SOAP error");
        }

        $ns = $xml->getNamespaces(true);
        $soap = $xml->children($ns["soap"]);

        $data["module"] = (string) ($soap->Header->children($ns["m"])->module);
        foreach ($soap->Body->children($ns["m"]) as $key => $item) {
            $data["method"] = $key;
            $data["params"] = Helpers::soapXmlToArray($item);
            break;
        }

        return $data;
    }

    protected function processRequestParams()
    {
        $rawParams = file_get_contents("php://input");
        $callback = __FUNCTION__ . "_" . $this->requestTransport;

        return $this->$callback($rawParams);
    }

    protected function processRequestOutput_json(array $_)
    {
        return Helpers::jsonEncode($_["result"], 500);
    }

    protected function processRequestOutput_soap(array $_)
    {
        return Helpers::soapGenerateMessage(array(
                    "method" => $_["method"],
                    "methodData" => $_["result"],
                    "module" => $_["module"],
                    "url" => $this->requestUrl,
        ));
    }

    protected function processRequestOutput(array $_)
    {
        $callback = __FUNCTION__ . "_" . $this->requestTransport;

        return $this->$callback($_);
    }

    abstract protected function processRequest($_params);

    public function handleRequest()
    {
        try {
            $this->negotiateRequestApiVersion();
            $this->negotiateRequestContentType();

            $requestParams = $this->processRequestParams();
            $result = $this->processRequest($requestParams);

            return $this->processRequestOutput(array(
                        "method" => $requestParams["method"],
                        "module" => $requestParams["module"],
                        "result" => $result,
            ));
        } catch (ApiException $e) {
            $errorCode = $e->getCode();
            $errorCode = (!empty($errorCode) ? $errorCode : $this->requestErrorDefaultCode);
            http_response_code($errorCode);
            header("Status: " . $this->requestErrorStatuses[$errorCode]);

            $message = $e->getMessage();
            if ($message !== NULL && $message !== "") {
                header("X-Api-Error: " . $message);
            }

            die;
        } catch (\Exception $e) {
            $errorCode = $this->requestErrorDefaultCode;
            http_response_code($errorCode);
            header("Status: " . $this->requestErrorStatuses[$errorCode]);

            die;
        }
    }

}
