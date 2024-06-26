<?php

include "config.php";
require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include('../include/GoogleAuthenticator.php');


$curl_session;

//======================================================================================
// Algemene functies 

function heliosInit($url, $http_method = "GET")
{
    global $curl_session;
    global $helios_settings;

    if (isset($curl_session)) {
        curl_setopt($curl_session, CURLOPT_USERPWD, null);  // basic auth niet meer nodig, gebruik vanaf nu php session cookie
    } else {
        // inloggen
        $cookieFile = uniqid();

        // init curl sessie
        $curl_session = curl_init();

        curl_setopt($curl_session, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_session, CURLOPT_HEADER, true);      // curl response bevat header info

        curl_setopt($curl_session, CURLOPT_COOKIEJAR, "/tmp/$cookieFile");
        curl_setopt($curl_session, CURLOPT_COOKIEFILE, "/tmp/$cookieFile");

        curl_setopt($curl_session, CURLOPT_USERPWD, $helios_settings['username'] . ":" . $helios_settings['password']);  // basic auth

        if (isset($helios_settings['bypassToken'])) {
            $urlToken = sha1($helios_settings['bypassToken'] . $helios_settings['password']) ;
            $loginUrl = $helios_settings['url'] . "/Login/Login?token=" . $urlToken;
        } else {
            $loginUrl = $helios_settings['url'] . "/Login/Login";
        }

        curl_setopt($curl_session, CURLOPT_URL, $loginUrl);
        curl_setopt($curl_session, CURLOPT_CUSTOMREQUEST, $http_method);
        $result = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);
        $body = json_decode($body, true);

        $authorization = sprintf("Authorization: Bearer %s", $body['TOKEN']);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));

        if ($status_code == 200) {
            heliosInit($url, $http_method);
        }
    }
    $full_url = sprintf("%s/%s", $helios_settings['url'], $url);

    curl_setopt($curl_session, CURLOPT_URL, $full_url);
    curl_setopt($curl_session, CURLOPT_CUSTOMREQUEST, $http_method);
}


function emailInit()
{
    global $smtp_settings;

    $mail = new PHPMailer(true);

    try {
        //Server settings
        //      $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host = $smtp_settings['smtphost'];             //Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   //Enable SMTP authentication
        $mail->Username = $smtp_settings['smtpuser'];             //SMTP username
        $mail->Password = $smtp_settings['smtppass'];             //SMTP password
        $mail->SMTPSecure = $smtp_settings['smtpsecure'];            //Enable implicit TLS encryption
        $mail->Port = $smtp_settings['smtpport'];             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        return $mail;
    } catch (Exception $e) {
        echo $mail->ErrorInfo;
    }
    return null;
}

function emailError($result)
{
    global $curl_session, $smtp_settings;

    list($header, $body) = returnHeaderBody($result);
    $url = curl_getinfo($curl_session, CURLINFO_EFFECTIVE_URL);

    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: " . curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
    $mail->Body = "URL= " . $url . "\n\n";
    $mail->Body .= "HEADER :\n";
    $mail->Body .= print_r($header, true);
    $mail->Body .= "\n";
    $mail->Body .= "BODY :\n" . $body;

    $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    if (!$mail->Send()) {
        print_r($mail);
    }
}

function returnHeaderBody($response)
{
    global $curl_session;

    // extract header
    $headerSize = curl_getinfo($curl_session, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $header = getHeaders($header);

    // extract body
    $body = substr($response, $headerSize);
    return [$header, $body];
}

// Zet de headers in een array
function getHeaders($respHeaders)
{
    global $cookies;

    $headers = array();
    $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

    foreach (explode("\r\n", $headerText) as $i => $line) {
        if ($i === 0) {
            $headers['http_code'] = $line;
        } else {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }
    }
    return $headers;
}

// De debug functie, schrijft niets als de globale setting UIT staat
if (!function_exists('Debug'))
{
    function Debug($file, $line, $text)
    {
        global $app_settings;

        if ($app_settings['Debug'])
        {
            $arrStr = explode("/", $file);
            $arrStr = array_reverse($arrStr );
            $arrStr = explode("\\", $arrStr[0]);
            $arrStr = array_reverse($arrStr );

            $toLog = sprintf("%s: %s (%d), %s\n", date("Y-m-d H:i:s"), $arrStr[0], $line, $text);

            if ($app_settings['LogDir'] == "syslog")
            {
                error_log($toLog);
            }
            else
            {
                error_log($toLog, 3, $app_settings['LogDir'] . "debug.txt");
            }
        }
    }
}

// De debug functie, schrijft niets als de globale setting UIT staat
if (!function_exists('HeliosError'))
{
    function Error($file, $line, $text)
    {
        global $app_settings;

        Debug($file, $line, $text);
        if ($app_settings['Error'])
        {
            $arrStr = explode("/", $file);
            $arrStr = array_reverse($arrStr );
            $arrStr = explode("\\", $arrStr[0]);
            $arrStr = array_reverse($arrStr );

            $toLog = sprintf("%s: %s (%d), %s\n", date("Y-m-d H:i:s"), $arrStr[0], $line, $text);

            if ($app_settings['LogDir'] == "syslog")
                error_log($toLog);
            else
                error_log($toLog, 3, $app_settings['LogDir'] . "error.txt");
        }
    }
}