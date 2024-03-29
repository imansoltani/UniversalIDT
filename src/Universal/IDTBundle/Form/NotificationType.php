<?php

namespace Universal\IDTBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NotificationType extends AbstractType
{
    /** @var array */
    private $notifications;

    public function __construct(array $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notifications', 'choice', array(
                    'choices' => $this->notifications,
                    'expanded' => true,
                    'multiple' => true,
                        'required' => false,
                    'label' => 'settings.notifications.notifications',
                    'translation_domain' => 'application'
                ))
            ->add('submit', 'submit', array(
                    "label"=>"settings.notifications.btn_update",
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
        return 'universal_idt_notifications';
    }
}
