<?php

class Miner
{
	function __construct(string $miner = NULL)
	{
		$this->miner = $miner;
	}

	public function mine()
	{
		$blockchain = new Blockchain;
		while(True)
		{
			$unconfirmed = $this->read_unconfirmed_blocks();
			if(count($unconfirmed) > 0)
			{
				$unconfirm = $this->read_unconfirmed_blocks();
				$block = $unconfirm[0];
				$fee = $block['fee'];
				$block['miner'] = $this->miner;
				$mine = new Block($block);
				$blockchain->mine($mine);
				unset($unconfirm[0]);
				$array = array_values($unconfirm);
				write_file('unconfirmed', $array);
				$data = array(
					'message' => 'Block mined successfully. Reward added to your account',
					'reward' => $fee
				);
				$response = json_encode($data);
				return $response;
			}
			else
			{
				$data = array(
					'success' => true,
					'message' => "No blocks to mine"
				);
				$response = json_encode($data);
				return $response;
				break;
			}
		}
	}

	public function balance(bool $symbol = false)
	{
		if($this->miner !== NULL)
		{
			$blocks = $this->read_confirmed_blocks();
			if(count($blocks) > 0)
			{
				$balance = number_format(0, COIN_DEC);
				for($i = 0; $i < count($blocks); $i++)
				{
					$miner = $blocks[$i]['message']['miner'];
					$amount = $blocks[$i]['message']['fee'];
					if($miner == $this->miner)
					{
						$balance += $amount;
					}
				}
				if($symbol == true)
				{
					return $balance.' '.COIN_SYM;
				}
				else
				{
					return $balance;
				}
			}
			else
			{
				if($symbol == true)
				{
					return '0 '.COIN_SYM;
				}
				else
				{
					return 0;
				}
			}
		}
		else
		{
			$data = array(
				'success' => false,
				'message' => "Miner not set"
			);
			$response = json_encode($data);
			return $response;
		}
	}

	public function withdraw(string $address)
	{
		if($this->miner !== NULL)
		{
			if($this->balance() > 0)
			{
				$blocks = $this->read_confirmed_blocks();
				for($i = 0; $i < count($blocks); $i++)
				{
					$miner = $blocks[$i]['message']['miner'];
					if($this->miner == $miner)
					{
						$blocks[$i]['message']['miner'] = NULL;
					}
				}
				$wallet = new Wallet;
				$data = array(
					'from' => $wallet->issuer,
					'to' => $address,
					'amount' => $this->balance(),
					'note' => 'Miner reward withdrawal',
					'type' => 'withdraw',
					'miner' => NULL,
					'time' => time(),
					'fee' => 0
				);
				write_file('transactions', $blocks);
				$this->sign($data, $wallet->secret);
			}
			else
			{
				$data = array(
					'success' => false,
					'message' => "Your balance is 0"
				);
				$response = json_encode($data);
				return $response;
			}
		}
		else
		{
			$data = array(
				'success' => false,
				'message' => "Miner not set"
			);
			$response = json_encode($data);
			return $response;
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