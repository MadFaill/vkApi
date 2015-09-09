<?php
/**
 * Project: vkAuth
 * User: MadFaill
 * Date: 05.08.14
 * Time: 12:35
 * File: Auth.php
 * Package: VkApi
 *
 */
namespace VkApi;
use VkApi\Error\AuthFailed;

/**
 * Class        Auth
 * @description None.
 *
 * @author      MadFaill
 * @copyright   MadFaill 05.08.14
 * @since       05.08.14
 * @version     0.01
 * @package     VkApi
 */
class Auth 
{
	private $login;
	private $password;
	private $store_path;

	private $token;

	/**
	 * @var \Requests_Session
	 */
	private $session;

	/**
	 * @param $login            Логин в социальной сети
	 * @param $password         Пароль в социальной сети
	 * @param null $store_path  Путь в каталог с кешем токена
	 */
	public function __construct($login, $password, $store_path=Null)
	{
		$this->login = $login;
		$this->password = $password;

		$this->session = new \Requests_Session('https://login.vk.com/');

		if ($store_path) {
			$this->set_store_path($store_path);
		}

		$this->session->headers = array(
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding' => 'gzip,deflate,sdch',
			'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4,mk;q=0.2',
			'Cache-Control' => 'max-age=0',
			'Connection' => 'keep-alive',
			'Content-Type' => 'application/x-www-form-urlencoded',
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36'
		);
	}

	/**
	 * Выставляет путь для сохранение кешированного токена
	 *
	 * @param $path
	 */
	public function set_store_path($path)
	{
		$this->store_path = $path;
	}

	/**
	 * @param $client_id
	 * @param array $scope
	 * @return mixed
	 */
	public function tokenForClientWithScope($client_id, array $scope)
	{
		if (!$this->token)
		{
			if ($this->store_path)
			{
				$file = $this->store_path.'/token-'.md5($client_id.serialize($scope)).'.json';
				if (file_exists($file)) {
					$this->token = json_decode(file_get_contents($file), true);
				}
			}

			if (!$this->token) {
				$this->authorize($client_id, $scope);
			}
		}

		if ($this->token['expires'] && time() > $this->token['expires']) {
			$this->authorize($client_id, $scope);
		}

		return $this->token;
	}

	/**
	 * Авторизация в соц сети и получение secure_token
	 * позволяет миновать открытие браузера
	 *
	 * @param $client_id
	 * @param array $scope
	 * @throws AuthFailed
	 */
	private function authorize($client_id, array $scope)
	{
		$response_form = $this->login_form($client_id, $scope);

		$dom = \nokogiri::fromHtml($response_form->body);
		$origin = $dom->get('input[name="_origin"]')->toArray();
		$ip_h = $dom->get('input[name="ip_h"]')->toArray();
		$lg_h = $dom->get('input[name="lg_h"]')->toArray();
		$to = $dom->get('input[name="to"]')->toArray();

		$this->session->headers['Referer'] = $response_form->url;
		$this->session->headers['Origin'] = 'https://oauth.vk.com';

		if (!$ip_h || $lg_h || !$to || !$origin) {
			throw new AuthFailed('Wrong client id or scope');
		}

		$auth_response = $this->auth_post_request($ip_h[0]['value'], $lg_h[0]['value'], $to[0]['value'], $origin[0]['value']);

		// 405 status === OK
		if (!$auth_response->success) {
			$url = $auth_response->url;
		}
		// if 200 - need confirmation
		else
		{
			$this->session->headers['Referer'] = $auth_response->url;

			$confirm_form = \nokogiri::fromHtml($auth_response->body);
			$confirm_form_data = $confirm_form->get('form[method=post]')->toArray();

			$confirm_response = $this->post_confirm($confirm_form_data[0]['action']);

			$url = $confirm_response->url;
		}

		if (preg_match('|access_token=([^&]+)&expires_in=([^&]+)&user_id=([\d]+)|ius', $url, $m))
		{
			$this->token = array(
				'token' => $m[1],
				'expires' => $m[2] ? $m[2] + time() : 0,
				'uid'   => $m[3]
			);

			if ($this->store_path)
			{
				file_put_contents($this->store_path.'/token-'.md5($client_id.serialize($scope)).'.json', json_encode($this->token));
			}
		}
		else {
			throw new AuthFailed('Wrong auth params!');
		}
	}

	/**
	 * Получение формы входа(логина) в соц сети
	 *
	 * @param $client_id
	 * @param array $scope
	 * @return \Requests_Response
	 */
	private function login_form($client_id, array $scope)
	{
		$params = array(
			'client_id' => $client_id,
			'scope'     => $scope,
			'redirect_uri' => 'http://oauth.vk.com/blank.html',
			'display'   => 'mobile',
			'response_type' => 'token'
		);

		$url = 'https://oauth.vk.com/authorize?'.http_build_query($params);

		return $this->session->get($url);
	}

	/**
	 * Запрос на авторизацию
	 *
	 * @param $ip_h
	 * @param $lg_h
	 * @param $to
	 * @param $_origin
	 * @return \Requests_Response
	 */
	private function auth_post_request($ip_h, $lg_h, $to, $_origin)
	{
		$params = array(
			'ip_h' => $ip_h,
			'lg_h' => $lg_h,
			'to'    => $to,
			'_origin'   => $_origin,
			'email' => $this->login,
			'pass'  => $this->password,
		);
		$url = '?act=login&soft=1&utf8=1';

		$options = array();

		return $this->session->post($url, array(), $params, $options);
	}

	/**
	 * Разрешаем доступ приложению
	 *
	 * @param $url
	 * @return \Requests_Response
	 */
	private function post_confirm($url)
	{
		$v = explode('?', $url);
		return $this->session->post('?'.$v[1]);
	}
}

// ---------------------------------------------------------------------------------------------------------------------
// > END Auth < #
// --------------------------------------------------------------------------------------------------------------------- 