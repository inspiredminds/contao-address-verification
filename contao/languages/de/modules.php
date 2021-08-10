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

$GLOBALS['TL_LANG']['MOD']['address_verification'] = ['Adressen', 'Adressen für die Adress-Verifikation verwalten.'];
$GLOBALS['TL_LANG']['MOD']['tl_address_group'] = 'Adress-Gruppen';
$GLOBALS['TL_LANG']['MOD']['tl_address'] = 'Adressen';

$GLOBALS['TL_LANG']['FMD'][AddressVerificationController::TYPE] = ['Adress-Verfikation', 'Erlaubt es Website-Besuchern eine Adresse zu verifizieren. Die Adresse wird danach in der Session gespeichert.'];
$GLOBALS['TL_LANG']['FMD'][RequireAddressVerificationSessionController::TYPE] = ['Adress-Verfilkations-Session verlangen', 'Erlaubt es eine Weiterleitungsseite zu definieren, zu der weitergeleitet wird, wenn noch keine Adress-Verifikation durchgeführt wurde.'];
