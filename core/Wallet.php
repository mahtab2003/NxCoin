<?php 

class Wallet
{
	private $prefix = 'c1';
	private $count = 2;
	public $issuer = 'c17fa3c1d9288a6d4f9619bae920eb65';
	public $secret = 'f453ad1710d7f8e577182bdfd191a751aaebeb373a866b31564d40dd9e8b6a1b';

	public function create()
	{
		$keys = openssl_key_pair_new();
		$hex = bin2hex($keys['privkey']);
		$sha256 = sha256($hex);
		$ripemd256 = ripemd256($hex);
		$wallet = array(
			'address' => sha256($ripemd256),
			'secret' => $ripemd256,
			'nonce' => 0 
		);
		while(true)
		{
			if(substr($wallet['address'], 0, $this->count) == $this->prefix)
			{
				$wallets = $this->read_wallets();
				$wallets[] = $wallet;
				write_file('wallet', $wallets);
				$data = json_encode($wallet);
				return $data;
			}
			else
			{
				$wallet['address'] = ripemd128($wallet['address'].'0');
				$wallet['nonce'] += 1;
			}
		}
	}

	public function get(string $secret)
	{
		$wallets = $this->read_wallets();
		for ($i = 0; $i < count($wallets); $i++)
		{
			$wallet = $wallets[$i];
			if($wallet['secret'] == $secret)
			{
				$address = $wallet['address'];
				$nonce = $wallet['nonce'];
				$verify = $this->verify($address, $secret, $nonce);
				if($verify == true)
				{
					$data = array(
						"success" => true,
						"message" => "Address retrived successfully",
						"address" => $address
					);
					$response = json_encode($data);
					return $response;
				}
				else
				{
					$data = array(
						"success" => false,
						"message" => "Something went's wrong"
					);
					$response = json_encode($data);
					return $response;
				}
			}
		}
		$data = array(
			"success" => false,
			"message" => "Address not found"
		);
		$response = json_encode($data);
		return $response;
	}

	private function verify(string $address, string $secret, int $nonce)
	{
		$secret = sha256($secret);
		for($i = 0; $i < $nonce; $i++)
		{
			$secret = ripemd128($secret.'0');
		}
		if($secret == $address)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function check(string $address)
	{
		$wallets = $this->read_wallets();
		for ($i = 0; $i < count($wallets); $i++)
		{
			$wallet = $wallets[$i];
			if($wallet['address'] == $address)
			{
				return true;
				break;
			}
		}
	}

	public function balance(string $address, bool $symbol = false)
	{
		if($address === $this->issuer)
		{
			$confirmed = $this->read_confirmed_blocks();
			if(!empty($confirmed))
			{
				$balance = 0;
				for($i = 0; $i < count($confirmed); $i++)
				{
					$block = $confirmed[$i];
					if($address == $block['message']['to'])
					{
						$balance += $block['message']['amount'];
					}
					$balance -= $block['message']['fee'];
				}
				if($symbol == true)
				{
					return $balance.' '.COIN_SYM;
				}
				return $balance;
			}
			else
			{
				if($symbol == true)
				{
					return '0 '.COIN_SYM;
				}
				return 0;
			}
		}
		else
		{
			$unconfirmed = $this->read_unconfirmed_blocks();
			$confirmed = $this->read_confirmed_blocks();
			if(!empty($confirmed))
			{
				$balance = 0;
				for($i = 0; $i < count($confirmed); $i++)
				{
					$block = $confirmed[$i];
					if($address == $block['message']['from'])
					{
						$amount = $block['message']['amount'];
						$fee = $block['message']['fee'];
						$total = $amount;
						$balance -= $total;
					}
					elseif($address == $block['message']['to'])
					{
						$amount = $block['message']['amount'];
						$fee = $block['message']['fee'];
						$total = $amount - $fee;
						$balance += $total;
					}
				}
				for($i = 0; $i < count($unconfirmed); $i++)
				{
					$block = $unconfirmed[$i];
					if($address == $block['from'])
					{
						$amount = $block['amount'];
						$fee = $block['fee'];
						$total = $amount;
						$balance -= $total;
					}
				}
				if($symbol == true)
				{
					return $balance.' '.COIN_SYM;
				}
				return $balance;
			}
			else
			{
				if($symbol == true)
				{
					return '0 '.COIN_SYM;
				}
				return 0;
			}
		}
	}

	private function read_wallets()
	{
		$wallets = read_file('wallet');
		$wallets = json_decode($wallets, true);
		return $wallets;
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