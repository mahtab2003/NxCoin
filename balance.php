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
		<input type="text" name="address" placeholder="address">
		<button>Check</button>
	</form>
	<?php
	include 'load.php';
	if (isset($_GET['address'])) {
		$wallet = new Wallet;
		$res = $wallet->balance(
			$_GET['address'],
			true
		);
		echo('Wallet balance is: '.$res);

		$tx = new Transaction;
		$res = $tx->read_all_transactions($_GET['address'], true);
	?>
		<br>
		<h4>Unconfirmed Transactions</h4>
		<table style="text-align: left">
			<tr>
				<th>ID</th>
				<th>Sender</th>
				<th>Receiver</th>
				<th>Amount</th>
				<th>Fee</th>
				<th>Comment</th>
				<th>Time</th>
			</tr>
			<?php 
				for ($i = 0; $i < count($res['unconfirmed']); $i++) { 
			?>
			<tr>
				<td><?php echo $i ?></td>
				<td><?php echo $res['unconfirmed'][$i]['message']['from'] ?></td>
				<td><?php echo $res['unconfirmed'][$i]['message']['to'] ?></td>
				<td><?php echo $res['unconfirmed'][$i]['message']['amount'] ?></td>
				<td><?php echo $res['unconfirmed'][$i]['message']['fee'] ?></td>
				<td><?php echo $res['unconfirmed'][$i]['message']['note'] ?></td>
				<td><?php echo date('d-m-Y h:i:s A', $res['unconfirmed'][$i]['message']['time']) ?></td>
			</tr>
			<?php
				}
			?>
		</table>
		<h4>Confirmed Transactions</h4>
		<table style="text-align: left">
			<tr>
				<th>Hash</th>
				<th>Sender</th>
				<th>Receiver</th>
				<th>Amount</th>
				<th>Fee</th>
				<th>Comment</th>
				<th>Time</th>
			</tr>
			<?php 
				for ($i = 0; $i < count($res['confirmed']); $i++) { 
			?>
			<tr>
				<td><?php echo $res['confirmed'][$i]['current_hash'] ?></td>
				<td><?php echo $res['confirmed'][$i]['message']['from'] ?></td>
				<td><?php echo $res['confirmed'][$i]['message']['to'] ?></td>
				<td><?php echo $res['confirmed'][$i]['message']['amount'] ?></td>
				<td><?php echo $res['confirmed'][$i]['message']['fee'] ?></td>
				<td><?php echo $res['confirmed'][$i]['message']['note'] ?></td>
				<td><?php echo date('d-m-Y h:i:s A', $res['confirmed'][$i]['message']['time']) ?></td>
			</tr>
			<?php
				}
			?>
		</table>
	<?php
	}
	?>
</body>
</html>