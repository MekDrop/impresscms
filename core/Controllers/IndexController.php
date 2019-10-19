<?php

namespace ImpressCMS\Core\Controllers;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles route request
 *
 * @package ImpressCMS\Core\Controllers
 */
class IndexController implements Controller{

	/**
	 * Main controller action
	 *
	 * @param RequestInterface $request
	 *
	 * @return ResponseInterface
	 */
	public function getIndex(RequestInterface $request): ResponseInterface
	{
		global $icmsConfig;

		$response = new Response();

		$member_handler = \icms::handler('icms_member');
		$group = $member_handler->getUserBestGroup(
			(!empty(\icms::$user) && is_object(\icms::$user)) ? \icms::$user->uid : 0
		);

		// added failover to default startpage for the registered users group -- JULIAN EGELSTAFF Apr 3 2017
		$groups = (!empty(\icms::$user) && is_object(\icms::$user)) ? \icms::$user->getGroups() : array(ICMS_GROUP_ANONYMOUS);
		if(($icmsConfig['startpage'][$group] == "" OR $icmsConfig['startpage'][$group] == "--")
			AND in_array(ICMS_GROUP_USERS, $groups)
			AND $icmsConfig['startpage'][ICMS_GROUP_USERS] != ""
			AND $icmsConfig['startpage'][ICMS_GROUP_USERS] != "--") {
			$icmsConfig['startpage'] = $icmsConfig['startpage'][ICMS_GROUP_USERS];
		} else {
			$icmsConfig['startpage'] = $icmsConfig['startpage'][$group];
		}

		if (isset($icmsConfig['startpage']) && $icmsConfig['startpage'] != '' && $icmsConfig['startpage'] != '--') {
			$arr = explode('-', $icmsConfig['startpage']);
			if (count($arr) > 1) {
				$page_handler = \icms::handler('icms_data_page');
				$page = $page_handler->get($arr[1]);
				if (is_object($page)) {
					header('Location: ' . $page->getURL());
				} else {
					$icmsConfig['startpage'] = '--';
					$this->getDefaultEmptyPage($request, $response);
				}
			} else {
				header('Location: ' . ICMS_MODULES_URL . '/' . $icmsConfig['startpage'] . '/');
			}
			exit();
		} else {
			$this->getDefaultEmptyPage($request, $response);
		}

		return $response;
	}

	protected function getDefaultEmptyPage(RequestInterface $request, ResponseInterface $response) {
		\icms::$response = new \icms_response_DefaultEmptyPage();
		\icms::$response->render();
	}

}