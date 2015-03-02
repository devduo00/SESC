use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

if($session) {

try {

$user_profile = (new FacebookRequest(
$session, 'GET', '/me'
))->execute()->getGraphObject(GraphUser::className());

echo "Name: " . $user_profile->getName();

} catch(FacebookRequestException $e) {

echo "Exception occured, code: " . $e->getCode();
echo " with message: " . $e->getMessage();

}

}