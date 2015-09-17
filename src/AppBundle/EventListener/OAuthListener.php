<?php
/**
 * Created by PhpStorm.
 * User: Alexa
 * Date: 15/6/4
 * Time: 下午3:16
 */
namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Httpkernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Wechat;
use Doctrine\ORM\EntityManager;

class OAuthListener
{
	protected $container;
	protected $router;
	protected $em;
	public function __construct($router, \Symfony\Component\DependencyInjection\Container $container,EntityManager $em)
	{
		$this->container = $container;
		$this->router = $router;
		$this->em = $em;
	}
	/*
	public function onKernelController(FilterControllerEvent $event)
	{
		//$controller = $event->getController();
		// 此处controller可以被该换成任何PHP可回调函数
		//$event->setController($controller);
	}
	*/
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		$session = $request->getSession();
		if($request->getClientIp() == '127.0.0.1'){
			$session->set('open_id', 'o5mOVuGapiB3tzYysVcE4xstN3s4');
			$session->set('user_id', 1);
		}
		else{
			if( $session->get('open_id') === null 
				&& $request->attributes->get('_route') !== '_callback' 
				&& stripos($request->attributes->get('_controller'), 'DefaultController') !== false
			){
				$app_id = $this->container->getParameter('wechat_appid');
				$session->set('redirect_url', $request->getUri());
				$state = '';
				$callback_url = $request->getUriForPath('/callback');
				//$callback_url = $this->router->generate('_callback','');
				$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_id."&redirect_uri=".$callback_url."&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
				$event->setResponse(new RedirectResponse($url));
			}
			$appId = $this->container->getParameter('wechat_appid');
			$appSecret = $this->container->getParameter('wechat_secret');
			$wechat = new Wechat\Wechat($appId, $appSecret);
			$wx = (Object)$wechat->getSignPackage();
			$session->set('wx_app_id', $wx->appId);
			$session->set('wx_timestamp', $wx->timestamp);
			$session->set('wx_nonce_str', $wx->nonceStr);
			$session->set('wx_signature', $wx->signature);
		}
		
		if( stripos($request->attributes->get('_controller'), 'DefaultController') !== false ){
			if( null == $request->get('store_id') ){
				$store_id = null == $session->get('store_id') ? 1 : $session->get('store_id');
			}
			else{
				$store_id = $request->get('store_id');
			}
			$session->set('store_id', $store_id);
			
			$store = $this->em->getRepository('AppBundle:Store')->find($session->get('store_id'));
			if( $store !== null ){
				$session->set('storeImg', $store->getPageHeaderImg());
				$session->set('storeDescription', $store->getDescription());
				$session->set('pageTitle', $store->getTitle());
				$session->set('storeInfo', $store->getInfo());
				$session->set('storeName', $store->getStoreName());
				$session->set('storeAddress', $store->getAddress());
				$session->set('storeTel', $store->getTel());
				$session->set('wechat_title', $store->getWxTitle());
				$session->set('wechat_desc', $store->getWxDesc());
				$session->set('wechat_img_url', $store->getWxImg());
				$session->set('wx_share_url', 'http://'.$request->getHost().$this->router->generate('_index', array('store_id'=>$store->getId())));
			}
			#店铺关闭状态
			if( (null == $store || $store->getIsActive() == 0 ) && $request->attributes->get('_route') != '_close' )
				$event->setResponse(new RedirectResponse($this->router->generate('_close')));
		}
		
	}
	/*
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$response = $event->getResponse();
		$request = $event->getRequest();
		if ($request->query->get('option') == 3) {
			$response->headers->setCookie(new Cookie("test", 1));
		}
	}
	*/
}