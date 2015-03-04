<?php
/*
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
        //echo  print_r( $graphObject, 1 );

        $graphObjMe	= $response->getGraphObject(GraphUser::className());
        $userName   = $graphObjMe->getName();
        $userID     = $graphObjMe->getID();

        //echo  print_r( $userName, 1 );

        // graph api request for friendlists data
        $request2   = new FacebookRequest($session, 'GET', '/me/friends');
        $response2  = $request2->execute();
        // get response
        $user_friendList = $response2->getGraphObject(GraphUser::className());
        // print data
        echo  count($user_friendList).":".print_r( $user_friendList, 1 )."<br>";

        // graph api request for friendlists data
        $request3   = new FacebookRequest($session, 'GET', '/me/likes');
        $response3  = $request3->execute();
        // get response
        $user_likes = $response3->getGraphObject(GraphUser::className());
        // print data
        echo  count($user_likes).":".print_r( $user_likes, 1 );
        echo  $user_likes["data"];

    } else {
        // show login url
        //echo '<a href="' . $helper->getLoginUrl() . '">Login</a>';
    }
*/
?>

<!DOCTYPE html>
<html>
<head>
    <title>Facebook Login JavaScript Example</title>
    <meta charset="UTF-8">
</head>
<body>
<script>
    // This is called with the results from from FB.getLoginStatus().
    function statusChangeCallback(response) {
        console.log('statusChangeCallback');
        console.log(response);
        // The response object is returned with a status field that lets the
        // app know the current login status of the person.
        // Full docs on the response object can be found in the documentation
        // for FB.getLoginStatus().
        if (response.status === 'connected') {
            // Logged into your app and Facebook.
            testAPI();
        } else if (response.status === 'not_authorized') {
            // The person is logged into Facebook, but not your app.
            document.getElementById('status').innerHTML = 'Please log ' +
            'into this app.';
        } else {
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not.
            document.getElementById('status').innerHTML = 'Please log ' +
            'into Facebook.';
        }
    }

    // This function is called when someone finishes with the Login
    // Button.  See the onlogin handler attached to it in the sample
    // code below.
    function checkLoginState() {
        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    }

    window.fbAsyncInit = function() {
        FB.init({
            appId      : '1553401168251528',
            xfbml      : true,
            version    : 'v2.2'
        });
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));


    // Here we run a very simple test of the Graph API after login is
    // successful.  See statusChangeCallback() for when this call is made.
    function testAPI() {
        console.log('Welcome!  Fetching your information.... ');
        FB.api('/me', function(response) {
            if (response && !response.error) {
                document.getElementById('status').innerHTML = 'Thanks for logging in, ' + response.name + '!';
            }
        });

        FB.api('/me/friends', function(response) {
            if (response && !response.error) {
                console.log(response);
                document.getElementById('friends').innerHTML= response;
            }
        });

        FB.api('/me/likes', function(response) {
            if (response && !response.error) {
                console.log(response);
                document.getElementById('likes').innerHTML  = response;
            }
        });

        FB.api('/me/invitable_friends', function(response) {
            if (response && !response.error) {
                console.log(response);
                document.getElementById('invitable_friends').innerHTML  = response;
            }
        });

    }
</script>

<!--
  Below we include the Login Button social plugin. This button uses
  the JavaScript SDK to present a graphical Login button that triggers
  the FB.login() function when clicked.
-->

<fb:login-button scope="public_profile,email,user_friends,user_likes" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>

<div id="friends_t">
    user_friends
</div>
<div id="friends">
</div>

<div id="invitable_friends_t">
    user_friends
</div>
<div id="invitable_friends">
</div>

<div id="friends_t">
    user_friends
</div>
<div id="friends">
</div>

x
</body>
</html>