<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification;

use Contao\Controller;
use Contao\Model\Collection;
use Contao\System;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;

trait ProcessAddressesTrait
{
    private function processAddresses(?Collection $addresses, bool $includeCountry = false): array
    {
        if (!$addresses) {
            return [];
        }

        Controller::loadDataContainer('tl_address');
        $dcaFields = $GLOBALS['TL_DCA']['tl_address']['fields'];
        $countries = $includeCountry ? System::getCountries() : [];

        return array_map(
            function (AddressModel $address) use ($dcaFields, $includeCountry, $countries) {
                $row = $address->row();

                foreach ($row as $key => $value) {
                    if (empty($dcaFields[$key]['inputType'])) {
                        unset($row[$key]);
                    }
                }

                // Build a human readable address for the autocomplete selection
                $row['address'] = $address->getReadableAddress($includeCountry, $countries);

                return $row;
            }, $addresses->getModels()
        );
    }
}
