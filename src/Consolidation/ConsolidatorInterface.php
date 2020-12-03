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

namespace Liaison\Revision\Consolidation;

/**
 * Interface for consolidators.
 */
interface ConsolidatorInterface
{
    /**
     * Handles the merging of newly created files from source.
     *
     * @return ConsolidatorInterface
     */
    public function mergeCreatedFiles();

    /**
     * Handles the merging of modified files from source.
     *
     * @return ConsolidatorInterface
     */
    public function mergeModifiedFiles();

    /**
     * Handles the merging of deleted files from source.
     *
     * @return ConsolidatorInterface
     */
    public function mergeDeletedFiles();
}
