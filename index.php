<?php
    session_start();
    require_once 'autoload.php';

    use Facebook\FacebookSession;
    use Facebook\FacebookRequest;
    use Facebook\GraphUser;
    use Facebook\FacebookRequestException;

    echo "1";

    FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

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

?>