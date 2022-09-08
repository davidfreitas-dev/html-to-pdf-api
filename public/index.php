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

    $header = file_get_contents(__DIR__ . '/header.html');
    $footer = file_get_contents(__DIR__ . '/footer.html');

    $dompdf->setOptions($options);
    $dompdf->loadHtml($header . $data['header'] . '</header>' . $data['body'] . $footer);
    $dompdf->setBasePath('/../');
    $dompdf->setPaper('a4', 'portrait');
    $dompdf->render();

    // Parameters
    $x          = 505;
    $y          = 790;
    $text       = "{PAGE_NUM} de {PAGE_COUNT}";     
    $font       = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');   
    $size       = 10;    
    $color      = array(0,0,0);
    $word_space = 0.0;
    $char_space = 0.0;
    $angle      = 0.0;

    $dompdf->getCanvas()->page_text(
        $x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle
    );

    // $b64PDF = chunk_split(base64_encode($dompdf->output()));

    // $response->getBody()->write($b64PDF);
    // return $response;

    $response->getBody()->write($dompdf->output());
    return $response->withHeader('Content-type', 'application/pdf');
});

$app->run();