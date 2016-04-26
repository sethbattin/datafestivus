#!/usr/bin/env bash
if [ ! -f phpunit.phar ]; then
    if [[ $PHP_VERSION -ge 50600 ]]; then
        wget https://phar.phpunit.de/phpunit.phar
    else
        wget -O phpunit.phar https://phar.phpunit.de/phpunit-old.phar
    fi
    chmod +x phpunit.phar
fi
phpunit.phar --bootstrap ../df_autoload.php .