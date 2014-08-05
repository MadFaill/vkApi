<?php
/**
 * Project: vkAuth
 * User: MadFaill
 * Date: 05.08.14
 * Time: 12:32
 * File: Api.php
 * Package: VkApi
 *
 */
namespace VkApi;

/**
 * Class        Api
 * @description Враппер для VkPhpSdk с получением токена
 *
 * @author      MadFaill
 * @copyright   MadFaill 19.02.14
 * @since       19.02.14
 * @version     0.01
 * @package     Cinema\lib\vk
 */
class Api
{
	private $client_id;
	private $scope;

	private $token = array(
		'token' => '',
		'uid' => 0
	);

	/**
	 * @var \VkPhpSdk
	 */
	private $api;

	/** @var  Auth */
	private $auth;

	/**
	 * @param $client_id   Идентификатор приложения
	 * @param array $scope Список доступов
	 * @param Auth $auth
	 */
	public function __construct($client_id, array $scope, Auth $auth=null)
	{
		$this->client_id = $client_id;
		$this->scope = $scope;

		$this->auth = $auth;
	}

	/**
	 * Апи вызов
	 * @param $method
	 * @param array $params
	 * @return array
	 */
	public function call($method, array $params=Null)
	{
		if (!$this->api) {
			$this->api = new \VkPhpSdk();
		}

		$token = $this->token();
		$this->api->setAccessToken($token['token']);
		$this->api->setUserId($token['uid']);

		return $this->api->api($method, $params);
	}

	/**
	 * Заполнение токена
	 *
	 * @param string $token_str
	 * @param int $user_id
	 */
	public function setToken($token_str, $user_id)
	{
		$this->token['token'] = $token_str;
		$this->token['uid'] = $user_id;
	}

	/**
	 * Получение token'a
	 *
	 * @return array
	 */
	public function token()
	{
		if ($this->auth) {
			return $this->auth->tokenForClientWithScope($this->client_id, $this->scope);
		}

		return $this->token;
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Api < #
// --------------------------------------------------------------------------------------------------------------------- 