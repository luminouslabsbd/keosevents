<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Form\OrganizerRegistrationType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('firstname', TextType::class, [
                    'purify_html' => true,
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'Nombre de pila'
                    )
                ])
                ->add('lastname', TextType::class, [
                    'purify_html' => true,
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'Apellido'
                    )
                ])
                ->add('username', TextType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'attr' => array(
                        'placeholder' => 'Nombre de usuario'
                    )
                ])
                ->add('email', EmailType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(),
                    ],
                    'attr' => array(
                        'placeholder' => 'Correo electrónico'
                    )
                ])
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'options' => array(
                        'purify_html' => true,
                        'translation_domain' => 'FOSUserBundle',
                        'attr' => array(
                            'placeholder' => 'Contraseña',
                            'autocomplete' => 'new-password',
                        ),
                    ),
                    'first_options' => array('attr' => array('placeholder' => 'form.password')),
                    'second_options' => array('attr' => array('placeholder' => 'form.password_confirmation')),
                    'invalid_message' => 'fos_user.password.mismatch',
                ))
                ->add('organizer', OrganizerRegistrationType::class)
                ->add('recaptcha', EWZRecaptchaType::class, array(
                    'attr' => array(
                        'options' => array(
                            'theme' => 'light',
                            'type' => 'image',
                            'size' => 'normal',
                        )
                    ),
                    'mapped' => false,
                    'constraints' => array(
                        new RecaptchaTrue(['groups' => 'Registration'])
                    )
                ))
        ;
    }

    public function getParent() {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix() {
        return 'fos_user_registration_form';
    }

}
