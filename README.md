<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Mailer Library - Symfony Mailer Extension</h1>
    <br>
</p>

This extension provides a [Symfony Mailer](https://symfony.com/doc/5.4/mailer.html) mail solution for [Yii framework 2.0](http://www.yiiframework.com).

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-symfonymailer/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-symfonymailer)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-symfonymailer/downloads.png)](https://packagist.org/packages/yiisoft/yii2-symfonymailer)
[![Build Status](https://github.com/yiisoft/yii2-symfonymailer/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-symfonymailer/actions)
[![codecov](https://codecov.io/gh/yiisoft/yii2-symfonymailer/graph/badge.svg?token=XCj60xP699)](https://codecov.io/gh/yiisoft/yii2-symfonymailer)
[![static analysis](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/static.yml/badge.svg)](https://github.com/yiisoft/yii2-symfonymailer/actions/workflows/static.yml)
[![type-coverage](https://shepherd.dev/github/yiisoft/yii2-symfonymailer/coverage.svg)](https://shepherd.dev/github/yiisoft/yii2-symfonymailer)

Requirements
------------

- PHP 8.1 or higher.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-symfonymailer
```

or add

```json
"yiisoft/yii2-symfonymailer": "~3.0.0"
```

to the require section of your composer.json.

Usage
-----

To use this extension,  simply add the following code in your application configuration:

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
With this extension, you do not have an ability of directly setting timeout that was possible with Swiftmailer extension. The reason is, the underlying Symfony package defines its classes as `final` thereby discouraging inheritance and pushing towards composition. To achieve timeout (and other transport configurations), you will need to define factory class. Below is an example for SMTP transport.

```php
namespace app\utils; //file is in utils folder of your application

use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class CustomSmtpFactory implements TransportFactoryInterface {
    public function __construct(private TransportFactoryInterface $factory, private float $timeout)
    {
        $this->timeout = 120;
    }

    public function create(Dsn $dsn): TransportInterface
    {
        $result = $this->factory->create($dsn);
        if ($result instanceof SmtpTransport) {
            //Setup timeout to this or 
            $result->getStream()->setTimeout($this->timeout);
        }
        return $result;
    }

    public function supports(Dsn $dsn): bool {
        return $this->factory->supports($dsn);
    }
}
```

then in configuration, set the factory

```php
   'mailer' => [
       'class' => yii\symfonymailer\Mailer::class,
       'transportFactory' => app\utils\CustomSmtpFactory::class,
        'transport' => [
            'scheme' => 'smtp',
            //other settings
        ],
   ],
  ```

Security implications of the DSN
--------------------------------

While the DSN might seem like a simple way to allow user configurable mailer settings it should be noted that the sendmail transport allows for execution of local executables.
If you need to have a user configurable DSN (which is easier to build and more powerful to use than creating a GUI) you should probably disable the sendmail transport.
Any user who has the power to configure a DSN essentially has shell access to wherever the code is running.

## Testing

[Check the documentation testing](/docs/testing.md) to learn about testing.
