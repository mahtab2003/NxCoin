<?php 

class Block
{
	public $id;
	public $previous_hash;
	public $current_hash;
	public $timestamp;
	public $message;
	public $proof;

	public function __construct(array $message, int $id = 1, string $previous_hash = '0', int $proof = 0)
	{
		$this->id = $id;
		$this->message = $message;
		$this->previous_hash = $previous_hash ?? none_sha256_hash();
		$this->timestamp = time();
		$this->proof = $proof;
		$this->current_hash = $this->sha256();
	}

	public function sha256()
	{
		$list = array(
			'id' => $this->id,
			'message' => $this->message,
			'previous_hash' => $this->previous_hash,
			'proof' => $this->proof
		);
		$string = json_encode($list);
		$hash = sha256($string);
		return $hash;
	}

	public function __toString()
	{
		$list = array(
			'id' => $this->id,
			'previous_hash' => $this->previous_hash,
			'current_hash' => $this->current_hash,
			'timestamp' => $this->timestamp,
			'message' => $this->message,
			'proof' => $this->proof
		);
		$string = json_encode($list);
		return $string;
	}
}
?>