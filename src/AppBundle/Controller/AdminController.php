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
use AppBundle\Form\Type\StoreType;

//use Liuggio\ExcelBundle;

//use Symfony\Component\Validator\Constraints\Page;

class AdminController extends Controller
{
	protected $pageSize = 30;
	/**
	 * @Route("/admin/", name="admin_index")
	 */
	public function indexAction()
	{
		return $this->render('AppBundle:admin:index.html.twig');
	}
	/**
	 * @Route("/admin/store/", name="admin_store")
	 */
	public function storeAction(Request $request)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:Store');
		$queryBuilder = $repository->createQueryBuilder('a');
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$queryBuilder->where('a.id IN (:id)');
			$queryBuilder->setParameter('id', $user->getAdminStores());
		}
		
		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:store.html.twig', array('pagination'=>$pagination));
	}
	 /**
	 * @Route("/admin/store/view/{id}", name="admin_store_view")
	 */
	 public function storeViewAction($id)
	 {
	 	$user = $this->getUser();
	 	$admin_stores = explode(',', $user->getAdminStores());
	 	if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !in_array($id, $admin_stores)){
	 		return $this->render('AppBundle:admin:error.html.twig', array(
	 			'errorMsg' => '抱歉，您没有对应的权限'
	 			));
	 	}

	 	$store = $this->getDoctrine()->getRepository('AppBundle:Store')->find($id);
	 	return $this->render('AppBundle:admin:storeView.html.twig', array('store'=>$store));
	 }
	/**
	 * @Route("/admin/store/edit/{id}", name="admin_store_edit")
	 */
	public function storeEditAction(Request $request, $id)
	{
		$user = $this->getUser();
		/*
		$admin_stores = explode(',', $user->getAdminStores());
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !in_array($id, $admin_stores)){
			return $this->render('AppBundle:admin:error.html.twig', array(
				'errorMsg' => '抱歉，您没有对应的权限'
				));
		}
		*/
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){
			return $this->render('AppBundle:admin:error.html.twig', array(
				'errorMsg' => '抱歉，您没有对应的权限'
				));
		}
		$em = $this->getDoctrine()->getEntityManager();
		$store = $em->getRepository('AppBundle:Store')->find($id);
		$pageHeaderImg = $store->getPageHeaderImg();
		$wxImg = $store->getWxImg();
		$form = $this->createForm(new StoreType(), $store);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
			$file = $data->getPageHeaderImg();
			if( null != $file ){
				$pageHeaderImg = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $pageHeaderImg);
			}

			$file = $data->getWxImg();
			if( null != $file ){
				$wxImg = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $wxImg);
			}
			$store->setPageHeaderImg($pageHeaderImg);
			$store->setWxImg($wxImg);

			$em->persist($store);
			$em->flush();
			return $this->redirectToRoute('admin_store_view', array('id'=>$id));
		}
		return $this->render('AppBundle:admin:storeForm.html.twig', array(
			'form' => $form->createView(),
			));

	}
	/**
	 * @Route("/admin/store/add", name="admin_store_add")
	 */
	public function storeAddAction(Request $request)
	{
		$user = $this->getUser();
		//$admin_stores = explode(',', $user->getAdminStores());
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')){
			return $this->render('AppBundle:admin:error.html.twig', array(
				'errorMsg' => '抱歉，您没有对应的权限'
				));
		}
		$em = $this->getDoctrine()->getEntityManager();
		$store = new Entity\Store();
		$form = $this->createForm(new StoreType(), $store);
		$form->getData()->setPassword('LL'.rand(11111,99999));
		$form->setData($form->getData());
		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads';
			$file = $data->getPageHeaderImg();
			$pageHeaderImg = null;
			$wxImg = null;
			if( null != $file ){
				$pageHeaderImg = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $pageHeaderImg);
			}

			$file = $data->getWxImg();
			if( null != $file ){
				$wxImg = md5(uniqid()).'.'.$file->guessExtension();
				$file->move($fileDir, $wxImg);
			}
			$store->setPageHeaderImg($pageHeaderImg);
			$store->setWxImg($wxImg);
			$store->setCreateTime(new \DateTime("now"));
			$store->setCreateIp($this->container->get('request')->getClientIp());

			$em->persist($store);
			$em->flush();
			return $this->redirectToRoute('admin_store');
		}
		return $this->render('AppBundle:admin:storeForm.html.twig', array(
			'form' => $form->createView(),
			));
	}
	/**
	 * @Route("/admin/wechat_user/", name="admin_wechat_user")
	 */
	public function wechatUserAction(Request $request)
	{
		$repository = $this->getDoctrine()->getRepository('AppBundle:WechatUser');
		$queryBuilder = $repository->createQueryBuilder('a');
		$queryBuilder->orderBy('a.createTime', 'DESC');
		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:wechatUser.html.twig', array('pagination'=>$pagination));

	}
	/**
	 * @Route("/admin/bargain/list/{store_id}", name="admin_bargain")
	 */
	public function bargainAction(Request $request, $store_id = null)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:Bargain');
		$queryBuilder = $repository->createQueryBuilder('a');
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$queryBuilder->leftJoin('a.item','i');
			$queryBuilder->where('i.store IN (:store)');
			$queryBuilder->setParameter('store', $user->getAdminStores());
		}
		else{
			$queryBuilder->leftJoin('a.item','i');
			$queryBuilder->where('i.store = :store');
			$queryBuilder->setParameter('store', $store_id);
		}

		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:bargain.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/bargain/winner/{store_id}", name="admin_bargain_winner")
	 */
	public function bargainWinnerAction(Request $request, $store_id = null)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:Bargain');
		$queryBuilder = $repository->createQueryBuilder('a');
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$queryBuilder->leftJoin('a.item','i');
			$queryBuilder->where('i.store IN (:store) AND a.isWinner = 1');
			$queryBuilder->setParameter('store', $user->getAdminStores());
		}
		else{
			$queryBuilder->leftJoin('a.item','i');
			$queryBuilder->where('i.store = :store AND a.isWinner = 1');
			$queryBuilder->setParameter('store', $store_id);
		}

		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:bargain.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/log/{store_id}", name="admin_bargain_log")
	 */
	public function bargainLogAction(Request $request, $store_id = null)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:BargainLog');
		$queryBuilder = $repository->createQueryBuilder('a');
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$queryBuilder->leftJoin('a.bargain','p')->leftjoin('p.item','s');
			$queryBuilder->where('s.store IN (:store)');
			$queryBuilder->setParameter('store', $user->getAdminStores());
		}
		else{
			$queryBuilder->leftJoin('a.bargain','p')->leftjoin('p.item','s');
			$queryBuilder->where('s.store = :store');
			$queryBuilder->setParameter('store', $store_id);
		}

		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:bargainLog.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/buy/{store_id}", name="admin_buy_log")
	 */
	public function buyLogAction(Request $request, $store_id = null)
	{
		$user = $this->getUser();
		$repository = $this->getDoctrine()->getRepository('AppBundle:BuyLog');
		$queryBuilder = $repository->createQueryBuilder('a');
		if( !$this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ){
			$queryBuilder->leftJoin('a.bargain','p')->leftjoin('p.item','s');
			$queryBuilder->where('s.store IN (:store)');
			$queryBuilder->setParameter('store', $user->getAdminStores());
		}
		else{
			$queryBuilder->leftJoin('a.bargain','p')->leftjoin('p.item','s');
			$queryBuilder->where('s.store = :store');
			$queryBuilder->setParameter('store', $store_id);
		}

		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:buyLog.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/account/", name="admin_account")
	 */
	public function accountAction()
	{
		return new Response('');
	}
	/**
	 * @Route("/admin/export/", name="admin_export")
	 */
	public function exportAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('AppBundle:LotteryLog');
		$queryBuilder = $repository->createQueryBuilder('a')
		->leftjoin('a.lottery','b')
		->leftjoin('b.prize','c');
		$queryBuilder->where('c.id != 5');
		$queryBuilder->orderBy('a.createTime', 'DESC');
		$logs = $queryBuilder->getQuery()->getResult();
		//$output = '';
		$arr = array(
			'id,奖项,姓名,手机,地址,抽奖时间,抽奖IP'
			);
		foreach($logs as $v){
			$member = $em->getRepository('AppBundle:Member')->findOneBySessionId($v->getSessionId());
			$_string = $v->getId().','.$v->getLottery()->getPrize()->getTitle().',';
			if( isset($member))
				$_string .= $member->getName().','.$member->getTel().','.$member->getAddress().',';
			else
				$_string .= '-,-,-,';
			$_string .= $v->getCreateTime()->format('Y-m-d H:i:s').','.$v->getCreateIp().',';
			$arr[] = $_string;
		}
		$output = implode("\n", $arr);

		//$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
		/*
		$phpExcelObject = new \PHPExcel();
		$phpExcelObject->getProperties()->setCreator("liuggio")
			->setLastModifiedBy("Giulio De Donato")
			->setTitle("Office 2005 XLSX Test Document")
			->setSubject("Office 2005 XLSX Test Document")
			->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
			->setKeywords("office 2005 openxml php")
			->setCategory("Test result file");
		$phpExcelObject->setActiveSheetIndex(0);
		foreach($logs as $v){
			$phpExcelObject->setCellValue('A1', $v->getId());
		}
		$phpExcelObject->getActiveSheet()->setTitle('Simple');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$phpExcelObject->setActiveSheetIndex(0);

		// create the writer
		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
		// create the response
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		// adding headers
		$dispositionHeader = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			'stream-file.xls'
		);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		$response->headers->set('Content-Disposition', $dispositionHeader);
		*/

		$response = new Response($output);
		$response->headers->set('Content-Disposition', ':attachment; filename=data.csv');
		$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		return $response;
	}

}
