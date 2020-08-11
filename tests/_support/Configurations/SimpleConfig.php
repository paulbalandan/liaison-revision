<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Support\Configurations;

use Liaison\Revision\Config\Revision;

class SimpleConfig extends Revision
{
    public $ignoredDirs = [
        'app/Config',
    ];

    public $ignoredFiles = [
        'app/.htaccess',
    ];
}
