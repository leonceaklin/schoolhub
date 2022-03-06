<?php
namespace App\Classes;
use ReallySimpleJWT\Token;

class CredentialsManager{

  public function __construct(){
    $this->passphrase = config('auth.credentials_secret');
    $this->secret = config('auth.jwt_secret');
  }

  public function createToken($data){
    $encrypted = $this->encrypt($data);
    return Token::create($encrypted, $this->secret, time()+3600*24*360, "SchoolHub");
  }

  public function getCredentials($token){
    if(Token::validate($token, $this->secret)){
      $payload = Token::getPayload($token, $this->secret);
      $ciphertext = $payload['user_id'];
      return $this->decrypt($ciphertext);
    }
  }

  private function encrypt($data){
    $key = $this->passphrase;
    $plaintext = json_encode($data);
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
    return $ciphertext;
  }

  private function decrypt($ciphertext){
    $key = $this->passphrase;
    $c = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
    if (hash_equals($hmac, $calcmac)){
      return json_decode($original_plaintext);
    }
  }
}
