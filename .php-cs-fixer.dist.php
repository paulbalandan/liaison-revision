<?php

declare(strict_types=1);

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Nexus\CsConfig\Factory;
use Nexus\CsConfig\Ruleset\Nexus73;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([__DIR__])
    ->exclude(['build', 'mock'])
    ->append([__FILE__])
;

$overrides = [];
$options = [
    'finder' => $finder,
    'cacheFile' => 'build/.php-cs-fixer.cache',
];

return Factory::create(new Nexus73(), $overrides, $options)->forLibrary(
    'Liaison Revision',
    'John Paul E. Balandan, CPA',
    'paulbalandan@gmail.com',
    2020,
);
