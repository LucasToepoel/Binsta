<?php

// loading
require __DIR__ . '/../vendor/autoload.php';
// session start
session_start();
    // get the requested URl without the query string
    $url = strtok($_SERVER["REQUEST_URI"], '?');

    // explode the URL on the forward slash, but we need to remove the first slash
    $slugs = explode('/', trim($url, '/'));


    $requestedController = empty($slugs[0]) ? 'post' : $slugs[0];
    $requestedMethod = empty($slugs[1]) ? 'index' : $slugs[1];

    //check if the requested $requestedController may be a method instead, if so swap them, leaving the $requestedController the default value
    $requestedController = strtolower($requestedController);

if (in_array($requestedController, ['index', 'show', 'create', 'edit', 'delete'])) {
    $requestedMethod = $requestedController;
    $requestedController = 'post';
}

//get user bean/session

$userBean = $_SESSION['user'] ?? null;
view('system/welcome.twig', ['user' => $userBean]);

// formatting
$controllerNameFormatted = ucfirst($requestedController) . 'Controller';
$methodFormatted = strtolower($requestedMethod);

// checking

switch (false) {
    case class_exists($controllerNameFormatted):
        error(404, 'Controller ' . $controllerNameFormatted . ' not found');
        break;
    case method_exists($controllerNameFormatted, $methodFormatted):
        error(404, 'Method ' . $controllerNameFormatted . '.' . $methodFormatted . ' not found');
        break;
}

/// acting

$controllerInstance = new $controllerNameFormatted();
try {
    return $controllerInstance->$methodFormatted();
} catch (Exception $e) {
    return error(500, $e->getMessage());
}
