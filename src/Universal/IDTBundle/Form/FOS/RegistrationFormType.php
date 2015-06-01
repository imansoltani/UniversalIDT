<?php
namespace Universal\IDTBundle\Form\FOS;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Universal\IDTBundle\Entity\User;

class RegistrationFormType extends AbstractType
{
    private $locales;
    private $countries;
    private $request;

    public function __construct(array $locales, array $countries, Request $request)
    {
        $this->locales = array_flip($locales);
        $this->countries = $countries;
        $this->request = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
                    "label"=>"registration.first_name",
                    'translation_domain' => 'website'
                ))
            ->add('lastName', 'text', array(
                    "label"=>"registration.last_name",
                    'translation_domain' => 'website'
                ))
            ->add('country', 'choice', array(
                    "label"=>"registration.country",
                    'placeholder' => 'registration.country_placeholder',
                    'translation_domain' => 'website',
                    'choices' => $this->countries
                ))
            ->add('language', 'choice', array(
                'choices' => $this->locales,
                'preferred_choices' => array($this->request->get('_locale')),
                "label"=>"registration.language",
                'translation_domain' => 'website'
            ))
            ->add('gender', null, array(
                    "label"=>"registration.gender",
                    'translation_domain' => 'website'
                ))
            ->add('phone', "text", array(
                    'required' => false,
                    "label"=>"registration.phone",
                    'translation_domain' => 'website'
                ))
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