<?php


declare(strict_types=1);

use App\Controllers\SobreController;
use App\Controllers\UbicacionController;
use App\Controllers\TramiteController;
use App\Controllers\FichaCustodiaController;
use App\Controllers\UsuarioController;

use App\Core\Router;


$router = new Router();

$sobreController = new SobreController();
$ubicacionController = new UbicacionController();
$tramiteController = new TramiteController();
$fichaCustodiaController = new FichaCustodiaController();
$usuarioController = new UsuarioController();

$router->get('/ubicaciones', [
    $ubicacionController,
    'index',
]);

$router->get('/sobres', [
    $sobreController,
    'index',
]);

$router->get('/sobres/nuevo', [
    $sobreController,
    'create',
]);

$router->get('/tramites/sin-sobre', [
    $tramiteController,
    'searchWithoutSobre',
]);

$router->get('/sobres/buscar-por-codigo', [
    $sobreController,
    'buscarPorCodigo',
]);

$router->get('/sobres/{id_sobre}', [
    $sobreController,
    'show',
]);

$router->get('/sobres/{id_sobre}/mover', [
    $sobreController,
    'moveForm',
]);

$router->post('/sobres/confirmar-fichas-impresas', [
    $fichaCustodiaController,
    'confirmPrinted',
]);

$router->post('/sobres/generar-fichas', [
    $fichaCustodiaController,
    'generate',
]);

$router->post('/sobres/{id_sobre}/mover', [
    $sobreController,
    'move',
]);

$router->post('/sobres/asignar-lote', [
    $sobreController,
    'assignBulk',
]);

$router->post('/api/sobres/{id_sobre}/asignar', [
    $sobreController,
    'assignFromScanner',
]);

$router->get('/usuarios/disponibles', [
    $usuarioController,
    'disponibles',
]);

$router->get('/api/sobres/{id_sobre}/documentos', [
    $sobreController,
    'documentsFromScanner',
]);

$router->post('/api/sobres/{id_sobre}/documentos', [
    $sobreController,
    'saveDocumentsFromScanner',
]);

return $router;
