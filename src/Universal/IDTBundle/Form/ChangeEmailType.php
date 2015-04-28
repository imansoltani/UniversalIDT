<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangeEmailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newEmail', 'email', array(
                    "label"=>"settings.email.new",
                    'translation_domain' => 'application'
                ))
            ->add('current_password', 'password', array(
                    "label"=>"settings.email.password",
                    'translation_domain' => 'application',
                    'mapped' => false,
                    'constraints' => new UserPassword(),
                ))
            ->add('submit', 'submit', array(
                    "label"=>"settings.email.btn_change",
                    'translation_domain' => 'application'
                ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Universal\IDTBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'universal_idt_change_email';
    }
}
