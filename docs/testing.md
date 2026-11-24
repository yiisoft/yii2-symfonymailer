# Testing

## Checking dependencies

This package uses [composer-require-checker](https://github.com/maglnet/ComposerRequireChecker) to check if all 
dependencies are correctly defined in `composer.json`.

To run the checker, execute the following command:

```shell
composer run check-dependencies
```

## Easy coding standard

The code is checked with [Easy Coding Standard](https://github.com/easy-coding-standard/easy-coding-standard) and
[PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer). To run it:

```shell
composer run ecs
```

## Mutation testing

With [Infection](https://infection.github.io/) 0.32 (PHP 8.3 or later), run mutation testing with PHPStan as CI does:

```shell
composer run mutation -- --static-analysis-tool=phpstan
```

With Infection 0.27 on PHP 8.1, run mutation testing without static analysis:

```shell
composer run mutation
```

## Static analysis

The code is statically analyzed with [PHPStan](https://phpstan.org/). To run static analysis:

```shell
composer run static
```

## Unit tests

The code is tested with [PHPUnit](https://phpunit.de/). To run tests:

```
composer run test
```
