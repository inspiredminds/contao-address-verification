<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Controller;

use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Contao\StringUtil;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;
use InspiredMinds\ContaoAddressVerification\ProcessAddressesTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/_address-verification/addresses/{module}/{_locale}", name=AddressController::class)
 */
class AddressController
{
    use ProcessAddressesTrait;

    private $framework;
    private $entityCacheTags;

    public function __construct(ContaoFramework $framework, EntityCacheTags $entityCacheTags)
    {
        $this->framework = $framework;
        $this->entityCacheTags = $entityCacheTags;
    }

    public function __invoke(int $module): Response
    {
        $this->framework->initialize();

        if (!($moduleModel = ModuleModel::findById($module))) {
            throw new BadRequestHttpException('Invalid module ID.');
        }

        $addressGroupIds = StringUtil::deserialize($moduleModel->address_groups, true);

        $addresses = AddressModel::findByPids($addressGroupIds);

        $response = new JsonResponse($this->processAddresses($addresses, (bool) $moduleModel->address_country));
        $response->setSharedMaxAge(2592000);

        $this->entityCacheTags->tagWith($addresses);
        $this->entityCacheTags->tagWith(array_map(static function ($id) { return 'contao.db.tl_address_group.'.$id; }, $addressGroupIds));

        return $response;
    }
}
