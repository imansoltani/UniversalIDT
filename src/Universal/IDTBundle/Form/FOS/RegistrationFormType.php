<?php
namespace Universal\IDTBundle\Form\FOS;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Universal\IDTBundle\Entity\User;

class RegistrationFormType extends AbstractType
{
    private $locales;
    private $countries;

    public function __construct(array $locales, array $countries)
    {
        $this->locales = array_flip($locales);
        $this->countries = $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text')
            ->add('lastName', 'text')
            ->add('country', 'choice', array(
                    'choices' => $this->countries
                ))
            ->add('language', 'choice', array(
                'choices' => $this->locales
            ))
            ->add('gender', 'choice', array(
                'choices' => array('M'=>'Male', 'F'=> 'Female')
            ))
            ->add('phone', "text")
            ->remove('username')
            ->addEventListener(FormEvents::SUBMIT,function(FormEvent $event){
                    $data = $event->getData();
//                    $form = $event->getForm();
                    if (!$data instanceof User) {
                        return;
                    }
                    $data->setUsername($data->getEmail());
                });
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'universal_idt_registration';
    }
}