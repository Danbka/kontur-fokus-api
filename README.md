Контур.Фокус: API 3.0
===
Super-simple, minimum abstraction Контур.Фокус API 3.0 wrapper, in PHP.

Examples
--------
Start by `use`-ing the class and creating an instance with your API key `$apiKey`

```php
use Danbka\KonturFokusApi\KFApiClient;

$kfApi = new KFApiClient($apiKey, $settings);
```

`$setting` is the array like this:
```php
$settings = [
    'format' => 'json|array|xml' // output format, default array
]
```

Let's go:

```php
$result = $kfApi->loadResource('req', ['ogrn' => '0000000000000']);
```

```php
$result = $kfApi->loadResource('excerpt', ['inn' => '0000000000']);
```

```php
$result = $kfApi->loadResource('stat');
```

Available resources you can find [here](https://focus-api.kontur.ru/api3/req/userform)