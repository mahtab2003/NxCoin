<!DOCTYPE html>
<html>
<head>
	<title>Miner</title>
</head>
<body>

<?php
include 'load.php';

$miner = new Miner(1);

// Mine blocks
$miner->mine();
echo($miner->balance(true));

// Miner withdraw
//$miner->withdraw('c1470d9ceb2a21ba951fff41dd3b0bf2');

?>
</body>
</html>
