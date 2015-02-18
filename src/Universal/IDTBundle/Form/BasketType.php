<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Universal\IDTBundle\Entity\User;

class BasketType extends AbstractType
{
    private $user;

    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->user === null)
            $builder
                ->add('account', 'choice', array(
                        'choices' => array(
                            'login' => 'I have an Account',
                            'register' => 'I want have an Account'
                        ),
                        'expanded' => true,
                        'empty_value' => 'None',
                        'required' => false
                    ));

        $builder
            ->add('submit', 'submit', array(
                    'label' => 'Go to checkout'
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
