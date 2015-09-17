<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class ItemType extends AbstractType
{
	private $admin_stores;
	public function __construct($admin_stores) {
		//$this->em = $entityManager;
		$this->admin_stores = $admin_stores;
		var_dump($admin_stores);
	}
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('orderId', 'text', array(
				'label' => '排序ID',
			))
			->add('name', 'text', array(
				'label' => '商品名',
			));
		if( $this->admin_stores ){
			$builder->add('store', 'entity', array(
				'label' => '店铺',
				'class' => 'AppBundle:Store',
				'query_builder' => function(EntityRepository $er) {
					return $er->createQueryBuilder('u')
						->where('u.id = :id')
						->setParameter('id', $this->admin_stores);
				},
				'property'=>'storeName',
			));
		}
		else{
			$builder->add('store', 'entity', array(
				'label' => '店铺',
				'class' => 'AppBundle:Store',
				'property'=>'storeName',
			));
		}
		$builder->add('imgUrl', 'file', array(
				'label' => '预览图片',
				'required' => false,
				'data_class' => null
			))
			->add('detailImgUrl', 'file', array(
				'label' => '细节图片',
				'required' => false,
				'data_class' => null
			))
			->add('intro', 'textarea', array(
				'label' => '商品说明',
			))
			->add('num', 'text', array(
				'label' => '数量',
			))
			->add('winNum', 'text', array(
				'label' => '已中数量',
			))
			->add('bargainNum', 'text', array(
				'label' => '需砍价次数',
			))
			->add('price', 'text', array(
				'label' => '原价',
			))
			->add('discountPrice', 'text', array(
				'label' => '折扣价',
			))
			/*
			->add('bargainPrice', 'text', array(
				'label' => '可砍价',
			))
			*/
			->add('save', 'submit', array('label' => '保存'))
		;
	}


	public function getName()
	{
		return 'item';
	}
}