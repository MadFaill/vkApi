<?php
/**
 * Project: vkAuth
 * User: MadFaill
 * Date: 05.08.14
 * Time: 12:53
 * File: list_videos.php
 *
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once realpath(__DIR__.'/../vendor/autoload.php');

use VkApi\Auth;
use VkApi\Api;

$data_str = file_get_contents(__DIR__.'/datafile.txt');
list ($login, $password, $client_id ) = explode(':::', $data_str);

$scope = array('offline', 'video');

$token_path = __DIR__;

$auth = new Auth($login, $password, $token_path);
$api = new Api($client_id, $scope, $auth);

$params = array('q'=>"Огонь и лед: Хроники драконов", 'count'=>200, 'sort'=>1);
$videos = $api->call('video.search', $params);

var_dump($videos);