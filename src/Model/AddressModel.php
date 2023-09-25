<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Model;

use Contao\Model;
use Contao\Model\Collection;
use Contao\System;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $street
 * @property string $number
 * @property string $apartment
 * @property string $postal
 * @property string $city
 * @property string $country
 */
class AddressModel extends Model
{
    protected static $strTable = 'tl_address';

    public static function findByPids(array $pids, $options = []): ?Collection
    {
        $t = static::getTable();
        $columns = ["$t.pid IN(".implode(',', array_map('\intval', $pids)).')'];

        return static::findBy($columns, null, $options);
    }

    public function getReadableAddress(bool $includeCountry = false, array $countries = []): string
    {
        if ($includeCountry && !$countries) {
            $countries = System::getCountries();
        }

        return implode(', ', array_filter([
            implode(' ', array_filter([
                $this->street,
                implode('/', array_filter([
                    $this->number,
                    $this->apartment,
                ])),
            ])),
            implode(' ', array_filter([
                $this->postal,
                $this->city,
            ])),
            $includeCountry ? ($countries[$this->country] ?? null) : null,
        ]));
    }
}
