<!DOCTYPE html>
<html>
<head>
	<title>Send NxCoin</title>
</head>
<body>
	<form action="" method="POST">
		<input type="text" name="from" placeholder="from">
		<input type="text" name="to" placeholder="to">
		<input type="text" name="amount" placeholder="amount">
		<input type="text" name="comment" placeholder="comment">
		<input type="text" name="priv_key" placeholder="private key">
		<button name="make">Transfer</button>
	</form>
</body>
</html>
<?php 
include 'load.php';
if (isset($_POST['make'])) {
	$trx = new Transaction;
	$res = $trx->create(
		$_POST['from'],
		$_POST['to'],
		$_POST['amount'],
		$_POST['comment'],
		$_POST['priv_key']
	);
	$res = json_decode($res, true);
	if($res['success'] !== false)
	{
		$bool = 'success';
	}
	else
	{
		$bool = 'failed';
	}
	echo('Status: '.$bool.'<br>');
	echo('Message: '.$res['message']);
}
?>