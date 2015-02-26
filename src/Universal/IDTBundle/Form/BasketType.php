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
                            'login' => 'I have an account',
                            'register' => 'I would like to create an account'
                        ),
                        'expanded' => true,
                        'empty_value' => 'I would like to buy without creating account',
                        'required' => false
                    ));

        $builder
            ->add('submit', 'submit', array(
                    'label' => 'Checkout'
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
