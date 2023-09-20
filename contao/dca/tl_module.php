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

$GLOBALS['TL_DCA']['tl_module']['fields']['address_groups'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'foreignKey' => 'tl_address_group.name',
    'eval' => ['tl_class' => 'clr', 'mandatory' => true, 'multiple' => true],
    'sql' => ['type' => 'blob', 'notnull' => false],
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['address_country'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['address_show_only_verified_nodes'] = [
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => ['tl_class' => 'w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['address_verified_nodes'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['nodes'];
$GLOBALS['TL_DCA']['tl_module']['fields']['address_verified_nodes']['eval']['mandatory'] = false;
$GLOBALS['TL_DCA']['tl_module']['fields']['address_verified_redirect'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo'];
$GLOBALS['TL_DCA']['tl_module']['fields']['address_unverified_nodes'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['address_verified_nodes'];
$GLOBALS['TL_DCA']['tl_module']['fields']['address_unverified_redirect'] = &$GLOBALS['TL_DCA']['tl_module']['fields']['address_verified_redirect'];

$GLOBALS['TL_DCA']['tl_module']['palettes'][AddressVerificationController::TYPE] = '{title_legend},name,headline,type;{address_verification_legend},address_groups,address_country,address_show_only_verified_nodes;{address_verified_legend},address_verified_nodes,address_verified_redirect;{address_unverified_legend},address_unverified_nodes,address_unverified_redirect;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][RequireAddressVerificationSessionController::TYPE] = '{title_legend},name,type;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests';
