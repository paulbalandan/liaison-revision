<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Paths;

/**
 * Interface for all pathfinders to implement.
 */
interface PathfinderInterface
{
    /**
     * Retrieves the array of paths to be used
     * in updating. Each array value must be an array
     * with the keys `origin` and `destination`
     * and string values.
     *
     * @return array
     */
    public function getPaths(): array;
}
