<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

require("./lib/splunk.php");
require("./lib/query/train.class.php");
require("./lib/query/system.class.php");



$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);


$app->get("/", function (Request $request, Response $response) {

	// Testing/debugging
	$urls = array(
		"/train/521",
		"/train/521/history",
		"/train/521/history/average",
		"/system",
		"/system/totals",
		);

	$output = "";
	foreach ($urls as $key) {
		$output .= "<a href=\"$key\">$key</a><br/>\n";
	}

    $response->getBody()->write($output);

});


$app->group("/train/{trainno}", function() {

	$this->get("", function(Request $request, Response $response, $args) {

		$splunk = new \Septa\Splunk();
		$train = new Septa\Query\Train($splunk);
    	$trainno = $request->getAttribute("trainno");

		$output = "<pre>" . print_r($train->get($args["trainno"]), true) . "</pre>";

	    $response->getBody()->write($output);

	});


	$this->get("/history", function(Request $request, Response $response, $args) {

		$splunk = new \Septa\Splunk();
		$train = new Septa\Query\Train($splunk);
    	$trainno = $request->getAttribute("trainno");

		$output = "<pre>" . print_r($train->getHistoryByDay($args["trainno"]), true) . "</pre>";

	    $response->getBody()->write($output);

	});


	$this->get("/history/average", function(Request $request, Response $response, $args) {

		$splunk = new \Septa\Splunk();
		$train = new Septa\Query\Train($splunk);
    	$trainno = $request->getAttribute("trainno");

		$output = "<pre>" . print_r($train->getHistoryHistoricalAvg($args["trainno"]), true) . "</pre>";

	    $response->getBody()->write($output);

	});

});


$app->group("/system", function() {

	$this->get("", function(Request $request, Response $response, $args) {
	
		$splunk = new \Septa\Splunk();
		$system = new Septa\Query\System($splunk);

		$num_trains = 10;
		$num_hours = 1;
		$span_min = 10;

		$output = "<pre>" . print_r($system->getTopLatestTrains($num_trains, $num_hours, $span_min), true) 
			. "</pre>";

	    $response->getBody()->write($output);

	});

	$this->get("/totals", function(Request $request, Response $response, $args) {

		$splunk = new \Septa\Splunk();
		$system = new Septa\Query\System($splunk);

		$num_days = 7;
		$output = "<pre>" . print_r($system->getTotalMinutesLateByDay($num_days), true) . "</pre>";

	    $response->getBody()->write($output);

	});

});


/**
* This endpoint is used for testing and development.
*/
$app->get("/test", function(Request $request, Response $response, $args) {

	$splunk = new \Septa\Splunk();
	$system = new Septa\Query\System($splunk);

	$output = "<pre>" . print_r($system->getTotalMinutesLateByDay($num_days), true) . "</pre>";

	$response->getBody()->write($output);

});

$app->run();



