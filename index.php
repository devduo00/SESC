<?php
    session_start();
    require_once 'autoload.php';

    use Facebook\FacebookSession;
    use Facebook\FacebookRequest;
    use Facebook\GraphUser;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequestException;

    echo "1";

    FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');
/*
    echo "2";
    // If you already have a valid access token:
    //$session = new FacebookSession('00e77536cc63860b0624bd1383dfe21d');

    echo "3";

    // If you're making app-level requests:
    $session = FacebookSession::newAppSession('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

    echo "4";
    echo "session";
    echo $session;

    // To validate the session:
    try {
        $session->validate();
    } catch (FacebookRequestException $ex) {
        // Session not valid, Graph API returned an exception with the reason.
        echo $ex->getMessage();
    } catch (\Exception $ex) {
        // Graph API returned info, but it may mismatch the current app or have expired.
        echo $ex->getMessage();
    }


    try {
        $response = (new FacebookRequest($session, 'GET', '/me'))->execute();
        $object = $response->getGraphObject();
        echo $object->getProperty('name');
    } catch (FacebookRequestException $ex) {
        echo $ex->getMessage();
    } catch (\Exception $ex) {
        echo $ex->getMessage();
    }

    // You can chain methods together and get a strongly typed GraphUser
    $me = (new FacebookRequest(
        $session, 'GET', '/me'
    ))->execute()->getGraphObject(GraphUser::className);
    echo $me->getName();
*/

    // login helper with redirect_uri
    $helper = new FacebookRedirectLoginHelper( 'http://180.70.94.239:8080/fb/SESC/' );

    echo "2";

    try {
        $session = $helper->getSessionFromRedirect();
    } catch( FacebookRequestException $ex ) {
        // When Facebook returns an error
    } catch( Exception $ex ) {
        // When validation fails or other local issues
    }

    echo "3";

    // see if we have a session
    if ( isset( $session ) ) {
        // graph api request for user data
        $request = new FacebookRequest( $session, 'GET', '/me' );
        $response = $request->execute();
        // get response
        $graphObject = $response->getGraphObject();

        // print data
        echo  print_r( $graphObject, 1 );
    } else {
        // show login url
        echo '<a href="' . $helper->getLoginUrl() . '">Login</a>';
    }

?>