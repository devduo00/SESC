<?php

use fbsdk\src\Facebook\FacebookRequest;
use fbsdk\src\Facebook\GraphUser;

FacebookSession::setDefaultApplication('1381794498804715', 'dbcf7985ae7d57274665c75dcbe5b1d0');

class GraphUserTest extends PHPUnit_Framework_TestCase
{

    public function testMeReturnsGraphUser()
    {
        $response = (
        new FacebookRequest(
            FacebookTestHelper::$testSession,
            'GET',
            '/me'
        ))->execute()->getGraphObject(GraphUser::className());

        $info = FacebookTestHelper::$testSession->getSessionInfo();

        $this->assertTrue($response instanceof GraphUser);
        $this->assertEquals($info->getId(), $response->getId());
        $this->assertNotNull($response->getName());
        $this->assertNotNull($response->getLastName());
        $this->assertNotNull($response->getLink());
    }

}
