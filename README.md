kronos-log
==========

[PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) Compliant implementation.

See [php-fig/log](https://github.com/php-fig/log) for more information on root classes and interfaces.

Usage
-----

```php

use Kronos\Log;

$logger = new Log\Logger();

$debug = new Log\Writer\File('/var/log/debug.log', new Kronos\Log\Adaptor\FileFactory());
$logger->addWriter($debug);

...

$logger->info('Need more {drink}', ['drink' => 'coffee']);
```

This is a simple usage of a logger with a writer which output into given files.

It's possible to use multiple writer accepting only some log levels.

```php

use Kronos\Log;

$logger = new Log\Logger();
$logger->addContext('user', $current_user);

$debug = new Log\Writer\File('/var/log/debug.log');
$debug->setMaxLevel(\Psr\Log\LogLevel::WARNING);
$logger->addWriter($debug);

$syslog = new Log\Writer\Syslog('application-name');
$syslog->setMinLevel(\Psr\Log\LogLevel::ERROR);
$logger->addWriter($syslog);

...

try {
    $logger->debug('Trying something with {value}', ['value' => $some_variable]);
    
    // Something
}
catch(\Exception $e) {
    $logger->error('Something did not work', ['exception' => $e]);
}
```

Here the info will be writen in `/var/log/debug.log` while the error will be sent to syslog.
