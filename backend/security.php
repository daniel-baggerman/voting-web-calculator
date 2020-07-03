<?php
include '../vendor/autoload.php';
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;

function validate_token_to_election($token_string, $election_id){
    /*
    *   Function to verify that given token is valid, and verify that the credentials in the token match the election wishing to be accessed.
    *   Returns true if the http request is allowed to proceed. False otherwise.
    */

    if(is_null($token_string)){
        return false;
    }

    // only verify token if election type is public and password protected or private. 
    $election_data = executeselect("SELECT coalesce(public_private,1) public_private, coalesce(password_protect,0) password_protect
                                    FROM elections 
                                    WHERE election_id = ?",false,[$election_id])[0];

    if( (string) $election_data['public_private'] === "1" && (string) $election_data['password_protect'] === "0"){
        // Election is public and not password protected. Allow request to proceed.
        return true;
    }
    
    $token = (new Parser())->parse((string) $token_string);

    // First verify the token signature
    // Set up some vars for Verification
    $public_key = new Key('file://../../../ssl/keys/jwt/public_key.key');
    $signer = new Sha256();

    // If not verified, send error message.
    if( !($token->verify($signer, $public_key)) ){
        return false;
    }
    
    // Next validate token data
    $validation_data = new ValidationData();
    $validation_data->setElectionId( (int) $election_id);

    if( !($token->validate($validation_data)) ){
        $nowdate = new DateTime();
        $now = $nowdate->format('Y-m-d H:i:s');
        // $eid = $token->getClaim('eid');
        // $str = ($token->validate($validation_data)) ? 'false' : 'true';
        // file_put_contents("test.txt","$now - failed validation: $str\n", FILE_APPEND | LOCK_EX);
        $temp = $token->validate($validation_data) ? 'true' : 'false';
        file_put_contents("test.txt","$now - valid: $temp\n", FILE_APPEND | LOCK_EX);
        return false;
    }

    // They passed all the tests!
    return true;
}

?>