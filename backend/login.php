<?php
include 'vote_db.php';
include 'vendor/autoload.php';
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

// Grab zee data
$post_body = json_decode(file_get_contents('php://input'), true);

if(json_last_error() != 0){
    echo json_last_error_msg();
};

$db_password_data = executeselect('SELECT ifnull(public_private,1) public_private, ifnull(password_protect,0) password_protect, password 
                                    FROM vote_elections 
                                    WHERE url_election_name = ?',
                                    false, 
                                    [$post_body['url_election_name']])[0];

$election_id = select_scalar('SELECT election_id FROM vote_elections WHERE url_election_name = ?', [$post_body['url_election_name']]);

// Two possible treatments requiring a JWT:
//   1. A public election requiring a password
//   2. A private election requiring the user authenticate themselves.
// In the first case, check the password in the post request against the stored password, and if valid, give a token with an election_id
// In the second case, check the user code in the post request against the available users, and if available, give a token with an election_id and voter_id
if (intval($db_password_data['public_private']) === 1 && intval($db_password_data['password_protect']) === 1 ) {
    // Public with password logic
    $password = $db_password_data['password'];
    if(password_verify($post_body['code'],$db_password_data['password'])){
    // if($password === $post_body['code']){
        $signer = new Sha256();
        $private_key = new Key('file://../');
        $token = (new Builder())->expiresAt(time()+3600)
                                ->withClaim('eid',$election_id)
                                ->getToken($signer, $private_key);

        // return token
        echo json_encode(["status" => "Success!",
                            "message" => "Logged in!",
                            "data" => ['token'=>$token]
                            ]);
    } else {
        // return validation error http response
        echo json_encode([  "status" => "Failure!",
                            "message" => "Incorrect password.",
                            "data" => ['db_password' => $password,
                                        'user_password' => $post_body['code']]
                            ]);
    }
} else 
if (intval($db_password_data['public_private']) === 0) {
    // Private with user ballot code logic
    $voter_id = select_scalar('SELECT vv.voter_id
                                FROM vote_voters vv
                                JOIN vote_election_voter_list vevl on vv.voter_id = vevl.voter_id
                                WHERE vv.voter_name = ?', [$post_body['code']]);

    if(!is_null($voter_id)){
        $token = (new Builder())->expiresAt(time()+3600)
                                ->withClaim('eid',$election_id)
                                ->withClaim('vid',$voter_id)
                                ->getToken();

        echo json_encode(["status" => "Success!",
                            "message" => "Logged in!",
                            "data" => $token
                            ]);
    } else {
        echo json_encode(["status" => "Failure!",
                            "message" => "No such user.",
                            "data" => []
                            ]);
    }
} else {
    echo json_encode(["status" => "Epic Failure!",
                        "message" => "Epic fail :'(",
                        "data" => [ 'db_password_data' => $password_data
                                    ]
                        ]);
}