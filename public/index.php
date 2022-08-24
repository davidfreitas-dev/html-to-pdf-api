<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Dompdf\Dompdf;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$app->add(new BasePathMiddleware($app));

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Microserviço de conversao de HTML para PDF');
    return $response;
});

$app->post('/', function (Request $request, Response $response, array $args) {    
    $dompdf = new Dompdf();
    
    $json = $request->getBody();
    $data = json_decode($json, true);

    $options = $dompdf->getOptions();
    $options->setIsRemoteEnabled(true);

    $dompdf->setOptions($options);
    $dompdf->loadHtml(base64_decode($data['content']));
    $dompdf->setBasePath('/../');
    $dompdf->setPaper('a4', 'portrait');
    $dompdf->render();

    $response->getBody()->write($dompdf->output());
    return $response->withHeader('Content-type', 'application/pdf');
});

$app->run();