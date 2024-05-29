<?php

use RedBeanPHP\R;

function getconnection(): void
{
    if (!R::testConnection()) {
        R::setup('mysql:host=localhost;dbname=binsta', 'root', '');
    }
}

function view($name, $data = []): void
{
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/views'));
    echo $twig->render($name, $data);
}

function error($errorNumber, $errorMessage): void
{
    http_response_code($errorNumber);
    $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/views'));
    echo $twig->render('system/error.twig', ['errorNumber' => $errorNumber, 'errorMessage' => $errorMessage]);
    exit;
}

function returnPage(): void
{
    $location = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $location);
}

function sanitizeInput($data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function sanitizeFilename($filename): string
{
    $filename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);
    return $filename;
}
