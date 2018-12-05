<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

/* Set credentials for authentication */
$cred = set_cred('ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

/* API mount */
$router->mount('/api', function() use ($router, $db, $cred) {
    http_content_type('application/json');

    /* Authentication check */
    $router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
        if (!check_cred($cred)){
            $feedback = [
                'type' => 'danger',
                'message' => 'Authentication failed. Please check the credentials.'
            ];
        echo json_encode($feedback);
        exit();
        }
    });

    /* Fallback route */
    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        echo '404 Page Not Found';
    });

    /* GET for reading all series */
    $router->get('/series', function() use ($db){
        $series = get_series($db);
        echo json_encode($series);
    });

    /* POST for creating series */
    $router->post('/series', function() use ($db) {
        $feedback = add_serie($db, $_POST);
        echo json_encode($feedback);
    });

    /* PUT for updating series */
    $router->put('/series/(\d+)', function($id) use ($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        $serie_info = get_serieinfo($db, $id);
        echo json_encode($serie_info);
    });

    /* DELETE for deleting series */
    $router->delete('/series/(\d+)', function($id) use($db) {
        $feedback = remove_serie($db, $id);
        echo json_encode($feedback);
    });

});

/* Run the router */
$router->run();
