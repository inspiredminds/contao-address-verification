<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoAddressVerification\Model\AddressModel;

$GLOBALS['BE_MOD']['content']['address_verification'] = [
    'tables' => ['tl_address_group', 'tl_address'],
];

$GLOBALS['TL_MODELS']['tl_address'] = AddressModel::class;
