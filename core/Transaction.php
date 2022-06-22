<?php 

class Transaction
{
	private $fee = 0.01;

	public function create(
		string $from,
		string $to,
		float $amount,
		string $note,
		string $secret,
		string $type = 'transfer',
		string $miner = NULL
	)
	{
		$wallet = new Wallet;
		$address = json_decode($wallet->get($secret), true);
		if($from !== $to)
		{
			if($address['address'] == $from)
			{
				if($wallet->check($to) == true)
				{
					if($wallet->balance($from) > $amount && $amount !== 0)
					{
						if($amount < $this->fee_calc($amount) OR $amount == $this->fee_calc($amount))
						{
							$data = array(
								"success" => false,
								"message" => "Insufficent funds"
							);
							$response = json_encode($data);
							return $response;
						}
						else
						{
							$data = array(
								'from' => $from,
								'to' => $to,
								'amount' => $amount,
								'note' => htmlentities($note),
								'type' => $type,
								'miner' => $miner,
								'time' => time(),
								'fee' => $this->fee_calc($amount)
							);
							return $this->sign($data, $secret);
						}
					}
					else
					{
						$data = array(
							"success" => false,
							"message" => "Insufficent funds"
						);
						$response = json_encode($data);
						return $response;
					}
				}
				else
				{
					$data = array(
						"success" => false,
						"message" => "Invalid recepient given"
					);
					$response = json_encode($data);
					return $response;
				}
			}
			else
			{
				$data = array(
					"success" => false,
					"message" => "Secret key doesn't match"
				);
				$response = json_encode($data);
				return $response;
			}
		}
		else
		{
			$data = array(
				"success" => false,
				"message" => "Sender cannot be receiver"
			);
			$response = json_encode($data);
			return $response;
		}
	}

	function fee_calc(float $amount)
	{
		$fee = $amount * $this->fee;
		if($fee < 0.00000001)
		{
			return 0.00000001;
		}
		if($fee > $amount)
		{
			return 0;
		}
		return $fee;
	}

	public function read_live_transactions(bool $symbol = false)
	{
		$unconfirmed = $this->read_unconfirmed_blocks();
		$unconfirmed_tx = [];
		$confirmed_tx = [];
		for($i = count($unconfirmed)-1; $i >= 0; $i--)
		{
			$unconfirmed[$i]['amount'] = $unconfirmed[$i]['amount'] - $unconfirmed[$i]['fee'];
			$unconfirmed[$i]['amount'] = number_format($unconfirmed[$i]['amount'], COIN_DEC);
			$unconfirmed[$i]['fee'] = number_format($unconfirmed[$i]['fee'], COIN_DEC);
			if($symbol == true)
			{
				$unconfirmed[$i]['amount'] = $unconfirmed[$i]['amount'].' '.COIN_SYM;
				$unconfirmed[$i]['fee'] = $unconfirmed[$i]['fee'].' '.COIN_SYM;
			}
			$unconfirmed_tx[] = array(
				'id' => NULL,
				'previous_hash' => NULL,
				'current_hash' => NULL,
				'timestamp' => NULL,
				'message' => $unconfirmed[$i],
				'proof' => NULL
			);
		}
		$confirmed = $this->read_confirmed_blocks();
		for($i = count($confirmed)-1; $i >= 0; $i--)
		{
			$confirmed[$i]['message']['amount'] = $confirmed[$i]['message']['amount'] - $confirmed[$i]['message']['fee'];
			$confirmed[$i]['message']['amount'] = number_format($confirmed[$i]['message']['amount'], COIN_DEC);
			$confirmed[$i]['message']['fee'] = number_format($confirmed[$i]['message']['fee'], COIN_DEC);
			if($symbol == true)
			{
				$confirmed[$i]['message']['amount'] = $confirmed[$i]['message']['amount'].' '.COIN_SYM;
				$confirmed[$i]['message']['fee'] = $confirmed[$i]['message']['fee'].' '.COIN_SYM;
			}
			$confirmed_tx[] = $confirmed[$i];
		}
		$transactions = array(
			'unconfirmed' => $unconfirmed_tx,
			'confirmed' => $confirmed_tx
		);
		return $transactions;
	}

