<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\extensions\symfonymailer;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use UnexpectedValueException;
use Yii;
use yii\helpers\FileHelper;
use yii\symfonymailer\Mailer;
use yii\symfonymailer\Message;
use yiiunit\extensions\symfonymailer\support\SubclassedMessage;
use yiiunit\extensions\symfonymailer\support\TenantAwareMessage;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
Yii::setAlias('@yii/symfonymailer', __DIR__ . '/../../../../extensions/symfonymailer');

/**
 * @covers \yii\symfonymailer\Message
 */
final class MessageTest extends TestCase
{
    /**
     * @var string test email address, which will be used as receiver for the messages.
     */
    private $testEmailReceiver = 'someuser@somedomain.com';

    public function setUp(): void
    {
        $this->mockApplication([
            'components' => [
                'mailer' => $this->createTestEmailComponent(),
            ],
        ]);
        $filePath = $this->getTestFilePath();
        if (!file_exists($filePath)) {
            FileHelper::createDirectory($filePath);
        }
    }

    public function tearDown(): void
    {
        $filePath = $this->getTestFilePath();
        if (file_exists($filePath)) {
            FileHelper::removeDirectory($filePath);
        }
    }

    /**
     * @return string test file path.
     */
    private function getTestFilePath(): string
    {
        return Yii::getAlias('@yiiunit/extensions/symfonymailer/runtime') . DIRECTORY_SEPARATOR . basename(self::class) . '_' . getmypid();
    }

    /**
     * @return Mailer test email component instance.
     */
    private function createTestEmailComponent(): Mailer
    {
        return new Mailer([
            'useFileTransport' => true,
        ]);
    }

    /**
     * @return Message test message instance.
     */
    private function createTestMessage(): Message
    {
        return Yii::$app->get('mailer')->compose();
    }

