<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Consolidation;

use Liaison\Revision\Files\FileManager;

class DefaultConsolidator implements ConsolidatorInterface
{
    /**
     * Path to workspace directory.
     *
     * @var string
     */
    protected $workspace;

    /**
     * Instance of FileManager.
     *
     * @var \Liaison\Revision\Files\FileManager
     */
    protected $fileManager;

    /**
     * Constructor.
     *
     * @param string                              $workspace
     * @param \Liaison\Revision\Files\FileManager $fileManager
     */
    public function __construct(string $workspace, FileManager $fileManager)
    {
        $this->workspace   = $workspace;
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeCreatedFiles()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function mergeDeletedFiles()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function mergeModifiedFiles()
    {
    }
}