	public function read_all_transactions(string $address, bool $symbol = false)
	{
		$unconfirmed = $this->read_unconfirmed_blocks();
		$confirmed = $this->read_confirmed_blocks();
		$unconfirmed_tx = [];
		$confirmed_tx = [];
		$balance = 0;
		for($i = count($confirmed)-1; $i >= 0; $i--)
		{
			$block = $confirmed[$i];
			$block['message']['amount'] = $block['message']['amount'] - $block['message']['fee'];
			$block['message']['amount'] = number_format($block['message']['amount'], COIN_DEC);
			$block['message']['fee'] = number_format($block['message']['fee'], COIN_DEC);
			if($address == $block['message']['from'])
			{
				if($symbol == true)
				{
					$block['message']['amount'] = '-'.$block['message']['amount'].' '.COIN_SYM;
					$block['message']['fee'] = $block['message']['fee'].' '.COIN_SYM;
				}
				else
				{
					$block['message']['amount'] = '-'.$block['message']['amount'];
				}
				$confirmed_tx[] = $block;
			}
			elseif($address == $block['message']['to'])
			{
				if($symbol == true)
				{
					$block['message']['amount'] = '+'.$block['message']['amount'].' '.COIN_SYM;
					$block['message']['fee'] = $block['message']['fee'].' '.COIN_SYM;
				}
				else
				{
					$block['message']['amount'] = '+'.$block['message']['amount'];
				}
				$confirmed_tx[] = $block;
			}
		}
		for($i = count($unconfirmed)-1; $i >= 0; $i--)
		{
			$block = $unconfirmed[$i];
			$block['amount'] = $block['amount'] - $block['fee'];
			$block['amount'] = number_format($block['amount'], COIN_DEC);
			$block['fee'] = number_format($block['fee'], COIN_DEC);
			if($address == $block['from'])
			{
				if($symbol == true)
				{
					$block['amount'] = '-'.$block['amount'].' '.COIN_SYM;
					$block['fee'] = $block['fee'].' '.COIN_SYM;
				}
				else
				{
					$block['amount'] = '-'.$block['amount'];
				}
				$unconfirmed_tx[] =  array(
					'id' => NULL,
					'previous_hash' => NULL,
					'current_hash' => NULL,
					'timestamp' => NULL,
					'message' => $block,
					'proof' => NULL
				);
			}
			elseif($address == $block['to'])
			{
				if($symbol == true)
				{
					$block['amount'] = '+'.$block['amount'].' '.COIN_SYM;
					$block['fee'] = $block['fee'].' '.COIN_SYM;
				}
				else
				{
					$block['amount'] = '+'.$block['amount'];
				}
				$unconfirmed_tx[] =  array(
					'id' => NULL,
					'previous_hash' => NULL,
					'current_hash' => NULL,
					'timestamp' => NULL,
					'message' => $block,
					'proof' => NULL
				);
			}
		}
		$transactions = array(
			'unconfirmed' => $unconfirmed_tx,
			'confirmed' => $confirmed_tx
		);
		return $transactions;
	}

	public function global_count(bool $symbol = false)
	{
		$wallet = new Wallet;
		$global = $this->read_all_transactions($wallet->issuer);
		if($global['confirmed'])
		{
			if($symbol == true)
			{
				$balance = str_replace('-', '', $global['confirmed'][count($global['confirmed'])-1]['message']['amount']).' '.COIN_SYM;
			}
			else
			{
				$balance = str_replace('-', '', $global['confirmed'][count($global['confirmed'])-1]['message']['amount']);
			}
			return $balance;
		}
		if($symbol == true)
		{
			return '0 '.COIN_SYM;
		}
		else
		{
			return 0;
		}
	}

	private function sign(array $data, string $secret)
	{
		$array = array(
			'from' => $data['from'],
			'to' => $data['to'],
			'amount' => $data['amount'],
			'note' => $data['note'],
			'type' => $data['type'],
			'time' => $data['time'],
			'fee' => $data['fee'],
			'secret' => $secret
		);
		$sign = sign(json_encode($array), $secret);
		$data['sign'] = $sign;
		$unconfirmed = $this->read_unconfirmed_blocks();
		$unconfirmed[] = $data;
		write_file('unconfirmed', $unconfirmed);
		$data = array(
			'success' => true,
			'message' => 'Transaction added successfully'
		);
		$response = json_encode($data);
		return $response;
	}

	private function verify_sign(string $sign, array $data, string $secret)
	{
		$array = array(
			'from' => $data['from'],
			'to' => $data['to'],
			'amount' => $data['amount'],
			'note' => $data['note'],
			'type' => $data['type'],
			'time' => $data['time'],
			'fee' => $data['fee'],
			'secret' => $secret
		);
		$response = verify_sign($sign, json_encode($array), $secret);
		return $response;
	}

	private function read_unconfirmed_blocks()
	{
		$blocks = read_file('unconfirmed');
		$blocks = json_decode($blocks, true);
		return $blocks;
	}

	private function read_confirmed_blocks()
	{
		$blocks = read_file('transactions');
		$blocks = json_decode($blocks, true);
		return $blocks;
	}
}
?>