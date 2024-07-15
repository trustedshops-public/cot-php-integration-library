<?php

declare(strict_types=1);

namespace TRSTDExampleIntegration\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ClientConfigurationFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('COT_TS_ID', TextType::class, [
                'label' => $this->trans('TsID', 'Modules.TRSTDExampleIntegration.Admin'),
            ])
            ->add('COT_CLIENT_SECRET', TextType::class, [
                'label' => $this->trans('Client Secret', 'Modules.TRSTDExampleIntegration.Admin'),
            ]);
    }
}
