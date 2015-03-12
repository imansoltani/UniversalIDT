<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RatesType extends AbstractType
{
    private $countries;
    private $destinations;

    public function __construct($countries, $destinations)
    {
        $this->countries = $countries;
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
                    'choices' => $this->countries,
                    'placeholder' => 'Select Country',
                ))
            ->add('destination', 'choice', array(
                    'choices' => $this->destinations,
                    'placeholder' => 'Select Destination',
                ))
            ->add('type', 'choice', array(
                    'choices' => array('LAC'=>'Local Access', 'TF'=>'Toll Free', 'INT'=>'Internet'),
                    'expanded' => true
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
