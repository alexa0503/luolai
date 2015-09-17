<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class StoreType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', 'text', array(
				'label' => '店铺名称',
			))
			->add('pageHeaderImg', 'file', array(
				'label' => '页面头部图片',
				'required' => false,
				'data_class' => null
			))
			->add('storeName', 'text', array(
				'label' => '店名',
			))
			->add('address', 'text', array(
				'label' => '店铺地址',
			))
			->add('tel', 'text', array(
				'label' => '店铺电话',
			))
			->add('info', 'textarea', array(
				'label' => '店铺公告',
			))
			->add('description', 'text', array(
				'label' => '活动时间',
			))
			->add('wxTitle', 'text', array(
				'label' => '微信分享标题',
			))
			->add('wxDesc', 'text', array(
				'label' => '微信分享描述',
			))
			->add('wxImg', 'file', array(
				'label' => '微信分享图片',
				'data_class' => null,
				'required' => false,
			))
			->add('password', 'text', array(
				'label' => '店铺密码',
			))
			->add('isActive', 'choice', array(
				'choices'=>array(0=>'禁用',1=>'正常'),
				'label' => '状态',
			))
			->add('save', 'submit', array('label' => '保存'))
		;
	}
	public function getName()
	{
		return 'store';
	}
}