<?php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
	/**
	 * 后台主菜单
	 * @param FactoryInterface $factory
	 * @param array $options
	 * @return \Knp\Menu\ItemInterface
	 */
	public function mainMenu(FactoryInterface $factory, array $options)
	{
		$securityContext = $this->container->get('security.context');
		$user = $securityContext->getToken()->getUser();
		$em = $this->container->get('doctrine')->getManager();

		
		$menu = $factory->createItem('root');
		$menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked nav-bracket');
		$menu->setChildrenAttribute('id', 'leftmenu');

		$menu->addChild('Dashboard', array('route' => 'admin_index'));

		$menu->addChild('wechatUser', array('route' => 'admin_wechat_user', 'label' => '授权用户'));



		$item = $menu->addChild('item', array('route' => 'admin_item', 'label' => '商品管理'));
		$item->setAttribute('class', 'nav-parent');
		$item->setChildrenAttribute('class', 'children');

		$stores = $em ->getRepository('AppBundle:Store')->findAll();
		if ( $securityContext->isGranted('ROLE_SUPER_ADMIN')) {
			foreach ($stores as $key => $value) {
				$item->addChild('item_'.$value->getId(), array('route' => 'admin_item', 'routeParameters' => array('store_id'=> $value->getId()), 'label' => $value->getStoreName()));
			}
		}
		else{
			$item->addChild('ite', array('route' => 'admin_item', 'label' => '查看所有'));
		}
		$item->addChild('itemAdd', array('route' => 'admin_item_add', 'label' => '添加商品'));


		if ( $securityContext->isGranted('ROLE_SUPER_ADMIN')) {
			$store = $menu->addChild('store', array('route' => 'admin_store', 'label' => '店铺信息'));
			$store->setAttribute('class', 'nav-parent');
			$store->setChildrenAttribute('class', 'children');
			$store->addChild('admin', array('route' => 'admin_store', 'label' => '查看所有'));
			$store->addChild('storeAdd', array('route' => 'admin_store_add', 'label' => '添加店铺'));


			$user = $menu->addChild('user', array('route' => 'admin_user', 'label' => '权限管理'));
			$user->setAttribute('class', 'nav-parent');
			$user->setChildrenAttribute('class', 'children');
			$user->addChild('userAll', array('route' => 'admin_user', 'label' => '查看所有'));
			$user->addChild('userAdd', array('route' => 'admin_user_add', 'label' => '添加用户'));



			$buy_log = $menu->addChild('buyLog', array('route' => 'admin_buy_log', 'label' => '购买日志'));
			$buy_log->setAttribute('class', 'nav-parent');
			$buy_log->setChildrenAttribute('class', 'children');

			$bargain_winner = $menu->addChild('bargain_winner', array('route' => 'admin_bargain_winner', 'label' => '已中奖砍价'));
			$bargain_winner->setAttribute('class', 'nav-parent');
			$bargain_winner->setChildrenAttribute('class', 'children');

			$bargain = $menu->addChild('bargain', array('route' => 'admin_bargain', 'label' => '砍价信息'));
			$bargain->setAttribute('class', 'nav-parent');
			$bargain->setChildrenAttribute('class', 'children');


			$bargain_log = $menu->addChild('bargainLog', array('route' => 'admin_bargain_log', 'label' => '砍价日志'));
			$bargain_log->setAttribute('class', 'nav-parent');
			$bargain_log->setChildrenAttribute('class', 'children');


			foreach ($stores as $key => $value) {

				$buy_log->addChild('buy_log_'.$value->getId(), array('route' => 'admin_buy_log', 'routeParameters' => array('store_id'=> $value->getId()), 'label' => $value->getStoreName()));


				$bargain->addChild('bargain_'.$value->getId(), array('route' => 'admin_bargain', 'routeParameters' => array('store_id'=> $value->getId()), 'label' => $value->getStoreName()));

				$bargain_winner->addChild('bargain_winner_'.$value->getId(), array('route' => 'admin_bargain_winner', 'routeParameters' => array('store_id'=> $value->getId()), 'label' => $value->getStoreName()));

				$bargain_log->addChild('bargain_log_'.$value->getId(), array('route' => 'admin_bargain_log', 'routeParameters' => array('store_id'=> $value->getId()), 'label' => $value->getStoreName()));
			}

		}
		else{

			$menu->addChild('buyLog', array('route' => 'admin_buy_log', 'label' => '购买日志'));
			$menu->addChild('bargain', array('route' => 'admin_bargain', 'label' => '砍价信息'));
			$menu->addChild('bargain_winner', array('route' => 'admin_bargain_winner', 'label' => '已中奖砍价'));

			$menu->addChild('bargainLog', array('route' => 'admin_bargain_log', 'label' => '砍价日志'));

			$menu->addChild('store', array('route' => 'admin_store', 'label' => '店铺信息'));
		}
		


		/*
		$catalog_log = $menu->addChild('lotteryLog', array('route' => 'admin_log', 'label' => '中奖记录'));
		$catalog_log->setAttribute('class', 'nav-parent');
		$catalog_log->setChildrenAttribute('class', 'children');
		$catalog_log->addChild('win', array('route' => 'admin_log','routeParameters' => array('win'=>'y'), 'label' => '已中奖记录'));
		$catalog_log->addChild('noWin', array('route' => 'admin_log', 'routeParameters' => array('win'=>'n'), 'label' => '未中奖'));
		$menu->addChild('member', array('route' => 'admin_member', 'label' => '提交信息'));
		$menu->addChild('prize', array('route' => 'admin_prize', 'label' => '奖项信息'));
		$menu->addChild('lottery', array('route' => 'admin_lottery', 'label' => '抽奖设置'));
		*/
		return $menu;
	}
}