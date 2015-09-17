<?php
namespace AppBundle\Controller;
use AppBundle\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;

class AdminUserController extends Controller
{
    /**
     * @Route("/admin/user",name="admin_user")
     */
    public function userAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $query = $repository->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->getQuery();

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $query,
            $request->query->get('page', 1),/*page number*/
            30/*limit per page*/
        );
        //$pagination->setTemplate('admin/pagination.html.twig');
        return $this->render('AppBundle:admin:user.html.twig', array('pagination' => $pagination));
    }
    /**
     * @Route("admin/user/add/",name="admin_user_add")
     */
    public function userAddAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = new Entity\User();
        $rows = $em->getRepository('AppBundle:Store')->findAll();
        $stores = array();
        foreach($rows as $row){
            $stores[$row->getId()] = $row->getTitle();
        }
        $form = $this->createFormBuilder($user, array('validation_groups'=>array('Default','add')))
            ->add('username', 'text')
            ->add('password', 'password')
            ->add('email', 'text')
            ->add('isActive', 'choice', array(
                'choices'=>array(0=>'禁用',1=>'正常')
            ))
            ->add('adminStores', 'choice', array(
                'choices'=>$stores,
                'multiple'=>true,
                'expanded'=>true,
            ))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $response = array(
                'ret' => 0,
                'msg' => '操作成功，<a href="'.$this->generateUrl('admin_user').'" class="btn btn-info">点击</a>返回列表或者继续<a href="'.$this->generateUrl('admin_user_add').'" class="btn btn-info">添加</a>。',
                'retUrl' => $this->generateUrl('admin_user')
            );
            $errors = array();
            foreach ($form as $fieldName => $formField) {
                foreach ($formField->getErrors(true) as $error) {
                    $errors[] = array($fieldName, $error->getMessage());
                }
            }
            if( count($errors) > 0){
                $response['ret'] = 1001;
                $response['errors'] = $errors;
            }
            else{
                $data = $form->getData();
                $user = new Entity\User();
                $user->setEmail($data->getEmail());
                $user->setUsername($data->getUsername());
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($data->getPassword(), $user->getSalt());
                $user->setPassword($password);
                $user->setIsActive($data->getIsActive());
                $user->setAdminStores(implode(',',$data->getAdminStores()));
                $user->setCreateTime(new \DateTime('now'));
                $user->setCreateIp($request->getClientIp());
                $user->setLastUpdateTime(new \DateTime('now'));
                $user->setLastUpdateIp($request->getClientIp());
                $role = $em->getRepository('AppBundle:Role')->find($request->get('form')['group']);
                $user->addRole($role);
                $em->persist($user);
                $em->flush();
            }
            return new JsonResponse($response);
        }
        return $this->render('AppBundle:admin:userForm.html.twig', array(
            'form' => $form->createView(),
            'pageTitle' => '新增用户',
            'page' => null,
            'roles' => $em->getRepository('AppBundle:Role')->findAll(),
            'roleId' => null,
        ));
    }
    /**
     * @Route("admin/user/edit/{id}",name="admin_user_edit")
     */
    public function userEdit(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);
        $password = $user->getPassword();
        $rows = $em->getRepository('AppBundle:Store')->findAll();
        $stores = array();
        foreach($rows as $row){
            $stores[$row->getId()] = $row->getTitle();
        }

        $form = $this->createFormBuilder($user)
            ->add('username', 'text')
            ->add('password', 'password')
            ->add('email', 'text')
            ->add('isActive', 'choice', array(
                'choices'=>array(0=>'禁用',1=>'正常')
            ))
            ->add('adminStores', 'choice', array(
                'choices'=>$stores,
                'multiple'=>true,
                'expanded'=>true,
                'data'=>explode(',', $user->getAdminStores())
            ))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            $response = array(
                'ret' => 0,
                'msg' => '操作成功，<a href="'.$this->generateUrl('admin_user').'" class="btn btn-info">点击</a>返回列表。',
                'retUrl' => $this->generateUrl('admin_user')
            );
            $errors = array();
            foreach ($form as $fieldName => $formField) {
                foreach ($formField->getErrors(true) as $error) {
                    $errors[] = array($fieldName, $error->getMessage());
                }
            }
            if( count($errors) > 0){
                $response['ret'] = 1001;
                $response['errors'] = $errors;
            }
            else{
                $data = $form->getData();
                $user->setEmail($data->getEmail());
                $user->setUsername($data->getUsername());

                if( $data->getPassword() !== null){
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($data->getPassword(), $user->getSalt());
                }
                $user->setPassword($password);

                $user->setIsActive($data->getIsActive());
                if( isset($request->get('form')['adminStores']) && is_array($request->get('form')['adminStores']))
                    $adminStores = implode(',', $request->get('form')['adminStores']);
                else
                    $adminStores = '';
                $user->setAdminStores($adminStores);
                foreach($user->getRoles() as $role){
                    $user->removeRole($role);
                }
                $role = $em->getRepository('AppBundle:Role')->find($request->get('form')['group']);
                //var_dump($request->get('form')['group']);
                $user->addRole($role);
                $em->persist($user);
                $em->flush();
            }
            return new JsonResponse($response);
        }
        $roleId = null;
        foreach($user->getRoles() as $role){
            $roleId = $role->getId();
            break;
        }
        return $this->render('AppBundle:admin:userForm.html.twig', array(
            'form' => $form->createView(),
            'pageTitle' => '用户修改',
            'roles' => $em->getRepository('AppBundle:Role')->findAll(),
            'page' => null,
            'roleId' => $roleId,
        ));
    }
    /**
     * @Route("admin/user/delete/{id}",name="admin_user_delete")
     */
    public function actionUserDelete(Request $request, $id)
    {
        $response = array(
            'ret' => 0,
            'msg' => '',
        );
        try{
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->find($id);
            $em->remove($user);
            $em->flush();
        }
        catch(Exception $e){
            $response = array(
                'ret' => 1001,
                'msg' => $e->getMessage(),
            );
        }

        return new JsonResponse($response);
    }
}