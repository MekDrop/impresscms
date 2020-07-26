<?php


namespace ImpressCMS\Core\Controllers;

use ImpressCMS\Core\Response\RedirectResponse;
use ImpressCMS\Core\Response\ViewResponse;
use League\Route\ContainerAwareInterface;
use League\Route\ContainerAwareTrait;
use League\Route\Http\Exception\ForbiddenException;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines all user related routes
 *
 * @package ImpressCMS\Core\Controllers
 */
class UserController implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Deals with user.php actions
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function getUserIndex(ServerRequestInterface $request): ResponseInterface
	{
		$params = $request->getQueryParams() + $request->getParsedBody();

		switch ((isset($params['op'])) ? trim($params['op']) : 'main') {
			case 'resetpass':
				return $this->getResetPass($request, $this->getRedirectUrl($params));
			case 'logout':
				return $this->getLogout();
			case 'actv':
				return $this->getActivatePage($request, $params['id'] ?? null, $params['actkey'] ?? '');
			case 'login':
				return $this->postLogin($request, $this->getRedirectUrl($params));
			case 'delete':
				if ($request->getMethod() == 'POST') {
					return $this->getDeletePage($request);
				}
				return $this->postDeletePage();
			case 'main':
			default:
				return $this->getMain($request, $this->getRedirectUrl($params));
		}
	}

	public function postLogin(ServerRequestInterface $request, string $redirect = ''): ResponseInterface {
		$post = $request->getParsedBody();

		$uname = isset($post['uname'])?trim($post['uname']):'';
		$pass = isset($post['pass'])?trim($post['pass']):'';

		/* if redirect goes to the register page, divert to main page - users don't go to register */
		if ($redirect && strpos($redirect, 'register') !== false) {
			$redirect = ICMS_URL;
		}

		$member_handler = \icms::handler('icms_member');

		\icms_loadLanguageFile('core', 'auth');
		$icmsAuth = & \icms_auth_Factory::getAuthConnection(\icms_core_DataFilter::addSlashes($uname));

		$uname4sql = addslashes(\icms_core_DataFilter::stripSlashesGPC($uname));
		$pass4sql = \icms_core_DataFilter::stripSlashesGPC($pass);

		/* Check to see if being access by a user - if not, attempt to authenticate */
		if (empty($user) || !is_object($user)) {
			$user = & $icmsAuth->authenticate($uname4sql, $pass4sql);
		}

		/* User exists: check to see if the user has been activated.
		 * If not, redirect with 'no permission' message
		 */
		if (false != $user) {
			if (0 == $user->getVar('level')) {
				redirect_header(ICMS_URL . '/', 5, _US_NOACTTPADM);
				exit();
			}

			/* Check to see if logins from multiple locations is permitted.
			 * If it is not, check for existing login and redirect if detected
			 */
			if ($icmsConfigPersona['multi_login']) {
				if (is_object($user)) {
					$online_handler = icms::handler('icms_core_Online');
					$online_handler->gc(300);
					$onlines = & $online_handler->getAll();
					foreach ($onlines as $online) {
						if ($online['online_uid'] == $user->getVar('uid')) {
							$user = false;
							redirect_header(ICMS_URL . '/', 3, _US_MULTLOGIN);
						}
					}
					if (is_object($user)) {
						$online_handler->write(
							$user->getVar('uid'),
							$user->getVar('uname'),
							time(),
							0,
							$_SERVER['REMOTE_ADDR']
						);
					}
				}
			}

			/* Check if site is closed and verify user's group can access if it is */
			if ($icmsConfig['closesite'] == 1) {
				$allowed = false;
				foreach ($user->getGroups() as $group) {
					if (in_array($group, $icmsConfig['closesite_okgrp']) || ICMS_GROUP_ADMIN == $group) {
						$allowed = true;
						break;
					}
				}
				if (!$allowed) {
					redirect_header(ICMS_URL . '/', 1, _NOPERM);
					exit();
				}
			}

			/* Continue with login - all negative checks have been passed */
			$user->setVar('last_login', time());
			if (!$member_handler->insertUser($user)) {}
			// Regenerate a new session id and destroy old session

			/**
			 * @var Aura\Session\Session $session
			 */
			$session = \icms::getInstance()->get('session');
			$session->resume();
			$session->regenerateId();
			$session->clear();

			$userSegment = $session->getSegment(icms_member_user_Object::class);
			$userSegment->set('userid', $user->getVar('uid'));
			$userSegment->set('groups', $user->getGroups());
			$userSegment->set('last_login', $user->getVar('last_login'));

			if (!$member_handler->updateUserByField($user, 'last_login', time())) {}
			$user_theme = $user->getVar('theme');
			if (in_array($user_theme, $icmsConfig['theme_set_allowed'])) {
				$session->getSegment(icms_view_theme_Object::class)->set('name', $user_theme);
			}

			// autologin hack V3.1 GIJ (set cookie)
			$secure = substr(ICMS_URL, 0, 5) == 'https'?1:0; // we need to secure cookie when using SSL
			$icms_cookie_path = defined('ICMS_COOKIE_PATH')? ICMS_COOKIE_PATH :
				preg_replace('?http://[^/]+(/.*)$?', "$1", ICMS_URL);
			if ($icms_cookie_path == ICMS_URL) {
				$icms_cookie_path = '/';
			}
			if (!empty($_POST['rememberme'])) {
				$expire = time() + (defined('ICMS_AUTOLOGIN_LIFETIME')? ICMS_AUTOLOGIN_LIFETIME : 604800); // 1 week default
				setcookie('autologin_uname', $user->getVar('login_name'), $expire, $icms_cookie_path, '', $secure, 0);
				$Ynj = date('Y-n-j');
				setcookie('autologin_pass', $Ynj . ':' . md5($user->getVar('pass') . ICMS_DB_PASS . ICMS_DB_PREFIX . $Ynj),
					$expire, $icms_cookie_path, '', $secure, 0);
			}
			// end of autologin hack V3.1 GIJ

			// Perform some maintenance of notification records
			$notification_handler = icms::handler('icms_data_notification');
			$notification_handler->doLoginMaintenance($user->getVar('uid'));

			/* check if user's password has expired and send to reset password page if it has */
			$is_expired = $user->getVar('pass_expired');
			if ($is_expired == 1) {
				redirect_header(ICMS_URL . '/user.php?op=resetpass', 5, _US_PASSEXPIRED, false);
			} else {
				redirect_header($redirect, 1, sprintf(_US_LOGGINGU, $user->getVar('uname')), false);
			}

		} elseif (!isset($_POST['xoops_redirect']) && !isset($_GET['xoops_redirect'])) {
			/* if not a user and redirect has not been set, go back to the user page */
			redirect_header(ICMS_URL . '/user.php', 5, $icmsAuth->getHtmlErrors());
		} else {
			/* if not a user and redirect has been set, go back to that page */
			redirect_header(
				ICMS_URL . '/user.php?xoops_redirect='
				. urlencode($redirect), 5, $icmsAuth->getHtmlErrors(), false
			);
		}
	}

	/**
	 * Executes when is ok to delete user
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function postDeletePage(ServerRequestInterface $request): ResponseInterface {
		global $icmsConfigUser;
		if (!\icms::$user || (int)$icmsConfigUser['self_delete'] !== 1) {
			throw new ForbiddenException(_US_NOPERMISS);
		}
		$groups = \icms::$user->getGroups();
		if (in_array(ICMS_GROUP_ADMIN, $groups, true)) {
			return redirect_header('user.php', 5, _US_ADMINNO);
		}

		$post = $request->getParsedBody();
		if ($post['ok']) {
			$member_handler = \icms::handler('icms_member');
			if ($member_handler->deleteUser(\icms::$user) !== false) {
				$online_handler = \icms::handler('icms_core_Online');
				$online_handler->destroy(\icms::$user->uid);
				$notification_handler = &\icms::handler('icms_data_notification');
				$notification_handler->unsubscribeByUser(\icms::$user->uid);
				return redirect_header('index.php', 5, _US_BEENDELED);
			}
			return redirect_header('index.php', 5, _US_NOPERMISS);
		}
		return new RedirectResponse('index.php');
	}

	public function getDeletePage(): ResponseInterface
	{
		global $icmsConfigUser;
		if (!\icms::$user || (int)$icmsConfigUser['self_delete'] !== 1) {
			throw new ForbiddenException(_US_NOPERMISS);
		}
		$groups = \icms::$user->getGroups();
		if (in_array(ICMS_GROUP_ADMIN, $groups, true)) {
			return redirect_header('user.php', 5, _US_ADMINNO);
		}
		include 'header.php';
		\icms_core_Message::confirm(['op' => 'delete', 'ok' => 1], 'user.php', _US_SURETODEL . '<br/>' . _US_REMOVEINFO);
		include 'footer.php';
	}

	/**
	 * Gets activate user page (op = actv)
	 *
	 * @param ServerRequestInterface $request
	 * @param int|null $id
	 * @param string $activationKey
	 * @return ResponseInterface
	 * @throws NotFoundException
	 */
	public function getActivatePage(ServerRequestInterface $request, ?int $id, string $activationKey): ResponseInterface
	{
		if (empty($id)) {
			return redirect_header('index.php', 1, '');
		}
		$member_handler = \icms::handler('icms_member');
		$user = &$member_handler->getUser($id);
		if ($user === null) {
			throw new NotFoundException();
		}
		if ($user->actkey !== $activationKey) {
			return redirect_header('index.php', 5, _US_ACTKEYNOT);
		}
		if ($user->level > 0) {
			return redirect_header('user.php', 5, _US_ACONTACT, false);
		}
		if ($member_handler->activateUser($user) !== false) {
			global $icmsConfigUser, $icmsConfig;
			if ((int)$icmsConfigUser['activation_type'] === 2) {
				$mailer = new \icms_messaging_Handler();
				$mailer->useMail();
				$mailer->setTemplate('activated.tpl');
				$mailer->assign('SITENAME', $icmsConfig['sitename']);
				$mailer->assign('ADMINMAIL', $icmsConfig['adminmail']);
				$mailer->assign('SITEURL', ICMS_URL . '/');
				$mailer->setToUsers($user);
				$mailer->setFromEmail($icmsConfig['adminmail']);
				$mailer->setFromName($icmsConfig['sitename']);
				$mailer->setSubject(sprintf(_US_YOURACCOUNT, $icmsConfig['sitename']));
				return redirect_header(
					'user.php',
					3,
					(!$mailer->send()) ? printf(_US_ACTVMAILNG, $user->getVar('uname')) : printf(_US_ACTVMAILOK, $user->getVar('uname')),
					false
				);
			}
			$user->sendWelcomeMessage();
			return redirect_header('user.php', 3, _US_ACTLOGIN, false);
		}
		return redirect_header('index.php', 3, 'Activation failed!');
	}

	/**
	 * Gets main page (op = main)
	 *
	 * @param ServerRequestInterface $request
	 * @param string $redirect
	 * @return ResponseInterface
	 */
	public function getMain(ServerRequestInterface $request, string $redirect = ''): ResponseInterface
	{
		if (!\icms::$user) {

			global $icmsConfig, $icmsConfigUser;

			$response = new ViewResponse([
				'pagetype' => 'user',
				'template_main' => 'system_userform.html',
			]);

			$cookies = $request->getCookieParams();

			$response->assign('usercookie', $cookies[$icmsConfig['usercookie']] ?? false);
			$response->assign('lang_login', _LOGIN);
			$response->assign('lang_username', _USERNAME);
			$response->assign('redirect_page', $redirect);
			$response->assign('lang_password', _PASSWORD);
			$response->assign('lang_notregister', _US_NOTREGISTERED);
			$response->assign('lang_lostpassword', _US_LOSTPASSWORD);
			$response->assign('lang_noproblem', _US_NOPROBLEM);
			$response->assign('lang_youremail', _US_YOUREMAIL);
			$response->assign('lang_sendpassword', _US_SENDPASSWORD);
			$response->assign('lang_rememberme', _US_REMEMBERME);
			$response->assign('mailpasswd_token', \icms::$security->createToken());
			$response->assign('allow_registration', $icmsConfigUser['allow_register']);
			$response->assign('rememberme', $icmsConfigUser['remember_me']);
			$response->assign('icms_pagetitle', _LOGIN);

			return $response;
		}
		if ($redirect) {
			return new RedirectResponse($redirect);
		}
		return new RedirectResponse(
			ICMS_URL . '/userinfo.php?uid=' . (int)\icms::$user->uid
		);
	}

	/**
	 * Logouts logged in user (op = logout)
	 *
	 * @return ResponseInterface
	 */
	public function getLogout(): ResponseInterface
	{
		$this->container->get('session')->destroy();

		return redirect_header(ICMS_URL . '/', 3, _US_LOGGEDOUT . '<br />' . _US_THANKYOUFORVISIT);
	}

	/**
	 * Gets reset pass page (op = resetpass)
	 *
	 * @param ServerRequestInterface $request
	 * @param string $redirect
	 * @return ResponseInterface
	 */
	public function getResetPass(ServerRequestInterface $request, string $redirect = ''): ResponseInterface
	{
		if (\icms::$user) {
			$response = new ViewResponse([
				'pagetype' => 'user',
				'template_main' => 'system_userform.html'
			]);
			$response->assign('redirect_page', $redirect);
			$response->assign('lang_reset', 1);
			$response->assign('lang_resetpassword', _US_RESETPASSWORD);
			$response->assign('lang_resetpassinfo', _US_RESETPASSINFO);

			$response->assign('lang_sendpassword', _US_SENDPASSWORD);
			$response->assign('lang_subresetpassword', _US_SUBRESETPASSWORD);
			$response->assign('lang_currentpass', _US_CURRENTPASS);
			$response->assign('lang_newpass', _US_NEWPASSWORD);
			$response->assign('lang_newpass2', _US_VERIFYPASS);
			$response->assign('resetpassword_token', \icms::$security->createToken());
			$response->assign('icms_pagetitle', _LOGIN);

			return $response;
		}
		if ($redirect) {
			return new RedirectResponse($redirect);
		}
		return new RedirectResponse(ICMS_URL . '/userinfo.php?uid=' . (int)\icms::$user->uid);
	}

	/**
	 * Gets redirect URI from request
	 *
	 * @param array $params Get and post params array
	 *
	 * @return string
	 */
	protected function getRedirectUrl(array $params): string
	{
		$redirect = $params['xoops_redirect'] ?? false;

		if ($redirect) {
			$redirect = htmlspecialchars(trim($redirect), ENT_QUOTES, _CHARSET);
			$pos = strpos($redirect, '://');
			if ($pos !== false) {
				$location = substr(ICMS_URL, strpos(ICMS_URL, '://') + 3);
				if (substr($redirect, $pos + 3, strlen($location)) !== $location) {
					$redirect = ICMS_URL;
				} elseif (substr($redirect, $pos + 3, strlen($location) + 1) === $location . '.') {
					$redirect = ICMS_URL;
				}
			}
		}

		if ($redirect && $redirect !== htmlspecialchars($_SERVER['REQUEST_URI'])) {
			$redirect = ICMS_URL;
		}

		return $redirect;
	}

}