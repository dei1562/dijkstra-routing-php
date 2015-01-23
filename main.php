<?php

if (!isset($argv[2]) || !isset($argv[2])) {
    print 'Missing from and to arguments' . PHP_EOL;
    exit(0);
}

echo '
 ____  _____ _____ _____
|    \|   __|_   _|   __|
|  |  |__   | | | |  |  |
|____/|_____| |_| |_____|

======= FOI 2015 ========

     Antolović Zoran
     Huskanović Alen
      Pavlović Milan

';


require("Dijkstra.php");
require("Application.php");

Application::detectColorizeFlag($argv);

/**
 * Array of bidirectional connection weights that are basic input for application
 */
/*
$connections = array(
    'h1'  => array('h2' => 2, 'a1' => 5, 'cz2' => 9, 'cz1' => 7, 'i3' => 6),
    'h2'  => array('a1' => 3, 'cz2' => 6, 'nj3' => 9, 'i4' => 11),
    'a1'  => array('cz1' => 7, 'cz2' => 4, 'nj1' => 1, 'i4' => 5),
    'cz1' => array('cz2' => 1, 'nj2' => 3, 'nj3' => 5, 'b1' => 9),
    'cz2' => array('i3' => 10, 'nj1' => 4, 'nj2' => 4,),
    'nj1' => array('nj2' => 1, 'nj3' => 4, 'b1' => 2, 'f4' => 5),
    'nj2' => array('b1' => 7, 'f3' => 10, 'f1' => 8, 'f5' => 5),
    'nj3' => array('f1' => 15, 'f2' => 9, 's1' => 5, 'nj2' => 2),
    'b1'  => array('f4' => 1, 'f3' => 7, 'f5' => 9, 's1' => 10),
    's1'  => array('i1' => 3, 'i2' => 5, 'f5' => 2,),
    'i1'  => array('i3' => 5, 'i2' => 4, 'i4' => 8, 'f1' => 12),
    'i2'  => array('i4' => 2, 'f2' => 8, 'f3' => 11, 'f5' => 5),
    'i3'  => array('sh6' => 13, 'f5' => 4, 'f2' => 6),
    'i4'  => array('sh6' => 7, 'sh5' => 12, 'sh3' => 14),
    'f1'  => array('sh5' => 3, 'sh6' => 5),
    'f2'  => array('f4' => 7, 'f3' => 1, 'f1' => 2),
    'f3'  => array('sh4' => 15),
    'f4'  => array('f1' => 7),
    'f5'  => array('f1' => 4, 'sh6' => 11),
    'sh1' => array('p1' => 6, 'p2' => 12),
    'sh2' => array('sh1' => 3, 'sh4' => 5, 'p1' => 7),
    'sh3' => array('sh2' => 4, 'sh1' => 6, 'p1' => 5, 'p2' => 7),
    'sh4' => array('sh5' => 5, 'sh6' => 2, 'p1' => 4),
    'sh5' => array('sh6' => 3, 'p2' => 11, 'p1' => 13),
    'sh6' => array('p2' => 7, 'p1' => 9),
    'p1'  => array('p2' => 3),
    'p2'  => array('p2' => 0),
);
*/

// Load connections
$connections = json_decode(file_get_contents("web/data/connections.json"), true);

// Load positions and parse it
$positions = json_decode(file_get_contents("web/data/positions.json"), true);
$associativePositions = array();
foreach ($positions as $p) {
    $associativePositions[$p['label']] = array('lat' => $p['lat'], 'lng' => $p['lng']);

}
unset($positions);

$app = new Application($connections);

echo "1. Calculating shortest path from {$argv[1]} to {$argv[2]}" . PHP_EOL . PHP_EOL;
$app->findShortestPath($argv[1], $argv[2]);
$app->printShortestPath();

$namedShortestPath = $app->getResult();
file_put_contents('web/data/sp_data.json', json_encode($namedShortestPath));

if (!isset($argv[3]) || $argv[3] != "--simulate") {
    exit(0);
}
echo PHP_EOL;

$startNodeName = $argv[1];
$destinationNodeName = $argv[2];

echo "2. Simulating packet travel from {$startNodeName} to {$destinationNodeName}" . PHP_EOL . PHP_EOL;

$traversedDistanceSum = 0;
$tripHistory = array();

// Skip start node
$currentNode = $app->goToNextNodeInShortestPath();
$tripHistory[] = $app->getCurrentNodeName();

do {
    $currentNode = $app->goToNextNodeInShortestPath();
    $previousNode = $app->getPreviousNode();
    $traversedDistance = $app->getDistanceByIndex($previousNode, $currentNode);
    $traversedDistanceSum += $traversedDistance;
    $tripHistory[] = $app->getCurrentNodeName();

    echo "I am on node " . Application::colorize($app->getCurrentNodeName(), "SUCCESS") . " - " . $traversedDistance . PHP_EOL;
    if ($app->iAmOnLastNode()) {
        break;
    }

    $app->randomizeConnections();
    echo ' - Connections randomized.' . PHP_EOL;

    echo ' - Recalculating shortest path from ' . $app->getNodeNameByIndex($currentNode) . ' to ' . $destinationNodeName . PHP_EOL;
    $app->findShortestPath($app->getNodeNameByIndex($currentNode), $destinationNodeName);
    $app->printShortestPath();
    $app->resetTrip();
    sleep(1);

} while (1);

echo PHP_EOL;
echo Application::colorize('Packet reached destination ' . $destinationNodeName . ' with total distance travelled: ' . $traversedDistanceSum,
        'SUCCESS') . PHP_EOL;

// Open the file to get existing content
echo 'Writing json' . PHP_EOL;
$file = 'web/data/sp_modified_data.json';
file_put_contents($file, json_encode($tripHistory));

echo 'Server starting on localhost:5555' . PHP_EOL;
echo shell_exec('php -S localhost:5555 -t web');