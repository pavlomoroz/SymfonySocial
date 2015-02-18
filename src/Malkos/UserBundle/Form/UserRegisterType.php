<?php

namespace Malkos\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text');
        $builder->add('email', 'email');
        $builder->add('service_service', 'hidden');
        $builder->add('service_id', 'hidden');
        $builder->add('service_token', 'hidden');

        $builder->add('Register', 'submit');
    }

    public function getName()
    {
        return 'user';
    }
}