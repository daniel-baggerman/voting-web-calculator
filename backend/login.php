<?php
include 'vote_db.php';
include '../vendor/autoload.php';
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/*
 *  Authenticate a request to login from user. If they have the right password, give them a JWT with the election info.
 */

// Grab zee data
$post_body = json_decode(file_get_contents('php://input'), true);

if(json_last_error() != 0){
    echo json_last_error_msg();
    exit();
};

$db_password_data = executeselect('SELECT   election_id, 
                                            coalesce(public_private,1) public_private, 
                                            coalesce(password_protect,0) password_protect, 
                                            password, 
                                            url_election_name
                                    FROM elections 
                                    WHERE url_election_name = ?',
                                    false, 
                                    [$post_body['url_election_name']])[0];

if(is_string($db_password_data)){
    http_response_code(500);
    echo "Error fetching data from database. Error message: ".$db_password_data;
    exit();
}

// Two possible treatments requiring a JWT:
//   1. A public election requiring a password
//   2. A private election requiring the user authenticate themselves.
// In the first case, check the password in the post request against the stored password, and if valid, give a token with an election_id
// In the second case, check the user code in the post request against the available users, and if available, give a token with an election_id and voter_id
if (intval($db_password_data['public_private']) === 1 && intval($db_password_data['password_protect']) === 1 ) {
    // Public with password logic
    $password = $db_password_data['password'];
    if(password_verify($post_body['code'],$db_password_data['password'])){
        // Issue temporary, bogus voter_id
        $rtn = executesql("INSERT INTO voters (voter_id, voter_name)
                            SELECT coalesce(max(voter_id),0)+1, 'Schulze'||(coalesce(max(voter_id),0)+1) from voters");

        if($rtn <> "OK"){
            http_response_code(500);
            echo "Failure to insert voter_id.";
        }

        $voter_id = select_scalar('SELECT max(voter_id) FROM voters');

        // token stuff
        $signer = new Sha256();
        $private_key = new Key('file://../../../ssl/keys/jwt/private_key.key');
        $token = (new Builder())->expiresAt(time()+3600)
                                ->withClaim('eid', (string) $db_password_data['election_id'])
                                ->withClaim('uen', (string) $db_password_data['url_election_name'])
                                ->withClaim('vid', (string) $voter_id)
                                ->getToken($signer, $private_key);

        // return token
        // apparently you can't json_encode() a token :(
        echo $token;
    } else {
        // return validation error http response
        http_response_code(401);
        echo "Incorrect password.";
    }
} else 
if (intval($db_password_data['public_private']) === 0) {
    // Private with user ballot code logic
    $voter_id = select_scalar('SELECT v.voter_id
                                FROM voters v
                                JOIN election_voter_list evl ON v.voter_id = evl.voter_id
                                WHERE v.voter_name = ?', [$post_body['code']]);

    if(!is_null($voter_id)){
        $token = (new Builder())->expiresAt(time()+3600)
                                ->withClaim('eid',$db_password_data['election_id'])
                                ->withClaim('uen',$db_password_data['url_election_name'])
                                ->withClaim('vid',$voter_id)
                                ->getToken();

        echo $token;
    } else {
        http_response_code(401);
        echo "No such user.";
    }
} else {
    http_response_code(500);
    echo "Epic fail :'(";
}