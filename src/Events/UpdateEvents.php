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

namespace Liaison\Revision\Events;

/**
 * `UpdateEvents` contains all events dispatched by the Application.
 *
 * All listeners should accept as their sole argument
 * the Application class. This allows you to introspect
 * through the application at a specific stage and access
 * to its properties at those points.
 */
final class UpdateEvents
{
    /**
     * The INITIALIZE event is triggered after the application
     * has booted and initialized all required classes and set
     * the workspace directory.
     */
    public const INITIALIZE = 'revision.initialize';

    /**
     * The PREFLIGHT event is called before the application
     * checks on the preflight conditions, i.e. before creating
     * a backup of the current vendor snapshot.
     */
    public const PREFLIGHT = 'revision.preflight';

    /**
     * The PREUPGRADE event is called before the application
     * calls the updateInternals method.
     */
    public const PREUPGRADE = 'revision.preupgrade';

    /**
     * The POSTUPGRADE event is called after the application
     * has analysed the modifications brought by the update.
     */
    public const POSTUPGRADE = 'revision.postupgrade';

    /**
     * The PRECONSOLIDATE event is called before the application
     * calls the current consolidator to manage the consolidation.
     */
    public const PRECONSOLIDATE = 'revision.preconsolidate';

    /**
     * The POSTCONSOLIDATE event is called after the application
     * has analysed and resolved the merges and conflicts brought
     * by the consolidation process.
     */
    public const POSTCONSOLIDATE = 'revision.postconsolidate';

    /**
     * The TERMINATE event is called before the application
     * will terminate the current process.
     */
    public const TERMINATE = 'revision.terminate';
}
