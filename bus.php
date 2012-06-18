<?php
/**
 * The bus end point handler.
 * Decode request message(envelope) and invoke the requested method using reflection.
 *
 * @author Itzik Paz
 *
 */


/**
 * Response code describing successful operation
 */
define("RESPONSE_CODE_SUCCESS", 1);

/**
 * Response code describing erroneous operation
 */
define("RESPONSE_CODE_ERROR", 2);

/**
 * Define the path to the application's classes
 */
define("CLASS_BASE_DIRECTORY", $_SERVER['DOCUMENT_ROOT'] . 'examples/');

/**
 * Output the result message
 * @param   int     $responseCode   Result code
 * @param   mixed   $message        Message object / string to send to the client
 */
function sendResponse($responseCode, $message) {
    $responseCodeText = ($responseCode === RESPONSE_CODE_SUCCESS) ? "success" : "error";
    if (is_object($message) || is_array($message)) {
        $messageText = json_encode($message);

        if ($messageText === false) {
            $responseCodeText = "error";
            $messageText = "Failed serializing response message";
        }
    }
    else {
        $messageText = '"' . $message . '"';
    }

    echo '{"result": "' . $responseCodeText . '", "data": ' . $messageText . '}';
    exit;
}

/**
 * Validate request message
 * @param   string  $requestObject  Request message(envelope) content
 * @return  object  Decoded message object. In case of an error, the application will terminate and output the error to the client
 */
function validateRequest($requestObject) {
    $object = json_decode($requestObject);
    if ($object === false) {
        sendResponse(RESPONSE_CODE_ERROR, "Failed de-serializing request object");
    }

    if (!isset($object->service)) {
        sendResponse(RESPONSE_CODE_ERROR, "Service was not supplied");
    }

    if (!isset($object->method)) {
        sendResponse(RESPONSE_CODE_ERROR, "Method was not supplied");
    }

    return $object;
}

if (!isset($_POST['json'])) {
    sendResponse(RESPONSE_CODE_ERROR, "Missing request object");
}


$requestEnvelope = validateRequest($_POST['json']);
$fileName = strtolower($requestEnvelope->service) . ".class.php";

if (!file_exists(CLASS_BASE_DIRECTORY . $fileName)) {
    sendResponse(RESPONSE_CODE_ERROR, "Service was not found");
}

include CLASS_BASE_DIRECTORY . $fileName;

if (!class_exists($requestEnvelope->service)) {
    sendResponse(RESPONSE_CODE_ERROR, "Service initiator was not found");
}

$class = new $requestEnvelope->service;

if (!method_exists($class, $requestEnvelope->method)) {
    sendResponse(RESPONSE_CODE_ERROR, "Method was not found");
}
$methodName = $requestEnvelope->method;

$reflectionMethod = new ReflectionMethod($requestEnvelope->service, $methodName);
if (!$reflectionMethod->isPublic()) {
    sendResponse(RESPONSE_CODE_ERROR, "Method is not accessible");
}

$result = isset($requestEnvelope->data) ? $reflectionMethod->invokeArgs($class, $requestEnvelope->data) : $class->$methodName();

if ($result === false) {
    sendResponse(RESPONSE_CODE_ERROR, "Failed running method");
}
else {
    sendResponse(RESPONSE_CODE_SUCCESS, $result);
}

?>