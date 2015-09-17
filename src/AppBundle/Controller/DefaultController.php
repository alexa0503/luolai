<?php
namespace AppBundle\Controller;

use AppBundle\Wechat\Wechat;
use Imagine\Gd\Imagine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Helper;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Cookie;

#use Symfony\Component\Validator\Constraints\Image;

class DefaultController extends Controller
{
    public function getUser()
    {
        $session = $this->get('session');
        //$em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->findOneByOpenId($session->get('open_id'));
        return $user;
    }
    public function getStore()
    {
        $session = $this->get('session');
        //默认店铺
        $store_id = null == $session->get('store_id') ? 1 : $session->get('store_id');
        //$em = $this->getDoctrine()->getManager();
        $store = $this->getDoctrine()->getRepository('AppBundle:Store')->find($store_id);
        return $store;
    }
    /**
     * @Route("/home/{store_id}", name="_index")
     * @Route("/", name="__index")
     */
    public function indexAction($store_id = 1)
    {
        $store = $this->getStore();
        //$items = $this->getDoctrine()->getRepository('AppBundle:Item')->findAllByStore($store);
        return $this->render('AppBundle:default:index.html.twig', array(
            'store' => $store
        ));
    }
    /**
     * @Route("/close", name="_close")
     */
    public function closeAction()
    {
        return $this->render('AppBundle:default:close.html.twig');
    }
    /**
     * @Route("/item/{item_id}/{store_id}/{user_id}", name="_item")
     */
    public function itemAction(Request $request, $item_id, $store_id = null, $user_id = null)
    {
        $item = $this->getDoctrine()->getRepository('AppBundle:Item')->find($item_id);
        if(null == $item){
            return $this->render('AppBundle:default:error.html.twig');
        }
        $user = $this->getUser();
        if($user_id == null )
            $user_id = $user->getId();

        $itemUser = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->find($user_id);
        $em = $this->getDoctrine()->getEntityManager();
        $bargain = $em->getRepository('AppBundle:Bargain')->findOneBy( array('item'=>$item, 'user'=>$user));
        //var_dump($bargain->getId());
        if( null == $bargain){
            $em = $this->getDoctrine()->getEntityManager();
            $bargain = new Entity\Bargain;
            $bargain->setUser($user);
            $bargain->setItem($item);
            $bargain->setNum(0);
            $bargain->setPrice($item->getPrice());
            $bargain->setExchangeCode('');
            $bargain->setIsWinner(0);
            $bargain->setCreateTime(new \DateTime("now"));
            $bargain->setCreateIp($this->container->get('request')->getClientIp());
            $em->persist($bargain);
            $em->flush();
        }
        $bargain = $em->getRepository('AppBundle:Bargain')->findOneBy( array('item'=>$item, 'user'=>$itemUser));
        //var_dump($itemUser->getId());
        if( null == $bargain){
            return $this->redirectToRoute('_index', array('store_id'=>$store_id)); 
        }
        if ($bargain->getIsWinner() == 1 && $user->getId() == $user_id) {
            return $this->redirectToRoute('_success', array('bargain_id'=>$bargain->getId(), 'store_id'=>$bargain->getItem()->getStore()->getId()));
        }
        $discount_price = preg_replace('/\.$/U', '', rtrim($bargain->getPrice(), '0'));
        $discount_price = preg_replace('/\d{1}/i', '<span>$0</span>', $discount_price);

        $logs = $em->getRepository('AppBundle:BargainLog')->findBy( array('bargain'=>$bargain));

        $request->getSession()->set('wx_share_url', 'http://'.$request->getHost().$this->generateUrl('_item', array(
            'item_id' => $item_id,
            'user_id' => $user_id,
            'store_id' => $store_id,
        )));
        return $this->render('AppBundle:default:item.html.twig',array(
            'item' => $item,
            'bargain' => $bargain,
            'discount_price' => $discount_price,
            'logs'=>$logs,
            'user'=>$user,
            'itemUser'=>$itemUser,
        ));
    }
    /**
     * @Route("/success/{bargain_id}/{store_id}", name="_success")
     */
    public function successAction(Request $request, $bargain_id, $store_id)
    {
        $bargain = $this->getDoctrine()->getRepository('AppBundle:Bargain')->find($bargain_id);
        if( $bargain == null || $bargain->getUser()->getId() != $this->getUser()->getId() ){
            return $this->redirectToRoute('_index', array('store_id'=>$store_id)); 
        }
        $discount_price = preg_replace('/\.$/U', '', rtrim($bargain->getPrice(), '0'));
        $discount_price = preg_replace('/\d{1}/i', '<span>$0</span>', $discount_price);
        return $this->render('AppBundle:default:success.html.twig',array(
            'bargain' => $bargain,
            'discount_price' => $discount_price,
        ));
    }
    /**
     * @Route("/ticket/{bargain_id}/{store_id}", name="_ticket")
     */
    public function ticketAction(Request $request, $bargain_id, $store_id)
    {
        $bargain = $this->getDoctrine()->getRepository('AppBundle:Bargain')->find($bargain_id);
        return $this->render('AppBundle:default:ticket.html.twig',array(
            'bargain' => $bargain,
        ));
    }
    /**
     * @Route("/post/{bargain_id}/{store_id}", name="_ticket_post")
     */
    public function ticketPostAction(Request $request, $bargain_id, $store_id)
    {
        $cookies = $request->cookies;
        $session = $request->getSession();
        $return = array(
            'ret' => 0,
            'msg' => ''
        );
        $em = $this->getDoctrine()->getEntityManager();
        $bargain = $em->getRepository('AppBundle:Bargain')->find($bargain_id);
        if( $cookies->has('storePassword')){
            if( null == $bargain){
                $return['ret'] = 1501;
                $return['msg'] = '该砍价不存在';
            }
            elseif( !$bargain->getItem()->getIsActive()){
                $return['ret'] = 1505;
                $return['msg'] = '抱歉，'.$bargain->getItem()->getName().'已下架~';
            }
            elseif($bargain->getIsBought()){
                $return['ret'] = 1502;
                $return['msg'] = $bargain->getUser()->getNickname().'，'.$bargain->getItem()->getName().'已购买~';
            }
            elseif( !$bargain->getIsWinner()){
                $return['ret'] = 1503;
                $return['msg'] = $bargain->getUser()->getNickname().'，'.$bargain->getItem()->getName().'砍价未成功，请继续努力~';
            }
            elseif( $bargain->getItem()->getStore()->getPassword() != $cookies->has('storePassword') ){
                $return['ret'] = 1504;
                $return['msg'] = '请到'.$bargain->getItem()->getStore()->getStoreName().'购买~';
            }
            else{
                $bargain->setIsBought(1);
                $log = new Entity\BuyLog;
                $log->setUser($this->getUser());
                $log->setBargain($bargain);
                $log->setCreateTime(new \DateTime("now"));
                $log->setCreateIp($this->container->get('request')->getClientIp());
                $em->persist($bargain);
                $em->persist($log);
                $em->flush();
                $return['msg'] = $bargain->getUser()->getNickname().'，购买成功，感谢您的参与。';
            }
        }
        else{
            $return['ret'] = 1104;
            $return['msg'] = '请登录';
        }
        return new Response(json_encode($return));
    }
     /**
     * @Route("/ticket_login/{bargain_id}/{store_id}", name="_ticket_login")
     */
    public function ticketLogin(Request $request, $bargain_id, $store_id)
    {
        $cookies = $request->cookies;
        $session = $request->getSession();
        $return = array(
            'ret' => 0,
            'msg' => ''
        );
        $em = $this->getDoctrine()->getEntityManager();
        $bargain = $em->getRepository('AppBundle:Bargain')->find($bargain_id);
        if( $request->get('password') != null 
            && strtolower($request->get('password')) == strtolower($bargain->getItem()->getStore()->getPassword() )
            ){
            $response = new Response(json_encode($return));
            $cookie = new Cookie('storePassword', $bargain->getItem()->getStore()->getPassword());
            $response->headers->setCookie( $cookie );
        }
        else{
            $return['ret'] = 1001;
            $return['msg'] = '密码错误';
            $response = new Response(json_encode($return));
        }
        return $response;
    }
    /**
     * @Route("/bargain/{bargain_id}/{store_id}", name="_bargain")
     */
    public function bargainAction(Request $request, $bargain_id, $store_id)
    {
        $return = array(
            'ret' => 0,
            'msg' => '',
        );
        $user = $this->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $bargain = $this->getDoctrine()->getRepository('AppBundle:Bargain')->find($bargain_id);

        $em->getConnection()->beginTransaction();
        try {
            $qb = $em->getRepository('AppBundle:BargainLog')
                ->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->where('a.user=:user AND a.bargain = :bargain')
                ->setParameter('user', $user)
                ->setParameter('bargain', $bargain);
            $count = $qb->getQuery()->getSingleScalarResult();

            
            if($bargain->getIsWinner() == 1){
                $return['ret'] = 1002;
                $return['msg'] = '已经成功，不能再砍啦~';
            }
            elseif($bargain->getItem()->getWinNum() >= $bargain->getItem()->getNum()){
                $return['ret'] = 1003;
                $return['msg'] = '商品已经砍完啦~';
            }
            elseif($bargain->getNum() >= $bargain->getItem()->getBargainNum()){
                $return['ret'] = 1004;
                $return['msg'] = '已经砍到最大次数啦~';
            }
            elseif($bargain->getUser()->getId() == $user->getId() ){
                $return['ret'] = 1001;
                $return['msg'] = '不能给自己砍价喔~';
            }
            elseif( $count > 0){
                $return['ret'] = 1005;
                $return['msg'] = '已经砍过了喔~';
            }
            else{
                
                if( $bargain->getItem()->getBargainNum() - $bargain->getNum() == 1){
                    $bargain->setIsWinner(1);
                    //$bargain->getItem()->setWinNum($bargain->getItem()->getWinNum() + 1);
                    $bargain->getItem()->increaseWinNum();
                    $price = $bargain->getPrice() - $bargain->getItem()->getDiscountPrice();
                    $bargain_price = $bargain->getPrice() - $price;
                    $bargain->setExchangeCode = 'AU'.rand(10000, 99999).$bargain->getId();
                }
                else{
                     $remain_price = $bargain->getPrice() - $bargain->getItem()->getDiscountPrice();
                     $basic_price = $remain_price / ($bargain->getItem()->getBargainNum() - $bargain->getNum());
                        if ($basic_price < 2) {
                            $price = 1;
                        } else {
                            $min_price = 1;
                            $max_price = ceil($basic_price);
                            $price = rand($min_price, $max_price);
                        }
                        $bargain_price = $remain_price > $price ? $bargain->getPrice() - $price : $bargain->getPrice();
                }
                $bargain->setPrice($bargain_price);
                //$bargain->setNum($bargain->getNum() + 1);
                $bargain->increaseNum();
                $log = new Entity\BargainLog;
                $log->setUser($user);
                $log->setBargain($bargain);
                $log->setPrice($price);
                $log->setCreateTime(new \DateTime("now"));
                $log->setCreateIp($this->container->get('request')->getClientIp());
                $em->persist($bargain);
                $em->persist($log);
                $em->flush();
                $em->getConnection()->commit();
            }
        } catch (Exception $e) {
            // Rollback the failed transaction attempt
            $em->getConnection()->rollback();
            $return['ret'] = 1100;
            $return['msg'] = $e->getMessage();
        }
        
        
        return new Response(json_encode($return));
    }
    /**
     * @Route("/info/{bargain_id}/{store_id}/{user_id}", name="_bargain_info")
     */
    public function bargainInfoAction(Request $request, $bargain_id, $store_id,$user_id=null)
    {
        $user = $this->getUser();
        $bargain = $this->getDoctrine()->getRepository('AppBundle:Bargain')->find($bargain_id);
        $logs = $this->getDoctrine()->getRepository('AppBundle:BargainLog')->findBy( array('bargain'=>$bargain));
        $discount_price = preg_replace('/\.$/U', '', rtrim($bargain->getPrice(), '0'));
        $discount_price = preg_replace('/\d{1}/i', '<span>$0</span>', $discount_price);
        return $this->render('AppBundle:default:bargainInfo.html.twig',array(
            //'item' => $item,
            'bargain' => $bargain,
            'discount_price' => $discount_price,
            'logs'=>$logs,
            'user'=>$user,
        ));
    }
    /**
     * @Route("callback/", name="_callback")
     */
    public function callbackAction(Request $request)
    {
        $session = $request->getSession();
        $code = $request->query->get('code');
        //$state = $request->query->get('state');
        $app_id = $this->container->getParameter('wechat_appid');
        $secret = $this->container->getParameter('wechat_secret');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $app_id . "&secret=" . $secret . "&code=$code&grant_type=authorization_code";
        $data = Helper\HttpClient::get($url);
        $token = json_decode($data);
        //$session->set('open_id', null);
        if ( isset($token->errcode) && $token->errcode != 0) {
            return new Response('something bad !');
        }

        $wechat_token = $token->access_token;
        $wechat_openid = $token->openid;
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$wechat_token}&openid={$wechat_openid}";
        $data = Helper\HttpClient::get($url);
        $user_data = json_decode($data);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $session->set('open_id', $user_data->openid);
            $repo = $em->getRepository('AppBundle:WechatUser');
            $qb = $repo->createQueryBuilder('a');
            $qb->select('COUNT(a)');
            $qb->where('a.openId = :openId');
            $qb->setParameter('openId', $user_data->openid);
            $count = $qb->getQuery()->getSingleScalarResult();
            if($count <= 0){
                $wechat_user = new Entity\WechatUser();
                $wechat_user->setOpenId($wechat_openid);
                $wechat_user->setNickName($user_data->nickname);
                $wechat_user->setCity($user_data->city);
                $wechat_user->setGender($user_data->sex);
                $wechat_user->setProvince($user_data->province);
                $wechat_user->setCountry($user_data->country);
                $wechat_user->setHeadImg($user_data->headimgurl);
                $wechat_user->setCreateIp($request->getClientIp());
                $wechat_user->setCreateTime(new \DateTime('now'));
                $em->persist($wechat_user);
                $em->flush();
            }
            else{
                $wechat_user = $em->getRepository('AppBundle:WechatUser')->findOneBy(array('openId' => $wechat_openid));
                $wechat_user->setHeadImg($user_data->headimgurl);
                $em->persist($wechat_user);
                $em->flush();
                $session->set('user_id', $wechat_user->getId());
            }

            $redirect_url = $session->get('redirect_url') == null ? $this->generateUrl('_index') : $session->get('redirect_url');
            $em->getConnection()->commit();
            return $this->redirect($redirect_url);
        }
        catch (Exception $e) {
            $em->getConnection()->rollback();
            return new Response($e->getMessage());
        }
    }
}
