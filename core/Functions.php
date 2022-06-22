<?php 

function none_sha256_hash()
{
	$string = str_repeat(0, 64);
	return $string;
}

function repeat_zero(int $count)
{
	$string = str_repeat(0, $count);
	return $string;
}

function sha256(string $string)
{
	$string = trim($string);
	$hash = hash('sha256', $string);
	return $hash;
}

function ripemd128(string $string)
{
	$string = trim($string);
	$hash = hash('ripemd128', $string);
	return $hash;
}

function ripemd256(string $string)
{
	$string = trim($string);
	$hash = hash('ripemd256', $string);
	return $hash;
}

function openssl_key_pair_new(int $bits = 2048)
{
	$key = openssl_pkey_new([
		'private_key_bits' => $bits
	]);
	openssl_pkey_export($key, $privkey);
	$subject = openssl_pkey_get_details($key);
	$pubkey = $subject['key'];
	openssl_pkey_free($key);
	$pair = array(
		'pubkey' => trim($pubkey),
		'privkey' => trim($privkey)
	);
	return $pair;
}

function sign(string $data, string $secret)
{
	$hash = base64_encode(hash('sha256', $data, $secret));
	return $hash;
}

function verify_sign(string $hash, string $data, string $secret)
{
	$sign = sign('sha256', $data, $secret);
	if($hash == $sign)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function read_file(string $file)
{
	return file_get_contents(__DIR__.'/../data/'.$file.'.dat');
}

function write_file(string $file, $data)
{
	return file_put_contents(__DIR__.'/../data/'.$file.'.dat', json_encode($data));
}
?>