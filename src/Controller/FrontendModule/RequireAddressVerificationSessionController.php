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
use Contao\PageModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(RequireAddressVerificationSessionController::TYPE, category="address_verification")
 */
class RequireAddressVerificationSessionController extends AbstractFrontendModuleController
{
    public const TYPE = 'require_address_verification';

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null): Response
    {
        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->getBackendWildcard($model);
        }

        if (!$request->hasSession()) {
            return new Response();
        }

        $session = $request->getSession();

        if ($session->has('address-verification')) {
            return new Response();
        }

        $jumpTo = PageModel::findByPk($model->jumpTo);

        if (null === $jumpTo) {
            return new Response();
        }

        return new RedirectResponse($jumpTo->getAbsoluteUrl());
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        return new Response();
    }
}
