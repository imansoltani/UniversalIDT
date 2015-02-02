<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Universal\IDTBundle\Entity\Product;

/**
 * Class DenominationsType
 * @package Universal\IDTBundle\Form
 */
class DenominationsType extends AbstractType
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('denominations', 'choice', array(
                    "choices" => array()
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Universal\IDTBundle\Entity\Product'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
