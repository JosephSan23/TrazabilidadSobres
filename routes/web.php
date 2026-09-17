<?php


declare(strict_types=1);

use App\Controllers\SobreController;
use App\Controllers\UbicacionController;
use App\Controllers\TramiteController;
use App\Controllers\FichaCustodiaController;
use App\Core\Router;


$router = new Router();

$sobreController = new SobreController();
$ubicacionController = new UbicacionController();
$tramiteController = new TramiteController();
$fichaCustodiaController = new FichaCustodiaController();

$router->get('/ubicaciones', [
    $ubicacionController,
    'index',
]);

$router->get('/sobres', [
    $sobreController,
    'index',
]);

$router->get('/sobres(nuevo', [
    $sobreController,
    'create',
]);

$router->get('/tramites/sin-sobre', [
    $tramiteController,
    'searchWithoutSobre',
]);

$router->get('/sobres/{id_sobre}', [
    $sobreController,
    'show',
]);

$router->get('/sobres/{id_sobre}/mover', [
    $sobreController,
    'moveForm',
]);

$router->post('/sobres/generar-fichas', [
    $fichaCustodiaController,
    'generate',
]);

$router->post('/sobres/{id_sobre}/mover', [
    $sobreController,
    'move',
]);

return $router;