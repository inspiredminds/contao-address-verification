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

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $name
 * @property string $nodes
 */
class AddressGroupModel extends Model
{
    protected static $strTable = 'tl_address_group';
}
