<?php
/**
 * NotifyServer_Test file
 */

use INTERMediator\DB\DBClass;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\DB_Notification_Common;
use INTERMediator\NotifyServer;
use PHPUnit\Framework\TestCase;

class NotifyServer_Test extends TestCase
{
    private function createNotifyServer(?array $channels): NotifyServer
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->method('matchInRegistered')->willReturn($channels);
        $handler->method('appendIntoRegistered')->willReturn($channels);
        $handler->method('removeFromRegistered')->willReturn($channels);

        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;
        $dbClass->logger = Logger::getInstance();

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        return $notifyServer;
    }

    public static function noChannelsProvider(): array
    {
        return ['null' => [null], 'empty' => [[]]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testUpdatedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->updated('client-1', 'person', [1], ['name'], ['A'], false);
        $this->assertNoTriggerCall();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testCreatedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->created('client-1', 'person', [1], 'id', ['id' => 1], false);
        $this->assertNoTriggerCall();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testDeletedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->deleted('client-1', 'person', [1]);
        $this->assertNoTriggerCall();
    }

    public function testMethodsBeforeInitialize(): void
    {
        $notifyServer = new NotifyServer();
        $this->assertNull($notifyServer->register('person', '', [1]));
        $this->assertFalse($notifyServer->unregister('client-1', null));
        $notifyServer->updated('client-1', 'person', [1], ['name'], ['A'], false);
        $notifyServer->created('client-1', 'person', [1], 'id', ['id' => 1], false);
        $notifyServer->deleted('client-1', 'person', [1]);
        $this->assertStringNotContainsString('[NotifyServer] trigger',
            implode("\n", Logger::getInstance()->getDebugMessages()));
    }

    public function testMethodsAfterFailedInitialize(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(false);
        $handler->expects($this->never())->method('register');
        $handler->expects($this->never())->method('matchInRegistered');
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertFalse($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertNull($notifyServer->register('person', '', [1]));
        $notifyServer->updated('client-1', 'person', [1], ['name'], ['A'], false);
    }

    public function testInitializeWithoutHandler(): void
    {
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = null;
        $this->assertFalse((new NotifyServer())->initialize($dbClass, 'client-1'));
    }

    public function testRegisterAndUnregisterDelegateToHandler(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->once())->method('register')
            ->with('client-1', 'person', 'id=1', [1])->willReturn('reg-1');
        $handler->expects($this->once())->method('unregister')
            ->with('client-1', ['5'])->willReturn(true);
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertSame('reg-1', $notifyServer->register('person', 'id=1', [1]));
        $this->assertTrue($notifyServer->unregister('client-1', ['5']));
    }

    public static function invalidTableKeysProvider(): array
    {
        return ['nulls' => [[null, null]], 'empty strings' => [['', null]], 'empty array' => [[]]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTableKeysProvider')]
    public function testUnregisterWithoutValidIdsDoesNotRemoveAllRegistrations(array $tableKeys): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->never())->method('unregister');
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertTrue($notifyServer->unregister('client-1', $tableKeys));
    }

    public function testUnregisterFiltersInvalidIdsAndKeepsNullForAll(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->exactly(2))->method('unregister')
            ->willReturnCallback(function (?string $client, ?array $tableKeys): bool {
                static $call = 0;
                $call++;
                $this->assertSame('client-1', $client);
                $this->assertSame($call === 1 ? ['5', 7] : null, $tableKeys);
                return true;
            });
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertTrue($notifyServer->unregister('client-1', [null, '5', '', 7]));
        $this->assertTrue($notifyServer->unregister('client-1', null));
    }

    private function assertNoTriggerCall(): void
    {
        $messages = implode("\n", Logger::getInstance()->getDebugMessages());
        $this->assertStringContainsString('[NotifyServer] trigger / No channels to notify', $messages);
        $this->assertStringNotContainsString('[ServiceServerProxy] sync', $messages);
    }

    protected function setUp(): void
    {
        $logger = Logger::getInstance();
        $logger->clearLogs();
        $logger->setDebugMode(2);
    }
}
