<?php

use RedBeanPHP\R;

class BaseController
{
    public function getEntryById($typeOfBean, $queryStringKey): object
    {
        $id = $_GET[$queryStringKey] ?? null;
        if (!$id) {
            error(400, 'Invalid request: ID not provided');
        }

        $bean = R::load($typeOfBean, $id);
        if (!$bean->id) {
            error(404, $typeOfBean . ' with ID ' . $id . ' not found');
        }

        return $bean;
    }
    public function authorizeUser(): void
    {
        if (!isset($_SESSION['user'])) {
            error(401, 'Unauthorized');
            exit;
        }
    }
}
