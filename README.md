# phpfit/config-builder

Composer plugin to get configs from all phpfit packages and combine them all to
generate new config file on `BASEPATH/etc/{config.key}.php`. This module is for
internal system php fit framework usage only. Nothing to do here for app developer.

This module is executed for every after package install or update.

## Installation

```bash
composer require phpfit/config-builder
```

## Usage

Add an `extra` key on your package `composer.json` file to target the package
config file location:

```json
{
    "...": "...",
    "extra": {
        "phpfit": {
            "config": "etc/config.php"
        }
    },
    "...": "..."
}
```

Extra name should be `phpfit.config` to be identified.

## Config Strucutre

Add config file on `etc/config.php` and put package config as array. The top
array key will be used as config file name, and array value as config value after
combining them with exists config file.

```php

return [
    'db' => [
        'host' => 'localhost'
    ]
];
```

Above package config will create or modify exists app config named `etc/config/db.php`
and combine the value of exists app config with provided package config. The final
result of the config on `etc/config/db.php` will be as below:

```php
<?php

return [
    'host' => 'localhost'
];
```

## License

The phpfit/env library is licensed under the MIT license.
See [License File](LICENSE.md) for more information.
