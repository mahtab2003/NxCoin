<!DOCTYPE html>
<html>
<head>
	<title>Wallet Balance</title>
	<style type="text/css">
		body{
			font-family: sans-serif;
		}
	</style>
</head>
<body>
	<form action="" method="get">
		<button name="create">Create</button>
	</form>
	<?php
	include 'load.php';
	if (isset($_GET['create'])) {
		$wallet = new Wallet;
		$res = $wallet->create();
		$res = json_decode($res, true);
		echo('Wallet Address: '.$res['address'].'<br>');
		echo('Wallet Private Key: '.$res['secret']);
	?>
	<?php
	}
	?>
</body>
</html>