<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_address_group'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_address'],
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
            'flag' => 1,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_address',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'href' => 'act=edit',
                'icon' => 'header.svg',
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
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
    ],
    'palettes' => [
        'default' => '{address_group_legend},name',
    ],
];
