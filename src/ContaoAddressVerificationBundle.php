<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Bayern Portal extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoAddressVerification;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoAddressVerificationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
