<?php
namespace Universal\IDTBundle\Form\FOS;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractType
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
            ->add('firstName', 'text', array(
                    "label"=>"settings.profile.first_name",
                    'translation_domain' => 'application'
                ))
            ->add('lastName', 'text', array(
                    "label"=>"settings.profile.last_name",
                    'translation_domain' => 'application'
                ))
            ->add('country', 'choice', array(
                    'choices' => $this->countries,
                    "label"=>"settings.profile.country",
                    'translation_domain' => 'application'
                ))
            ->add('language', 'choice', array(
                'choices' => $this->locales,
                    "label"=>"settings.profile.language",
                    'translation_domain' => 'application'
            ))
            ->add('gender', null, array(
                    "label"=>"settings.profile.gender",
                    'translation_domain' => 'application'
                ))
            ->add('phone', "text", array(
                    "label"=>"settings.profile.phone",
                    'translation_domain' => 'application',
                    'required' => false
                ))
            ->remove('username')
            ->remove('email');
    }

    public function getParent()
    {
        return 'fos_user_profile';
    }

    public function getName()
    {
        return 'universal_idt_profile';
    }
}