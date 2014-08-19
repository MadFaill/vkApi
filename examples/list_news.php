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

$scope = array('offline', 'news', 'friends', 'wall', 'groups');

$token_path = __DIR__;

$auth = new Auth($login, $password, $token_path);
$api = new Api($client_id, $scope, $auth);
$api->setApiVersion('5.24');

try
{
//	$params = array('owner_id'=>$api->uid(), 'count'=>100);
//	$audios = $api->call('newsfeed.get'/*, $params*/);

	$params = array('group_ids'=>'1013891');

//	var_dump($params);

	$audios = $api->call('groups.getById', $params);

	var_dump($audios);
}
catch (\VkApi\Error\AuthFailed $e) {
	print "Error: ".$e->getMessage()."\r\n";
}
catch (\VkApi\Error\Exception $e) {
	print "Api Error: ".$e->getMessage()."\r\n";
}