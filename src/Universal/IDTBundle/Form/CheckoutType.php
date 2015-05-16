<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Universal\IDTBundle\DBAL\Types\PaymentMethodEnumType;

class CheckoutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                    'required' => false,
                    "label"=>"checkout.form.delivery.email",
                    'translation_domain' => 'website',
                ))
//            ->add('sms', 'text', array(
//                    'required' => false
//                ))
            ->add('method', 'choice', array(
                    'choices' => PaymentMethodEnumType::getChoices(),
                    'expanded' => true,
                    'required' => true
                ))
            ->add('agree', 'checkbox', array(
                    'required' => true
                ))
            ->add('submit', 'submit', array(
                    "label"=>"checkout.form.review.order_link",
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
//            'data_class' => 'Universal\IDTBundle\Entity\Product'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'universal_idt_checkout';
    }
}
