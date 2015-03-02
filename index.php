<?php
    //$session    = FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

    use Facebook\GraphUser;

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