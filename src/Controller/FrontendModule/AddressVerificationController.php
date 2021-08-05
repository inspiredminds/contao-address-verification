<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(AddressVerificationController::TYPE, category="address_verification")
 */
class AddressVerificationController extends AbstractFrontendModuleController
{
    public const TYPE = 'address_verification';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        return new Response($template->parse());
    }

    private function buildForm($request): Form
    {

    }
}
