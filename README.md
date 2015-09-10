Vkontakte API
=============

Имеет на борту авторизацию для получения токена.
Полноценно следит за токеном, если используется объект авторизации.

Распространяется по лицензии [MIT](http://opensource.org/licenses/MIT) и предоставляется **AS-IS**.

Как это работает
================

Для  того чтобы использовать библиотеку, понадобится:

- Создать [Stand-Alone](http://vk.com/editapp?act=create) приложение в самом контакте
- Вставить в переменные логин, пароль от контактика и + ид приложений
- запустить скрипт

Библиотека сама получает token. Для этого и требуется login && password.

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
