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
use VkApi\Error\Exception;

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
	private $api_version;

	private $token = array(
		'token' => '',
		'uid' => 0
	);

	/**
	 * @var Vk
	 */
	private $api;

	/** @var  Auth */
	private $auth;

	/**
	 * @param int $client_id Идентификатор приложения
	 * @param array $scope Список доступов
	 * @param Auth $auth
	 */
	public function __construct($client_id, array $scope, Auth $auth=null)
	{
		$this->client_id = $client_id;
		$this->scope = $scope;

		$this->auth = $auth;
	}

	public function setApiVersion($v)
	{
		$this->api_version = $v;
	}

	/**
	 * Апи вызов
	 *
	 * @param $method
	 * @param array $params
	 * @return array
	 * @throws Error\Exception
	 */
	public function call($method, array $params=Null)
	{
		if (!$this->api) {
			$this->api = new Vk();
		}

		if ($this->api_version) {
			$this->api->setApiVersion($this->api_version);
		}

		$token = $this->token();
		$this->api->setAccessToken($token['token']);

        $result = $this->api->api($method, $params);

        if (!isset($result['response'])) {
            throw new Exception('Unknown result format');
        }

        return $result['response'];
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

	/**
	 * Идентификатор текущего юзера
	 *
	 * @return int
	 */
	public function uid()
	{
		$token = $this->token();
		return $token['uid'];
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Api < #
// --------------------------------------------------------------------------------------------------------------------- 