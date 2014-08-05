Vkontakte API
=============

Фасад над [VkApiSdk](https://github.com/ailove-dev/vkPhpSdk).
Имеет на борту авторизацию для получения токена.
Полноценно следит за токеном, если используется объект авторизации.

Распространяется по лицензии [MIT](http://opensource.org/licenses/MIT) и предоставляется **AS-IS**.

Примеры
=======

Примеры использования можно посмотреть в ``` examples ```


```php
use VkApi\Auth;
use VkApi\Api;

$scope = array();
$token_path = __DIR__;

$auth = new Auth($login, $password, $token_path);
$api = new Api($client_id, $scope, $auth);

$result = $api->call('method', array('option1'=>'value1'));

```

Установка
=========

```json
{
    "require": {
        "mad-tools/vk-api": "dev-master"
    }
}
```