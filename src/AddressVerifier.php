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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use InspiredMinds\ContaoAddressVerification\Model\AddressModel;

class AddressVerifier
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param array<int> $addressGroupIds
     */
    public function verify(AddressModel $address, array $addressGroupIds, bool $includeCountry = false): bool
    {
        $where = [
            'street = ?',
            'number = ?',
            'postal = ?',
        ];

        $values = [
            $address->street ?: '',
            $address->number ?: '',
            $address->postal ?: '',
        ];

        // If the address defined an apartment, it should verify if the database has none.
        if (!empty($address->apartment)) {
            $where[] = "(apartment = ? OR apartment = '')";
            $values[] = $address->apartment;
        } else {
            $where[] = "apartment = ''";
        }

        if ($includeCountry) {
            $where[] = 'country = ?';
            $values[] = $address->country;
        }

        // Set string types for values so far
        $types = array_fill(0, \count($values), ParameterType::STRING);

        // Add parent IDs
        $where[] = 'pid IN (?)';
        $values[] = $addressGroupIds;
        $types[] = Connection::PARAM_INT_ARRAY;

        $sql = 'SELECT * FROM tl_address WHERE '.implode(' AND ', $where);

        $record = $this->db->fetchAssociative($sql, $values, $types);

        if (false === $record) {
            return false;
        }

        $address->preventSaving(false);
        $address->setRow($record);

        return true;
    }
}
