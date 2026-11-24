<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/yii_logo_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/yii_logo_light.svg">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" alt="Yii Framework" height="100px">
    </picture>
    <h1 align="center">Yii2 Symfony Mailer Extension</h1>
    <br>
</p>

This extension provides a [Symfony Mailer](https://symfony.com/doc/current/mailer.html) mail solution for [Yii framework 2.0](https://www.yiiframework.com).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-symfonymailer.svg?style=for-the-badge&label=Stable&logo=packagist)](https://packagist.org/packages/yiisoft/yii2-symfonymailer)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-symfonymailer.svg?style=for-the-badge&label=Downloads)](https://packagist.org/packages/yiisoft/yii2-symfonymailer)
[![Build Status](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-symfonymailer/build.yml?style=for-the-badge&logo=github&label=Build)](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/build.yml)
[![codecov](https://img.shields.io/codecov/c/github/yiisoft/yii2-symfonymailer.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Codecov)](https://codecov.io/gh/yiisoft/yii2-symfonymailer)
[![Static Analysis](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-symfonymailer/static.yml?style=for-the-badge&label=Static)](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/static.yml)

Installation
------------

> [!IMPORTANT]
> - The minimum required [PHP](https://www.php.net/) version is PHP `8.1`.

The preferred way to install this extension is through [composer](https://getcomposer.org/download/):

```
php composer.phar require --prefer-dist yiisoft/yii2-symfonymailer
```

Usage
-----

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'transport' => [
                'scheme' => 'smtps',
                'host' => '',
                'username' => '',
                'password' => '',
                'port' => 465,
                'dsn' => 'native://default',
            ],
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
        ],
    ],
];
```

or

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'transport' => [
                'dsn' => 'smtp://user:pass@smtp.example.com:25',
            ],
        ],
    ],
];
```

You can then send an email as follows:

```php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

DI Container
------------

The `Mailer` component will automatically use the DI container when it is available.
This allows you to easily override the transport factory configurations or their dependencies.

Serializing messages
--------------------

A message can be passed to PHP's `serialize()` function, for example to store it or to hand it to a queue job, and sent after it is restored with `unserialize()`:

```php
$message = Yii::$app->mailer->compose('contact/html')
    ->setFrom('from@domain.com')
    ->setTo($form->email)
    ->setSubject($form->subject);

$payload = serialize($message);
// Later, possibly in another process.
$message = unserialize($payload);

$message->send();
```

The payload holds the Symfony email and the charset. The `mailer` property is not serialized, so a restored message is sent through the `mailer` application component unless you pass a mailer to `send()`.

> [!IMPORTANT]
> - Only unserialize payloads that come from a trusted source.
> - Files added with `attach()` or `embed()` are stored as a path and read when the message is sent, so the file must still exist at that path. Content added with `attachContent()` or `embedContent()` is stored in the payload.

### Extending the message class

A class that extends `Message` without adding state is serialized as is. A class that adds its own state must include it in the payload: override `__serialize()` and `__unserialize()`, and call the parent implementation in both.

```php
namespace app\mail;

use yii\symfonymailer\Message;

final class TenantMessage extends Message
{
    public string $tenant = '';

    public function __serialize(): array
    {
        return parent::__serialize() + ['tenant' => $this->tenant];
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->tenant = $data['tenant'];
    }
}
```

Then set the class in the mailer configuration:

```php
'mailer' => [
    'class' => \yii\symfonymailer\Mailer::class,
    'messageClass' => \app\mail\TenantMessage::class,
],
```

> [!NOTE]
> Earlier versions of this extension used `__sleep()`. Payloads written by those versions for the `Message` class can still be unserialized. A subclass that overrides `__sleep()` must move to `__serialize()` and `__unserialize()`, because PHP no longer calls `__sleep()` on it.

Migrating from yiisoft/yii2-swiftmailer
---------------------------------------

To migrate from the deprecated [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer) to this extension you need to update the application config.

Swiftmailer default transport was the `SendmailTransport`, while with this extension it will default to a `NullTransport` (sends no mail). You can use the swiftmailer default like the following:

```php
'mailer' => [
    'class' => yii\symfonymailer\Mailer::class,
    'transport' => [
        'dsn' => 'sendmail://default',
    ],
],
```

With this extension, you do not have an ability of directly setting timeout, or other configurations that were possible with Swiftmailer extension. The reason is, the underlying Symfony package defines its classes as `final` thereby discouraging inheritance and pushing towards composition.

To achieve timeout, for example (and other transport configurations supported), you will need to define factory class and override default transport factory you are interested to replace through [Yii DI container](https://www.yiiframework.com/doc/guide/2.0/en/concept-di-container) definitions.

Below is an example that shows you how to override timeout.

First define your custom factory class.

```php
namespace app\utils;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use yii\base\BaseObject;

final class CustomSmtpFactory extends BaseObject implements TransportFactoryInterface
{
    public float $timeout;

    public function create(Dsn $dsn): TransportInterface
    {
        $transport = $this->create($dsn);
        if ($transport instanceof SmtpTransport) {

            /** @var SocketStream $stream */
            $stream = $transport->getStream();
            $stream->setTimeout($this->timeout);
        }
        return $transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return $dsn->getScheme() == 'smtp';
    }
}
```

Then in the root of web configuration, set the factory class in container definitions, thereby overriding the default class.

```php
'container' => [
    'definitions' => [
        EsmtpTransportFactory::class => [
            'class' => \app\utils\CustomSmtpFactory::class,
            'timeout' => 143, //Configure it to your own timeout
        ],
        // ... other definitions
    ],
],
```

That is all you need to do. The extension should use your new class and its configuration settings.

Security implications of the DSN
--------------------------------

While the DSN might seem like a simple way to allow user configurable mailer settings it should be noted that the sendmail transport allows for execution of local executables.
If you need to have a user configurable DSN (which is easier to build and more powerful to use than creating a GUI) you should probably disable the sendmail transport.
Any user who has the power to configure a DSN essentially has shell access to wherever the code is running.

## Documentation

- [Testing](docs/testing.md)

## Security

[![Zizmor](https://img.shields.io/github/actions/workflow/status/yiisoft/yii2-symfonymailer/zizmor.yml?style=for-the-badge&label=Zizmor&logo=github)](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/zizmor.yml)
[![OpenSSF Scorecard](https://api.scorecard.dev/projects/github.com/yiisoft/yii2-symfonymailer/badge?style=for-the-badge)](https://scorecard.dev/viewer/?uri=github.com/yiisoft/yii2-symfonymailer)

GitHub Actions workflows are analyzed by [zizmor](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/zizmor.yml) for supply-chain vulnerabilities and workflow misconfigurations.
Supply-chain security is tracked via the [OpenSSF Scorecard](https://scorecard.dev/viewer/?uri=github.com/yiisoft/yii2-symfonymailer), which evaluates security practices across the repository.

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?style=for-the-badge&logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=for-the-badge&logo=yii)](https://www.yiiframework.com/)
[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=for-the-badge&logo=telegram)](https://t.me/yii_framework_in_english)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=for-the-badge&logo=slack)](https://yiiframework.com/go/slack)

## License

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE.md)
