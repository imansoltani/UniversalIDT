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
                            'login' => 'home.basket.form.account.has',
                            'register' => 'home.basket.form.account.wants'
                        ),
                        'expanded' => true,
                        'empty_value' => 'home.basket.form.account.guest',
                        'required' => false,
                        'translation_domain' => 'website'
                    ));

        $builder
            ->add('submit', 'submit', array(
                    "label"=>"home.basket.form.checkout",
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
