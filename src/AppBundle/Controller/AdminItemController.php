<?php
namespace AppBundle\Controller;

//use Guzzle\Http\Message\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\Time;
use AppBundle\Form\Type\ItemType;

//use Liuggio\ExcelBundle;

//use Symfony\Component\Validator\Constraints\Page;

class AdminItemController extends AdminController
{
	/**
	 * @Route("/admin/item/store/{store_id}", name="admin_item")
	 */
	public function itemAction(Request $request, $store_id = null)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:Item');
		$queryBuilder = $repository->createQueryBuilder('a');

		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			if( null != $store_id && in_array($store_id, explode(',', $user->getAdminStores()))){
				$queryBuilder->where('a.store IN (:store)');
				$queryBuilder->setParameter('store', $store_id);
			}
			else{
				$queryBuilder->where('a.store IN (:store)');
				$queryBuilder->setParameter('store', $user->getAdminStores());
			}
			
		}
		else{
			if( null != $store_id){
				$queryBuilder->where('a.store = :store');
				$queryBuilder->setParameter('store', $store_id);
			}
		}
		$queryBuilder->orderBy('a.orderId','ASC')->orderBy('a.isActive','DESC');
		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:item.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/item/delete/{id}", name="admin_item_delete")
	 */
	public function itemDeleteAction($id){
		return new Response('');
		$return = array(
			'ret' => 0,
			'msg'=>'',
			);
		$em = $this->getDoctrine()->getEntityManager();
		$item = $em->getRepository('AppBundle:Item')->find($id);
		//$item->removeBargain($item->getBargains());
		if( null != $item->getBargains() ){
			$bargains = $item->getBargains();
			foreach ($bargains as $bargain) {
				$em->remove($bargain);
				foreach ($bargain->getLogs() as $log) {
					$em->remove($log);
				}
				foreach ($bargain->getBuyLogs() as $log) {
					$em->remove($log);
				}
			}
		}
		$em->remove($item);
		$em->flush();
		return new Response(json_encode($return));
	}


	/**
	 * @Route("/admin/item/active/{id}", name="admin_item_active")
	 */
	public function itemActiveAction($id)
	{
		$return = array(
			'ret' => 0,
			'msg'=>'',
			);
		$em = $this->getDoctrine()->getEntityManager();
		$item = $em->getRepository('AppBundle:Item')->find($id);
		if( $item->getIsActive() == 0 && !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){
			$qb = $em->getRepository('AppBundle:Item')
			->createQueryBuilder('a')
			->select('COUNT(a)')
			->where('a.store = :store')
			->setParameter('store', $item->getStore());
			$count = $qb->getQuery()->getSingleScalarResult();
			if(	$count >= 10 ){
				$return['ret'] = 1001;
				$return['msg'] = '抱歉，您的砍价商品数量已达上线 ！';
			}
			else{
				$item->setIsActive(1);
				$em->persist($item);
				$em->flush();
			}
		}
		elseif( $item->getIsActive() == 0 && $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){
			$item->setIsActive(1);
			$em->persist($item);
			$em->flush();
		}
		else{
			$item->setIsActive(0);
			$em->persist($item);
			$em->flush();
		}
		
		return new Response(json_encode($return));
	}
	


	 /**
	 * @Route("/admin/item/view/{id}", name="admin_item_view")
	 */
	 public function itemViewAction($id)
	 {
	 	$user = $this->getUser();
	 	$admin_stores = explode(',', $user->getAdminStores());
	 	$item = $this->getDoctrine()->getRepository('AppBundle:Item')->find($id);
	 	if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !in_array($item->getStore()->getId(), $admin_stores)){
	 		return $this->render('AppBundle:admin:error.html.twig', array(
	 			'errorMsg' => '抱歉，您没有对应的权限'
	 			));
	 	}


	 	return $this->render('AppBundle:admin:itemView.html.twig', array('item'=>$item));
	 }
	/**
	 * @Route("/admin/item/edit/{id}", name="admin_item_edit")
	 */
	public function itemEditAction(Request $request, $id)
	{
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$user = $this->getUser();
			$admin_stores = explode(',', $user->getAdminStores());
		}
		else{
			$admin_stores = null;
		}
		
		$em = $this->getDoctrine()->getEntityManager();
		$item = $em->getRepository('AppBundle:Item')->find($id);
		$imgUrl = $item->getImgUrl();
		$detailImgUrl = $item->getDetailImgUrl();
		$form = $this->createForm(new ItemType($admin_stores), $item);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
			$file = $data->getImgUrl();
			if( null != $file ){
				$imgUrl = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $imgUrl);
			}

			$file = $data->getDetailImgUrl();
			if( null != $file ){
				$detailImgUrl = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $detailImgUrl);
			}
			$item->setBargainPrice($data->getDiscountPrice());
			$item->setImgUrl($imgUrl);
			$item->setDetailImgUrl($detailImgUrl);

			$em->persist($item);
			$em->flush();
			return $this->redirectToRoute('admin_item_view', array('id'=>$id));
		}
		return $this->render('AppBundle:admin:itemForm.html.twig', array(
			'form' => $form->createView(),
			));

	}
	/**
	 * @Route("/admin/item/add", name="admin_item_add")
	 */
	public function itemAddAction(Request $request)
	{
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$user = $this->getUser();
			$admin_stores = explode(',', $user->getAdminStores());
		}
		else{
			$admin_stores = null;
		}
		$em = $this->getDoctrine()->getEntityManager();
		$item = new Entity\Item();
		$form = $this->createForm(new ItemType($admin_stores), $item);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$imgUrl = null;
			$detailImgUrl = null;
			$data = $form->getData();
			$fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
			$file = $data->getImgUrl();
			if( null != $file ){
				$imgUrl = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $imgUrl);
			}

			$file = $data->getDetailImgUrl();
			if( null != $file ){
				$detailImgUrl = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $detailImgUrl);
			}
			$item->setBargainPrice($data->getDiscountPrice());
			$item->setImgUrl($imgUrl);
			$item->setDetailImgUrl($detailImgUrl);
			$item->setCreateTime(new \DateTime("now"));
			$item->setCreateIp($this->container->get('request')->getClientIp());

			$em->persist($item);
			$em->flush();
			return $this->redirectToRoute('admin_item', array('store_id'=>$item->getStore()->getId()));
		}
		return $this->render('AppBundle:admin:itemForm.html.twig', array(
			'form' => $form->createView(),
			));
	}

}
