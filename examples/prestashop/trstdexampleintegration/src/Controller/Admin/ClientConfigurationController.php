<?php

declare(strict_types=1);

namespace TRSTDExampleIntegration\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ClientConfigurationController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $textFormDataHandler = $this->get('trstdexampleintegration.form.client_configuration_text_form_data_handler');

        $textForm = $textFormDataHandler->getForm();
        $textForm->handleRequest($request);

        if ($textForm->isSubmitted() && $textForm->isValid()) {
            /** You can return array of errors in form handler and they can be displayed to user with flashErrors */
            $errors = $textFormDataHandler->save($textForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('client_configuration_form');
            }

            $this->flashErrors($errors);
        }

        return $this->render('@Modules/trstdexampleintegration/views/templates/admin/form.html.twig', [
            'clientConfigurationForm' => $textForm->createView(),
        ]);
    }
}
