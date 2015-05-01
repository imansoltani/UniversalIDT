<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                    "label"=>"home.contact.form.name",
                    'translation_domain' => 'website',
                ))
            ->add('email', 'email', array(
                    "label"=>"home.contact.form.email",
                    'translation_domain' => 'website',
                ))
            ->add('message', 'textarea', array(
                    "label"=>"home.contact.form.message",
                    'translation_domain' => 'website',
                ))
            ->add('send', 'submit', array(
                    "label"=>"home.contact.form.link",
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
        return 'universal_idt_contact';
    }
}
