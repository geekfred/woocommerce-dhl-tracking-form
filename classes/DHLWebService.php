<?php

/**
 * Created by PhpStorm.
 * User: matti
 * Date: 2018-05-21
 * Time: 09:24
 * SEcustomerintegration@DHL.com
 */
class clsWSSEToken {
    private $UsernameToken;
    function __construct ($innerVal){
        $this->UsernameToken = $innerVal;
    }
}
class clsWSSEAuth {
    private $Username;
    private $Password;
    function __construct($username, $password) {
        $this->Username=$username;
        $this->Password=$password;
    }
}
class DhlWebservice
{
    function __construct($pass,$user){
        $this->wsdl = "https://actws.dhl.com/shipmentTrackingWsV4/services/shipmentTrackingWsV4?wsdl";
        $this->pass = $pass;
        $this->user = $user;
    }
    /***
     * @param $shipmentId What is the shipment ID we are looking for?
     * @return array|string
     */
    public function GetByShipmentIdPublic($shipmentId){
        $client = $this->getSoapClient();
        $xml = $this->makeSoapCall($client,"GetConsignmentsByIdentifierPublic",$this->GetPayloadForShipmentId($shipmentId));
        if(isset($xml->Body->Fault)){
            return "Could not find Package";
        }
        $history =  $xml->Body->GetConsignmentsByIdentifierPublicResponse->consignment->eventHistory;
        return $this->cleanResponse($history);
    }
    /***
     * Gets you a result only on your shipments. ! Requires API Credentials !
     * @param $shipmentId
     * @return array
     */
    public function GetByShipmentId($shipmentId){
        $client = $this->getSoapClient();
        $client->__setSoapHeaders($this->CreateLoginHeaders());
        $xml = $this->makeSoapCall($client,"GetConsignmentsByIdentifier",$this->GetPayloadForShipmentId($shipmentId));
        if(isset($xml->Body->Fault)){
            return "Could not find Package";
        }
        $history =  $xml->Body->GetConsignmentsByIdentifierResponse->consignment->eventHistory;
        return $this->cleanResponse($history);
    }
    public function GetShipmentByReferencePublic($reference){
        $client = $this->getSoapClient();
        $xml = $this->makeSoapCall($client,'GetConsignmentByReferencePublic',$this->GetPayloadForReference($reference));
        $history = ($xml->Body->GetConsignmentByReferencePublicResponse->consignmentPublic->eventHistory);
        return $this->cleanResponse($history);
    }
    public function GetShipmentByReference($reference){
        $client = $this->getSoapClient();
        $client->__setSoapHeaders($this->CreateLoginHeaders());
        $xml = $this->makeSoapCall($client,'GetConsignmentsByReference',$this->GetPayloadForReference($reference));
        $history = ($xml->Body->GetConsignmentsByReferenceResponse->consignment->eventHistory);
        return $this->cleanResponse($history);
    }
    private function GetPayloadForReference($ref){
        $wrapper = new StdClass;
        $wrapper->responseLocale = new SoapVar("SV",XSD_STRING);
        $wrapper->referenceData = new stdClass();
        $wrapper->referenceData->reference = new SoapVar($ref, XSD_STRING);
        $wrapper->referenceData->referenceType = new SoapVar("ALL", XSD_STRING);
        $params = new SoapVar($wrapper,XSD_ANYTYPE);
        return array($params);
    }
    /***
     * @param $shipmentId What shipmentID are we looking for?
     * @return array The final payload to be sent to the soapclient
     */
    private function GetPayloadForShipmentId($shipmentId){
        $wrapper = new StdClass;
        $wrapper->responseLocale = new SoapVar("SV",XSD_STRING);
        $wrapper->consignmentIdentification = new stdClass();
        $wrapper->consignmentIdentification->identification = new SoapVar($shipmentId, XSD_STRING);
        $wrapper->consignmentIdentification->identificationType = new SoapVar("generic", XSD_STRING);
        $params = new SoapVar($wrapper,XSD_ANYTYPE);
        return array($params);
    }
    /***
     * @return SoapClient Getting you a properly initialized and created SoapClient
     */
    private function getSoapClient(){
        $params = array ('encoding' => 'UTF-8', 'verifypeer' => false, 'verifyhost' => false, 'soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 1, "connection_timeout" => 1800 );
        $client = new SoapClient($this->wsdl,$params);
        return $client;
    }
    /***
     * @param $client The soapclient to use
     * @param $function What function to execute on the webservice
     * @param $payload What is the payload that should be sent
     * @return null|SimpleXMLElement The response
     */
    private function makeSoapCall($client,$function,$payload){
        $xml = null;

        try{
            $xml = $client->__soapCall($function,$payload);

        }
        catch(Exception $e){

            $res = $client->__getLastResponse();
            $clean_xml = $this->cleanXML($res);
            $xml = simplexml_load_string($clean_xml);

            if ($xml === false) {
                echo "Failed loading XML\n";
                foreach(libxml_get_errors() as $error) {
                    echo "\t", $error->message;
                }
            }

        }
        return $xml;
    }
    /***
     * @param $history All the event-history
     * @return array a new clean array with only the date, time and description
     */
    private function cleanResponse($history){
        $cleanData = array();
        foreach($history->eventData as $event){
            $cleanData[] = array(
                "date" => substr((string)$event->eventDate,0,10),
                "time" => substr((string)$event->eventTime,0,8),
                "descr" => (string)$event->eventDescription
            );

        }
        return $cleanData;
    }
    /***
     * @param $xmlstring The string to be cleaned
     * @return mixed|string The properly cleaned string
     */
    private function cleanXML($xmlstring){
        $xmlstring = substr($xmlstring,strpos($xmlstring,"<soap:"));
        $xmlstring = substr($xmlstring,0,strpos($xmlstring,":Envelope>")+10);
        $xmlstring = str_ireplace(['SOAP-ENV:', 'SOAP:','ns2:'], '', $xmlstring);
        return $xmlstring;
    }

    /***
     * @return SoapHeader Gettings you the proper login headers to consume the API
     */
    private function CreateLoginHeaders(){
        //Check with your provider which security name-space they are using.
        $strWSSENS = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
        $objSoapVarUser = new SoapVar($this->user, XSD_STRING,null,$strWSSENS,null,$strWSSENS);
        $objSoapVarPass =new SoapVar('<ns2:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $this->pass . '</ns2:Password>', XSD_ANYXML );
        $objWSSEAuth = new clsWSSEAuth($objSoapVarUser, $objSoapVarPass);
        $objSoapVarWSSEAuth = new SoapVar($objWSSEAuth, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'UsernameToken', $strWSSENS);
        $objWSSEToken = new clsWSSEToken($objSoapVarWSSEAuth);
        $objSoapVarWSSEToken = new SoapVar($objWSSEToken, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'UsernameToken', $strWSSENS);
        $objSoapVarHeaderVal=new SoapVar($objSoapVarWSSEToken, SOAP_ENC_OBJECT, NULL, $strWSSENS, 'Security', $strWSSENS);
        $objSoapVarWSSEHeader = new SoapHeader($strWSSENS, 'Security', $objSoapVarHeaderVal,true);
        return $objSoapVarWSSEHeader;
    }
}