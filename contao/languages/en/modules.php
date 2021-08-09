<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoAddressVerification\Controller\FrontendModule\AddressVerificationController;
use InspiredMinds\ContaoAddressVerification\Controller\FrontendModule\RequireAddressVerificationSessionController;

$GLOBALS['TL_LANG']['MOD']['address_verification'] = ['Addresses', 'Manage addresses for address verification.'];
$GLOBALS['TL_LANG']['MOD']['tl_address_group'] = 'Address groups';
$GLOBALS['TL_LANG']['MOD']['tl_address'] = 'Addresses';

$GLOBALS['TL_LANG']['FMD'][AddressVerificationController::TYPE] = ['Address verification', 'Allows the front end user to check an address. The address will be saved in the session.'];
$GLOBALS['TL_LANG']['FMD'][RequireAddressVerificationSessionController::TYPE] = ['Require address verification session', 'Allows you to define a redirect page, if no address verification session was started.'];
