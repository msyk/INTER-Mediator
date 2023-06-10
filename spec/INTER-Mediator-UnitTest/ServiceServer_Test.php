<?php

namespace INTERMediator;

use PHPUnit\Framework\TestCase;

class ServiceServer_Test extends TestCase
{

    public function test_instanciate()
    {
        $ssProxy = ServiceServerProxy::instance();
        $this->assertNotNull($ssProxy, "The ServiceServerProxy instance has to get.");

        $checkResult = $ssProxy->checkServiceServer();
        $this->assertFalse($checkResult, "Usually the service server is offline.");

        $messages = $ssProxy->getMessages();
        $this->assertTrue(count($messages) > 0, "Some massages have to stored.");
    }

}