    /**
     * Creates image file with given text.
     * @param  string $fileName file name.
     * @param  string $text     text to be applied on image.
     * @return string image file full name.
     */
    private function createImageFile($fileName = 'test.jpg', $text = 'Test Image'): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD lib required.');
        }
        $fileFullName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . $fileName;
        $image = imagecreatetruecolor(120, 20);
        $textColor = imagecolorallocate($image, 233, 14, 91);
        imagestring($image, 1, 5, 5, $text, $textColor);
        imagejpeg($image, $fileFullName);

        return $fileFullName;
    }


    // Tests :

    public function testSetGet(): void
    {
        $message = new Message();

        $charset = 'utf-16';
        $message->setCharset($charset);
        $this->assertEquals($charset, $message->getCharset(), 'Unable to set charset!');

        $subject = 'Test Subject';
        $message->setSubject($subject);
        $this->assertEquals($subject, $message->getSubject(), 'Unable to set subject!');

        $from = 'from@somedomain.com';
        $message->setFrom($from);
        $this->assertContains($from, array_keys($message->getFrom()), 'Unable to set from!');

        $replyTo = 'reply-to@somedomain.com';
        $message->setReplyTo($replyTo);
        $this->assertContains($replyTo, array_keys($message->getReplyTo()), 'Unable to set replyTo!');

        $to = 'someuser@somedomain.com';
        $message->setTo($to);
        $this->assertContains($to, array_keys($message->getTo()), 'Unable to set to!');

        $cc = 'ccuser@somedomain.com';
        $message->setCc($cc);
        $this->assertContains($cc, array_keys($message->getCc()), 'Unable to set cc!');

        $bcc = 'bccuser@somedomain.com';
        $message->setBcc($bcc);
        $this->assertContains($bcc, array_keys($message->getBcc()), 'Unable to set bcc!');
    }

    /**
     * @depends testSetGet
     */
    public function testClone(): void
    {
        $m1 = new Message();
        $m1->setFrom('user@example.com');
        $m2 = clone $m1;
        $m1->setTo([
            'user1@example.com' => 'user1',
        ]);
        $m2->setTo([
            'user2@example.com' => 'user2',
        ]);

        $this->assertEquals([
            'user1@example.com' => 'user1',
        ], $m1->getTo());
        $this->assertEquals([
            'user2@example.com' => 'user2',
        ], $m2->getTo());

        $messageWithoutSymfonyInitialized = new Message();
        $m2 = clone $messageWithoutSymfonyInitialized; // should be no error during cloning
        $this->assertInstanceOf(Message::class, $m2);
    }

    public function testSetupHeaderShortcuts(): void
    {
        $charset = 'utf-16';
        $subject = 'Test Subject';
        $from = 'from@somedomain.com';
        $replyTo = 'reply-to@somedomain.com';
        $to = 'someuser@somedomain.com';
        $cc = 'ccuser@somedomain.com';
        $bcc = 'bccuser@somedomain.com';
        $returnPath = 'bounce@somedomain.com';
        $textBody = 'textBody';

        $messageString = $this->createTestMessage()
            ->setCharset($charset)
            ->setSubject($subject)
            ->setFrom($from)
            ->setReplyTo($replyTo)
            ->setTo($to)
            ->setCc($cc)
            ->setReturnPath($returnPath)
            ->setPriority(2)
            ->setTextBody($textBody)
            ->toString();

        $this->assertStringContainsString("charset=$charset", $messageString, 'Incorrect charset!');
        $this->assertStringContainsString("Subject: $subject", $messageString, 'Incorrect "Subject" header!');
        $this->assertStringContainsString("From: $from", $messageString, 'Incorrect "From" header!');
        $this->assertStringContainsString("Reply-To: $replyTo", $messageString, 'Incorrect "Reply-To" header!');
        $this->assertStringContainsString("To: $to", $messageString, 'Incorrect "To" header!');
        $this->assertStringContainsString("Cc: $cc", $messageString, 'Incorrect "Cc" header!');
        $this->assertStringContainsString("Return-Path: <{$returnPath}>", $messageString, 'Incorrect "Return-Path" header!');
        $this->assertStringContainsString('X-Priority: 2 (High)', $messageString, 'Incorrect "Priority" header!');
    }

    public function testSend(): void
    {
        $message = $this->createTestMessage();
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Test');
        $message->setTextBody('Yii Symfony Test body');
        $this->assertTrue($message->send());
    }

    /**
     * @depends testSend
     */
    public function testAttachFile(): void
    {
        $message = $this->createTestMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Attach File Test');
        $message->setTextBody('Yii Symfony Attach File Test body');
        $fileName = __FILE__;
        $message->attach($fileName);

        $this->assertTrue($message->send());

        $attachment = $this->getAttachment($message);
        $this->assertIsObject($attachment, 'No attachment found!');
        $this->assertStringContainsString("attachment filename: $fileName", $attachment->asDebugString(), 'Invalid file name!');
    }

    /**
     * @depends testSend
     */
    public function testAttachContent(): void
    {
        $message = $this->createTestMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Create Attachment Test');
        $message->setTextBody('Yii Symfony Create Attachment Test body');
        $fileName = 'test.txt';
        $fileContent = 'Test attachment content';
        $message->attachContent($fileContent, [
            'fileName' => $fileName,
            'contentType' => 'image/png',
        ]);

        $this->assertTrue($message->send());
        $attachment = $this->getAttachment($message);
        $this->assertIsObject($attachment, 'No attachment found!');
        $this->assertStringContainsString("attachment filename: $fileName", $attachment->asDebugString(), 'Invalid file name!');
        $this->assertStringContainsString('image/png', $attachment->asDebugString(), 'Invalid content type!');
    }

    /**
     * @depends testSend
     */
    public function testEmbedFile(): void
    {
        $fileName = $this->createImageFile('embed_file.jpg', 'Embed Image File');

        $message = $this->createTestMessage();

        $cid = $message->embed($fileName);
        $this->assertIsString($cid);
        $this->assertStringStartsWith('cid:', $cid);
        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Embed File Test');
        $message->setHtmlBody('Embed image: <img src="' . $cid . '" alt="pic">');

        $this->assertTrue($message->send());

        $attachment = $this->getAttachment($message);
        $this->assertIsObject($attachment, 'No attachment found!');
        $this->assertStringContainsString(" filename: $fileName", $attachment->asDebugString(), 'Invalid file name!');
        $this->assertStringContainsString(' disposition: inline', $attachment->asDebugString(), 'Invalid disposition!');
    }

    /**
     * @depends testSend
     */
    public function testEmbedContent(): void
    {
        $fileFullName = $this->createImageFile('embed_file.jpg', 'Embed Image File');
        $message = $this->createTestMessage();

        $fileName = basename($fileFullName);
        $contentType = 'image/jpeg';
        $fileContent = file_get_contents($fileFullName);

        $cid = $message->embedContent($fileContent, [
            'fileName' => $fileName,
            'contentType' => $contentType,
        ]);
        $this->assertIsString($cid);
        $this->assertStringStartsWith('cid:', $cid);

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Embed File Test');
        $message->setHtmlBody('Embed image: <img src="' . $cid . '" alt="pic">');

        $this->assertTrue($message->send());

        $attachment = $this->getAttachment($message);
        $this->assertIsObject($attachment, 'No attachment found!');
        $this->assertStringContainsString(" filename: $fileName", $attachment->asDebugString(), 'Invalid file name!');
        $this->assertStringContainsString($contentType, $attachment->asDebugString(), 'Invalid content type!');
        $this->assertStringContainsString(' disposition: inline', $attachment->asDebugString(), 'Invalid disposition!');
    }

    /**
     * @depends testSend
     */
    public function testSendAlternativeBody(): void
    {
        $message = $this->createTestMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Swift Alternative Body Test');
        $message->setHtmlBody('<b>Yii Swift</b> test HTML body');
        $message->setTextBody('Yii Swift test plain text body');

        $this->assertTrue($message->send(), 'Don`t send!');

        $body = $message->getSymfonyEmail()->getBody();

        $this->assertStringContainsString('text/plain', $body->asDebugString(), 'No text!');
        $this->assertStringContainsString('text/html', $body->asDebugString(), 'No HTML!');
    }

    public function testSerialize(): void
    {
        $message = $this->createTestMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Alternative Body Test');
        $message->setTextBody('Yii Symfony test plain text body');

        $serializedMessage = serialize($message);
        $this->assertNotEmpty($serializedMessage, 'Unable to serialize message!');

        $unserializedMessage = unserialize($serializedMessage);
        $this->assertEquals($message->getSymfonyEmail(), $unserializedMessage->getSymfonyEmail(), 'Unable to unserialize message!');
    }

    public function testThatSerializeDoesNotSerializeMailer(): void
    {
        $message = $this->createTestMessage();
        $message->mailer = 'testvalue';

        $unserializedMessage = unserialize(serialize($message));
        $this->assertNull($unserializedMessage->mailer);
    }

    public function testSendUnserializedMessageThroughApplicationMailer(): void
    {
        $message = $this->createTestMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Restored Message Test');
        $message->setTextBody('Yii Symfony restored message plain text body');

        $unserializedMessage = unserialize(serialize($message));

        $this->assertNull(
            $unserializedMessage->mailer,
            'Restored message must have no mailer assigned.',
        );
        $this->assertTrue(
            $unserializedMessage->send(),
            'Application mailer must send the message.',
        );
    }

    public function testSerializePayloadContainsOnlyEmailAndCharset(): void
    {
        $message = $this->createTestMessage();

        $message->setCharset('iso-8859-1');

        $payload = $message->__serialize();

        $this->assertSame(
            ['email', 'charset'],
            array_keys($payload),
            'Payload keys must be exactly email and charset.',
        );
        $this->assertSame(
            $message->getSymfonyEmail(),
            $payload['email'],
            'Payload must hold the Symfony email.',
        );
        $this->assertSame(
            'iso-8859-1',
            $payload['charset'],
            'Payload must hold the charset.',
        );
    }

    public function testSerializeSubclassWithoutOverrides(): void
    {
        $message = new SubclassedMessage();

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Subclass Test');
        $message->setTextBody('Yii Symfony subclass plain text body');

        $warnings = [];

        set_error_handler(
            static function (int $errno, string $errstr) use (&$warnings): bool {
                $warnings[] = $errstr;

                return true;
            },
        );

        try {
            $serializedMessage = serialize($message);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $warnings, 'No PHP warning must be emitted.');

        $unserializedMessage = unserialize($serializedMessage);

        $this->assertInstanceOf(
            SubclassedMessage::class,
            $unserializedMessage,
            'Subclass type must be preserved.',
        );
        $this->assertEquals(
            $message->getSymfonyEmail(),
            $unserializedMessage->getSymfonyEmail(),
            'Symfony email must be restored.',
        );
        $this->assertSame(
            'Yii Symfony Subclass Test',
            $unserializedMessage->getSubject(),
            'Subject must be preserved.',
        );
        $this->assertSame(
            'utf-8',
            $unserializedMessage->getCharset(),
            'Charset must be preserved.',
        );
    }

    public function testSerializeSubclassExtendingSerializationHooks(): void
    {
        $message = new TenantAwareMessage();

        $message->tenant = 'acme';

        $message->setTo($this->testEmailReceiver);
        $message->setFrom('someuser@somedomain.com');
        $message->setSubject('Yii Symfony Tenant Test');
        $message->setTextBody('Yii Symfony tenant plain text body');

        $unserializedMessage = unserialize(serialize($message));

        $this->assertInstanceOf(
            TenantAwareMessage::class,
            $unserializedMessage,
            'Subclass type must be preserved.',
        );
        $this->assertSame(
            'acme',
            $unserializedMessage->tenant,
            'Subclass state must be restored.',
        );
        $this->assertEquals(
            $message->getSymfonyEmail(),
            $unserializedMessage->getSymfonyEmail(),
            'Symfony email must be restored.',
        );
        $this->assertSame(
            'utf-8',
            $unserializedMessage->getCharset(),
            'Charset must be preserved.',
        );
    }

    public function testSerializePreservesNonDefaultCharset(): void
    {
        $message = $this->createTestMessage();

        $message->setCharset('iso-8859-1');
        $message->setTextBody('Yii Symfony charset plain text body');

        $unserializedMessage = unserialize(serialize($message));

        $this->assertSame(
            'iso-8859-1',
            $unserializedMessage->getCharset(),
            "Charset must remain 'iso-8859-1'.",
        );
    }

    public function testUnserializeLegacySleepPayload(): void
    {
        $email = (new Email())
            ->to($this->testEmailReceiver)
            ->from('someuser@somedomain.com')
            ->subject('Yii Symfony Legacy Test')
            ->text('Yii Symfony legacy plain text body', 'iso-8859-1');

        $unserializedMessage = unserialize($this->createLegacySerializedMessage($email, 'iso-8859-1'));

        $this->assertInstanceOf(
            Message::class,
            $unserializedMessage,
            'Payload must restore a message.',
        );
        $this->assertEquals(
            $email,
            $unserializedMessage->getSymfonyEmail(),
            'Symfony email must be restored.',
        );
        $this->assertSame(
            'Yii Symfony Legacy Test',
            $unserializedMessage->getSubject(),
            'Subject must be restored.',
        );
        $this->assertSame(
            'iso-8859-1',
            $unserializedMessage->getCharset(),
            'Charset must be restored.',
        );
        $this->assertStringContainsString(
            'Subject: Yii Symfony Legacy Test',
            $unserializedMessage->toString(),
            'Restored message must be renderable.',
        );
    }

    public function testUnserializePrefersCurrentKeysOverLegacyKeys(): void
    {
        $legacyPrefix = "\0" . Message::class . "\0";

        $email = (new Email())->subject('Yii Symfony Current Test');
        $legacyEmail = (new Email())->subject('Yii Symfony Legacy Test');

        $message = new Message();

        $message->__unserialize(
            [
                'email' => $email,
                'charset' => 'iso-8859-1',
                "{$legacyPrefix}email" => $legacyEmail,
                "{$legacyPrefix}charset" => 'windows-1251',
            ],
        );

        $this->assertSame(
            $email,
            $message->getSymfonyEmail(),
            'Current email key must take precedence.',
        );
        $this->assertSame(
            'iso-8859-1',
            $message->getCharset(),
            'Current charset key must take precedence.',
        );
    }

    /**
     * @dataProvider invalidSerializedPayloadProvider
     *
     * @param array<string, mixed> $data
     */
    public function testThrowUnexpectedValueExceptionForInvalidPayload(array $data, string $expectedMessage): void
    {
        $message = new Message();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage($expectedMessage);

        $message->__unserialize($data);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSerializedPayloadProvider(): array
    {
        $missingEmail = 'Unable to unserialize '
            . Message::class
            . ': the payload does not contain a Symfony email instance.';
        $missingCharset = 'Unable to unserialize '
            . Message::class
            . ": the payload does not contain a 'string' charset.";

        return [
            'missing email' => [
                ['charset' => 'utf-8'],
                $missingEmail,
            ],
            'email of the wrong type' => [
                ['email' => 'someuser@somedomain.com', 'charset' => 'utf-8'],
                $missingEmail,
            ],
            'missing charset' => [
                ['email' => new Email()],
                $missingCharset,
            ],
            'charset of the wrong type' => [
                ['email' => new Email(), 'charset' => 1],
                $missingCharset,
            ],
        ];
    }

    /**
     * Builds a serialized message in the format written by the former `__sleep()` implementation.
     *
     * @param Email $email Symfony email stored under the mangled private property name.
     * @param string $charset charset stored under the mangled private property name.
     *
     * @return string serialized message payload.
     */
    private function createLegacySerializedMessage(Email $email, string $charset): string
    {
        $class = Message::class;
        $emailKey = "\0" . $class . "\0email";
        $charsetKey = "\0" . $class . "\0charset";

        return sprintf(
            'O:%d:"%s":2:{s:%d:"%s";%ss:%d:"%s";%s}',
            strlen($class),
            $class,
            strlen($emailKey),
            $emailKey,
            serialize($email),
            strlen($charsetKey),
            $charsetKey,
            serialize($charset),
        );
    }

    /**
     * @depends testSendAlternativeBody
     */
    public function testAlternativeBodyCharset(): void
    {
        $message = $this->createTestMessage();
        $charset = 'windows-1251';
        $message->setCharset($charset);

        $message->setTextBody('some text');
        $message->setHtmlBody('some html');
        $message->setTo('to@to.to');
        $message->setFrom('from@to.to');
        $content = $message->toString();
        $this->assertEquals(2, substr_count($content, $charset), 'Wrong charset for alternative body.');

        $message->setTextBody('some text override');
        $content = $message->toString();
        $this->assertEquals(2, substr_count($content, $charset), 'Wrong charset for alternative body override.');
    }

    public function testSetupHeaders(): void
    {
        $messageString = $this->createTestMessage()
            ->setTo('to@to.to')
            ->setFrom('from@to.to')
            ->addHeader('Some', 'foo')
            ->addHeader('Multiple', 'value1')
            ->addHeader('Multiple', 'value2')
            ->setTextBody('Body')
            ->toString();

        $this->assertStringContainsString('Some: foo', $messageString, 'Unable to add header!');
        $this->assertStringContainsString('Multiple: value1', $messageString, 'First value of multiple header lost!');
        $this->assertStringContainsString('Multiple: value2', $messageString, 'Second value of multiple header lost!');

        $messageString = $this->createTestMessage()
            ->setTo('to@to.to')
            ->setFrom('from@to.to')
            ->setHeader('Some', 'foo')
            ->setHeader('Some', 'override')
            ->setHeader('Multiple', ['value1', 'value2'])
            ->setTextBody('Body')
            ->toString();

        $this->assertStringContainsString('Some: override', $messageString, 'Unable to set header!');
        $this->assertStringNotContainsString('Some: foo', $messageString, 'Unable to override header!');
        $this->assertStringContainsString('Multiple: value1', $messageString, 'First value of multiple header lost!');
        $this->assertStringContainsString('Multiple: value2', $messageString, 'Second value of multiple header lost!');

        $message = $this->createTestMessage();
        $message->setTextBody('Body');
        $message->setTo('to@to.to');
        $message->setFrom('from@to.to');
        $message->setHeader('Some', 'foo');
        $this->assertEquals(['foo'], $message->getHeader('Some'));
        $message->setHeader('Multiple', ['value1', 'value2']);
        $this->assertEquals(['value1', 'value2'], $message->getHeader('Multiple'));

        $message = $this->createTestMessage()
            ->setHeaders([
                'Some' => 'foo',
                'Multiple' => ['value1', 'value2'],
            ]);
        $this->assertEquals(['foo'], $message->getHeader('Some'));
        $this->assertEquals(['value1', 'value2'], $message->getHeader('Multiple'));
    }

    /**
     * Finds the attachment object in the message.
     * @param  Message                     $message message instance
     * @return DataPart|null attachment instance.
     */
    protected function getAttachment(Message $message): ?DataPart
    {
        $messageParts = $message->getSymfonyEmail()->getAttachments();
        $attachment = null;
        foreach ($messageParts as $part) {
            if ($part instanceof DataPart) {
                $attachment = $part;
                break;
            }
        }

        return $attachment;
    }
}
