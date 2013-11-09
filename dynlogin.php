<?php

/**
 * A quick and dirty script to automatically login on dyn.com
 * in order to keep a free account active
 */

$CONF_FILE = __DIR__.DIRECTORY_SEPARATOR.'conf.php';

if (!file_exists($CONF_FILE)) {
    die("Please copy conf.dist.php to conf.php and fill-in your credentials.");
    return 1;
}

if (!extension_loaded('curl')) {
    die("Required extension 'curl' is not loaded.");
    return 1;
}

// Load configuration
require_once __DIR__.DIRECTORY_SEPARATOR.'conf.php';

// Cookie file
$CKFILE = __DIR__.DIRECTORY_SEPARATOR.'CURLCOOKIE';

// dyn.com URLs
$LOGIN_URL = "https://account.dyn.com/entrance/";
$ACCOUNT_URL = "https://account.dyn.com/";

// Functions **********************************************************************

function getRequest($url) {
    global $CKFILE;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($ch, CURLOPT_CAINFO, $CURLOPT_CAINFO);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);

    curl_setopt ($ch, CURLOPT_COOKIEJAR, $CKFILE);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, $CKFILE);

    $response = curl_exec($ch);
    $error = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        throw new Exception($error);
    }

    return $response;
}

function postRequest($url, $form)
{
    global $CKFILE;

    /*Initialisation de la ressource curl*/
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($ch, CURLOPT_CAINFO, $CURLOPT_CAINFO);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);

    curl_setopt ($ch, CURLOPT_COOKIEJAR, $CKFILE);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, $CKFILE);

    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $form);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        throw new Exception($error);
    }

    return $response;
}

function getLoginForm($url)
{
    $response = getRequest($url);

    $doc = new DOMDocument();
    $doc->loadHTML($response);

    $loginBox = $doc->getElementById('loginbox');
    $inputs = $loginBox->getElementsByTagName('input');

    $form = array();

    foreach($inputs as $input) {
        $name = $input->getAttribute('name');
        $value = $input->getAttribute('value');
        $form[$name] = $value;
    }

    return $form;
}

// Procedural **********************************************************************


// Clear cookie

if (file_exists($CKFILE)) {
    unlink($CKFILE);
}

$form = getLoginForm($LOGIN_URL);

$form['username'] = $USERNAME;
$form['password'] = $PASSWORD;

// Post login form with credentials
$postResult = postRequest($LOGIN_URL, $form);

if ($postResult === false) {
    return 2;
}

// Post OK, check account page
$response = getRequest('https://account.dyn.com/');
$logged_in = preg_match('/Welcome&nbsp;<b>'.strtolower($USERNAME).'<\/b>/', $response);

if (php_sapi_name() != "cli") {
    echo $response;
} else {
    echo ($logged_in) ? "Log in successful !\n" : "Log in failed :-(\n";
}

return ($logged_in) ? 0 : 3;
