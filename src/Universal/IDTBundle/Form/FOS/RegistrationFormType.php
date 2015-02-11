<?php
namespace Universal\IDTBundle\Form\FOS;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
    private $locales;

    public function __construct(array $locales)
    {
        $this->locales = array_combine($locales, $locales);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('language', 'choice', array(
                'choices' => $this->locales
            ));
        $builder->add('gender', 'choice', array(
                'choices' => array('M'=>'Male', 'F'=> 'Female')
            ));
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