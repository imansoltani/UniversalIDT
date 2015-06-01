<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RatesType extends AbstractType
{
    private $origins;
    private $destinations;

    public function __construct($origins, $destinations)
    {
        $this->origins = $origins;
        $this->destinations = $destinations;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'choice', array(
                    'choices' => $this->origins,
                    'placeholder' => "home.rates.call.from_placeholder",
                    "label"=>"home.rates.call.from",
                    'translation_domain' => 'website',
                ))
            ->add('destination', 'choice', array(
                    'choices' => $this->destinations,
                    'placeholder' => "home.rates.call.to_placeholder",
                    "label"=>"home.rates.call.to",
                    'translation_domain' => 'website',
                ))
            ->add('type', 'choice', array(
                    'choices' => array('LAC' => 'home.rates.type.lac', 'TF' => 'home.rates.type.tf', 'INT' => 'home.rates.type.internet'),
                    'expanded' => true,
                    'translation_domain' => 'website',
                ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false
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
