<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Protocol;

use Laminas\Mail\Headers;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp;
use LaminasTest\Mail\TestAsset\SmtpProtocolSpy;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Mail
 * @covers Laminas\Mail\Protocol\Smtp<extended>
 */
class SmtpTest extends TestCase
{
    /** @var Smtp */
    public $transport;
    /** @var SmtpProtocolSpy */
    public $connection;

    public function setUp()
    {
        $this->transport  = new Smtp();
        $this->connection = new SmtpProtocolSpy();
        $this->transport->setConnection($this->connection);
    }

    public function testSendMinimalMail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody('testSendMailWithoutMinimalHeaders');
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
            . "RCPT TO:<api-tools-devteam@zend.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n"
            . "\r\n"
            . "testSendMailWithoutMinimalHeaders\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testSendEscapedEmail()
    {
        $headers = new Headers();
        $headers->addHeaderLine('Date', 'Sun, 10 Jun 2012 20:07:24 +0200');

        $message = new Message();
        $message->setHeaders($headers);
        $message->setSender('ralph.schindler@zend.com', 'Ralph Schindler');
        $message->setBody("This is a test\n.");
        $message->addTo('api-tools-devteam@zend.com', 'Laminas DevTeam');

        $expectedMessage = "EHLO localhost\r\n"
            . "MAIL FROM:<ralph.schindler@zend.com>\r\n"
            . "RCPT TO:<api-tools-devteam@zend.com>\r\n"
            . "DATA\r\n"
            . "Date: Sun, 10 Jun 2012 20:07:24 +0200\r\n"
            . "Sender: Ralph Schindler <ralph.schindler@zend.com>\r\n"
            . "To: Laminas DevTeam <api-tools-devteam@zend.com>\r\n"
            . "\r\n"
            . "This is a test\r\n"
            . "..\r\n"
            . ".\r\n";

        $this->transport->send($message);

        $this->assertEquals($expectedMessage, $this->connection->getLog());
    }

    public function testDisconnectCallsQuit()
    {
        $this->connection->disconnect();
        $this->assertTrue($this->connection->calledQuit);
    }

    public function testDisconnectResetsAuthFlag()
    {
        $this->connection->connect();
        $this->connection->setSessionStatus(true);
        $this->connection->setAuth(true);
        $this->assertTrue($this->connection->getAuth());
        $this->connection->disconnect();
        $this->assertFalse($this->connection->getAuth());
    }

    public function testConnectHasVerboseErrors()
    {
        $smtp = new TestAsset\ErroneousSmtp();

        $this->expectException('Laminas\Mail\Protocol\Exception\RuntimeException');
        $this->expectExceptionMessageRegExp('/nonexistentremote/');

        $smtp->connect('nonexistentremote');
    }

    public function testCanAvoidQuitRequest()
    {
        $this->assertTrue($this->connection->useCompleteQuit(), 'Default behaviour must be BC');

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertContains('QUIT', $this->connection->getLog());

        $this->connection->setUseCompleteQuit(false);
        $this->assertFalse($this->connection->useCompleteQuit());

        $this->connection->resetLog();
        $this->connection->connect();
        $this->connection->helo();
        $this->connection->disconnect();

        $this->assertNotContains('QUIT', $this->connection->getLog());

        $connection = new SmtpProtocolSpy([
            'use_complete_quit' => false,
        ]);
        $this->assertFalse($connection->useCompleteQuit());
    }
}
