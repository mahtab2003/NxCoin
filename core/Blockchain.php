<?php 

class Blockchain
{
	public $difficulty = MINE_DIFF;
	public $code = MINE_CODE;
	public $chain = [];

	public function __construct()
	{
		$this->chain = [];
	}

	private function add(Block $block)
	{
		$blocks = $this->read_confirmed_blocks();
		$blocks[] = $block;
		write_file('transactions', $blocks);
	}

	public function mine(Block $block)
	{
		if($this->valid() == NULL)
		{
			$block->id = $this->count_blocks();
			$block->previous_hash = $this->last_block('current_hash') ?? none_sha256_hash();
			while(True)
			{
				if(substr($block->sha256(), 0, $this->difficulty) === $this->code)
				{
					$block->current_hash = $block->sha256();
					$this->add($block);
					break;
				}
				else
				{
					$block->proof += 1;
				}
			}
		}
		else
		{
			return false;
		}
	}

	public function valid()
	{
		for($i = 0; $i <= count($this->chain); $i++)
		{
			$e = $i-1;
			$c = $e-1;
			$current_hash = $this->chain[$e]->previous_hash ?? none_sha256_hash();
			$previous_hash = $this->chain[$c]->current_hash ?? none_sha256_hash();
			if($previous_hash !== $current_hash)
			{
				return false;
				break;
			}
		}
	}

	private function read_confirmed_blocks()
	{
		$blocks = read_file('transactions');
		$blocks = json_decode($blocks, true);
		return $blocks;
	}

	private function last_block(string $param)
	{
		$blocks = $this->read_confirmed_blocks();
		if(!empty($blocks))
		{
			$last_block = $blocks[count($blocks)-1];
			if(isset($last_block[$param]))
			{
				return $last_block[$param];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return;
		}
	}

	private function count_blocks()
	{
		$blocks = $this->read_confirmed_blocks();
		$blocks = count($blocks);
		return $blocks;
	}
}

?>