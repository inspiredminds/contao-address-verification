<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use Contao\StringUtil;
use Contao\System;
use InspiredMinds\ContaoAddressVerification\Controller\Backend\AddressImportController;

$GLOBALS['TL_DCA']['tl_address'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'ptable' => 'tl_address_group',
        'markAsCopy' => 'number',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,street,number,apartment,postal,city,country' => 'unique',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['street'],
            'headerFields' => ['name'],
            'panelLayout' => 'filter;search,limit',
            'child_record_callback' => function (array $row) {
                return '<div class="tl_content_left">'.$row['street'].' '.$row['number'].($row['apartment'] ? '/'.$row['apartment'] : '').', '.$row['postal'].' '.$row['city'].'</div>';
            },
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.svg',
            ],
            'cut' => [
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
        'global_operations' => [
            'import' => [
                'icon' => 'tablewizard.svg',
                'button_callback' => function (?string $href, string $label, string $title, string $class, string $attributes): string {
                    $href = System::getContainer()->get('router')->generate(AddressImportController::class, ['groupId' => Input::get('id')]);

                    return '<a href="'.$href.'" class="'.$class.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
                },
            ],
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'foreignKey' => 'tl_address_group.name',
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'street' => [
            'search' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'number' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'apartment' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'postal' => [
            'search' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'city' => [
            'search' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'country' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'options_callback' => static function () {
                return System::getCountries();
            },
            'sql' => ['type' => 'string', 'length' => 2, 'default' => ''],
        ],
    ],
    'palettes' => [
        'default' => '{address_legend},street,number,apartment,postal,city,country',
    ],
];
