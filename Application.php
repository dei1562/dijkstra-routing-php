<?php

class Application
{

    protected $connections;

    /** @var  Graph */
    protected $graph;

    protected $result = null;

    protected $currentNode = null;


    /**
     * Create graph based on provided connections
     * @param array $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
        $this->createGraph();
    }


    /**
     * Create graph based on provided connections (edges)
     */
    public function createGraph()
    {
        $this->graph = new Graph();
        foreach ($this->connections as $baseNode => $conns) {
            foreach ($conns as $node => $value) {
                $this->graph->addedge($baseNode, $node, $value);
                $this->graph->addedge($node, $baseNode, $value);
            }
        }
    }


    /**
     * Helper function for colorizing CLI output
     *
     * @param $text
     * @param $status
     * @return string
     * @throws Exception
     */
    public static function colorize($text, $status)
    {
        $out = "";
        switch ($status) {
            case "SUCCESS":
                $out = "[42m"; //Green background
                break;
            case "FAILURE":
                $out = "[41m"; //Red background
                break;
            case "WARNING":
                $out = "[43m"; //Yellow background
                break;
            case "NOTE":
                $out = "[44m"; //Blue background
                break;
            default:
                throw new Exception("Invalid status: " . $status);
        }

        return chr(27) . "$out" . " $text " . chr(27) . "[0m";
    }


    /**
     * Do Djikstra algo on prepared graph
     *
     * @param $from
     * @param $to
     * @return array|null
     */
    public function findShortestPath($from, $to)
    {
        $this->result = $this->graph->getpath($from, $to);

        return $this->getResult();
    }


    /**
     * Return calculated result
     * @return array|null
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * Return distance on shortest path
     * @return int|mixed
     */
    public function getShortestPathDistance()
    {
        $sum = 0;
        $result = $this->result;
        $previous = array_shift($result);
        foreach ($result as $next) {
            $sum += $this->getDistance($previous, $next);
            $previous = $next;
        }

        return $sum;
    }


    /**
     * Return pairs on shortest path
     * @return array
     */
    public function getPairs($offset = 0)
    {
        $result = array_slice($this->result, $offset);
        $pairs = array();
        $previous = array_shift($result);
        foreach ($result as $next) {
            $pairs["$previous-$next"] = $this->getDistance($previous, $next);
            $previous = $next;
        }

        return $pairs;
    }


    /**
     * Return only pairs names array
     * @return array
     */
    public function getPairsNames()
    {
        $pairs = $this->getPairs();

        return array_keys($pairs);
    }


    /**
     * Return distance between nodes
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getDistance($from, $to)
    {
        if ($from == $to) {
            return 0;
        }

        //var_dump($from);
        //var_dump($to);

        return isset($this->connections[$from][$to]) ? $this->connections[$from][$to] : $this->connections[$to][$from];
    }


    /**
     * Get distance between two nodes in shortest path
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getDistanceByIndex($from, $to)
    {
        $from = $this->getNodeNameByIndex($from);
        $to = $this->getNodeNameByIndex($to);

        return $this->getDistance($from, $to);
    }


    /**
     * Randomizes connections (edge weights) and re-creates graph
     */
    public function randomizeConnections()
    {
        foreach ($this->connections as $fromNode => &$neighbours) {
            foreach ($neighbours as $toNode => &$value) {
                $value = rand(0, 15);
                if ($value == 0) {
                    $value = 99999;
                }
            }
        }
        $this->createGraph();
    }


    /**
     * Go to the next node in shortest path and set it as current
     * @return int|null
     */
    public function goToNextNodeInShortestPath()
    {
        // Start travel if not already  started
        if (is_null($this->currentNode)) {
            return $this->currentNode = 0;
        }

        return ++$this->currentNode;

    }


    /**
     * Returns previous node in shortest path
     * @return int|null
     */
    public function getPreviousNode()
    {
        $previous = ($this->getCurrentNode() - 1);
        if ($previous < 0) {
            $previous = 0;
        }

        return $previous;
    }


    /**
     * Reset current node to the beginning of shortest path
     */
    public function resetTrip()
    {
        $this->currentNode = 0;
    }


    /**
     * Returns boolean flag if current node is last in shortest path
     * @return bool
     */
    public function iAmOnLastNode()
    {
        // Am I on last node?
        if ($this->currentNode == (count($this->result) - 1)) {
            return true;
        }

        return false;
    }


    /**
     * Returns node name by its position in shortest path indexed array
     * @param $i
     * @return mixed
     */
    public function getNodeNameByIndex($i)
    {
        return $this->result[$i];
    }


    /**
     * Returns current node index in shortest path traversal
     * @return null|int
     */
    public function getCurrentNode()
    {
        return $this->currentNode;
    }


    /**
     * Returns name of current node in shortest path traversal
     * @return mixed
     */
    public function getCurrentNodeName()
    {
        return $this->getNodeNameByIndex($this->currentNode);
    }


    /**
     * Outputs summary of shortest path together with nodes, their distances and total distance
     */
    public function printShortestPath()
    {
        $result = $this->getResult();

        $previousNode = $result[0];
        foreach ($result as $node) {
            echo $node . " - " . $this->getDistance($previousNode, $node);
            $previousNode = $node;
            echo PHP_EOL;
        }

        echo PHP_EOL;

        echo 'SP Distance = ' . $this->getShortestPathDistance() . PHP_EOL . PHP_EOL;
    }

}