# Packet routing - Dijkstra's shortest path-algorithm in PHP

Simple implementation that emulates packet travel in network, between routers with random connection weight changes. App firstly calculates shortest path and on each hop randomizes connections and recalculates.

If simulate flag is provided, app will start simple server on localhost:5555 where you can find Google Maps Polylines visualization of graph. At bottom left you can find controls for filtering visualized content.

- **Author: Zoran Antolovic, Croatia**
- Forked from: https://github.com/shivas/PHP-Dijkstra


# CLI command start
- `php main.php h2 p2` for basic SP calculation
- `php main.php h2 p2 --simulate` for basic SP calculation and packet routing simulation
- `php main.php h2 p2 --simulate --colorize` for colorized output in bash
