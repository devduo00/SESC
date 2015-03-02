<?php
    use Facebook\FacebookSession;
    use Facebook\GraphUser;

    FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

    // If you already have a valid access token:
    $session = new FacebookSession('00e77536cc63860b0624bd1383dfe21d');

    // If you're making app-level requests:
    $session = FacebookSession::newAppSession();

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


    // Get the base class GraphObject from the response
    $object = $response->getGraphObject();

    // Get the response typed as a GraphUser
    $user = $response->getGraphObject(GraphUser::className());
    // or convert the base object previously accessed
    // $user = $object->cast(GraphUser::className());

    // Get the response typed as a GraphLocation
    $loc = $response->getGraphObject(GraphLocation::className());
    // or convert the base object previously accessed
    // $loc = $object->cast(GraphLocation::className());

    // User example
    echo $object->getProperty('name');
    echo $user->getName();

    // Location example
    echo $object->getProperty('country');
    echo $loc->getCountry();

    // SessionInfo example
    //$info = $session->getSessionInfo());
    //echo $info->getxpiresAt();

?>