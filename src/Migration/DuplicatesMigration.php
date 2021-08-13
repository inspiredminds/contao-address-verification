<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Address Verification extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class DuplicatesMigration extends AbstractMigration
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function shouldRun(): bool
    {
        if (!$this->hasTable()) {
            return false;
        }

        return $this->hasDuplicates();
    }

    public function run(): MigrationResult
    {
        $count = $this->deleteDuplicates();

        return $this->createResult(true, 'Deleted '.$count.' duplicates from tl_address.');
    }

    private function hasTable(): bool
    {
        $sm = $this->db->getSchemaManager();

        return $sm->tablesExist(['tl_address']) && !empty($sm->listTableColumns('tl_address'));
    }

    private function hasDuplicates(): bool
    {
        $result = $this->db->executeQuery(
            'SELECT
                pid, COUNT(pid),
                street, COUNT(street),
                number, COUNT(number),
                apartment, COUNT(apartment),
                postal, COUNT(postal),
                city, COUNT(city),
                country, COUNT(country)
            FROM
                tl_address
            GROUP BY
                pid,
                street,
                number,
                apartment,
                postal,
                city,
                country
            HAVING
                (COUNT(pid) > 1) AND
                (COUNT(street) > 1) AND
                (COUNT(number) > 1) AND
                (COUNT(apartment) > 1) AND
                (COUNT(postal) > 1) AND
                (COUNT(city) > 1) AND
                (COUNT(country) > 1)
            ;'
        );

        return $result->rowCount() > 0;
    }

    private function deleteDuplicates(): int
    {
        $result = $this->db->executeQuery(
            'DELETE t1 FROM tl_address t1
            INNER JOIN tl_address t2
            WHERE
                t1.id < t2.id AND
                t1.pid = t2.pid AND
                t1.street = t2.street AND
                t1.number = t2.number AND
                t1.apartment = t2.apartment AND
                t1.postal = t2.postal AND
                t1.city = t2.city AND
                t1.country = t2.country
            ;'
        );

        return $result->rowCount();
    }
}
