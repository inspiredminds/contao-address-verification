<?php

namespace InspiredMinds\ContaoAddressVerification\Controller\Backend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/address-verification/import",
 *   name=AddressImportController::class,
 *   defaults={"_scope": "backend"}
 * )
 */
class AddressImportController
{
    public function __invoke(Request $request): Response
    {
        return new Response();
    }
}
