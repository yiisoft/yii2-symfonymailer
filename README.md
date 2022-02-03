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

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-symfonymailer
```

or add

```json
"yiisoft/yii2-symfonymailer": "~2.0.0"
```

to the require section of your composer.json.

> Note: Version 2.0 of this extension uses Symfonymailer, which requires PHP 8.

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
                //'port' => 465,
                //'options' => ['ssl' => true],
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
