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

$GLOBALS['TL_DCA']['tl_module']['palettes'][AddressVerificationController::TYPE] = '{title_legend},name,headline,type;{address_verification_legend},address_groups,address_country;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
