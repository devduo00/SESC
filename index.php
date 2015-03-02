<?php

require_once 'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\GraphSessionInfo;
use Facebook\FacebookCurl;
use Facebook\FacebookHttpable;
use Facebook\FacebookCurlHttpClient;

session_start();
FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

// login helper with redirect_uri
$helper     = new FacebookRedirectLoginHelper( 'http://180.70.94.239:8080/fb/SESC/' );
$session    = $helper->getSessionFromRedirect();

if ( isset( $session ) ) {
    // graph api request for user data
    $request    = new FacebookRequest( $session, 'GET', '/me' );
    $response   = $request->execute();

    // get response
    $graphObject= $response->getGraphObject();
    // print data
    echo  print_r( $graphObject, 1 );
    $graphObjMe	= $response->getGraphObject(GraphUser::className());
    $user       = $graphObjMe->getName();

    // graph api request for friendlists data
    $request2   = new FacebookRequest($session, 'GET', '/me/friends');
    $response2  = $request2->execute();
    // get response
    $user_friendList = $response2->getGraphObject();
    // print data
    echo  print_r( $user_friendList, 1 );

} else {
    // show login url
    //echo '<a href="' . $helper->getLoginUrl() . '">Login</a>';
}

?>

<!doctype html>
<html>
<head>
    <title>SESC</title>
    <style>
        body {
            font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
        }
        h1 a {
            text-decoration: none;
            color: #3b5998;
        }
        h1 a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<h1>Sample web app using facebook php SDK </h1>

<?php if ($user): ?>
    <a href="<?php echo $logoutUrl; ?>">Logout</a>
<?php else: ?>
    <div>
        Check the login status using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $statusUrl; ?>">Check the login status</a>
    </div>
    <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $helper->getLoginUrl(); ?>">Login with Facebook</a>
    </div>
<?php endif ?>

<h3>PHP Session</h3>
<pre><?php print_r($_SESSION); ?></pre>

<?php if ($user): ?>
    <h3> Welcome <?php  echo $user_profile['name']; ?> !!! </h3>
    <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

    <h3>Your friend list Object is as follows (/me/friends?token=<?php echo $access_token; ?>)</h3>
    <pre><?php print_r($user_friendList); ?></pre>
<?php else: ?>
    <strong><em>You are not Connected.</em></strong>
<?php endif ?>
</body>
</html>