Yii2 Streamlog
==============

[![Latest Stable Version](https://poser.pugx.org/codemix/yii2-streamlog/v/stable.svg)](https://packagist.org/packages/codemix/yii2-streamlog)
[![Total Downloads](https://poser.pugx.org/codemix/yii2-streamlog/downloads.svg)](https://packagist.org/packages/codemix/yii2-streamlog)
[![License](https://poser.pugx.org/codemix/yii2-streamlog/license.svg)](https://packagist.org/packages/codemix/yii2-streamlog)

A Yii 2 log target for streams in URL format.

This log target allows you to log to any of the URL like targets that are
[supported by PHP](http://php.net/manual/en/wrappers.php). Typical use cases
are docker containers that often log to `STDOUT` and `STDERR`, in which case
the target urls would be `php://stdout` and `php://stderr` respectively.


## Installation and Configuration

Install the package through [composer](http://getcomposer.org):

    composer require codemix/yii2-streamlog

And then add this to your application configuration:

```php
<?php
return [
    // ...
    'components' => [
        // ...
        'log' => [
            'targets' => [
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stdout',
                    'levels' => ['info','trace'],
                    'logVars' => [],
                ],
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
```
