<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    // GitignoreCommand
    'mutExOptionsForWriteGiven'     => 'Cannot have both "-{0}" and "-{1}" options present.',
    'gitignoreWriteDenied'          => '{0} is not allowed to write entries to `.gitignore`.',
    'gitignoreFileMissing'          => '`.gitignore` is missing.',
    'createGitignoreFile'           => 'Create a new .gitgnore file?',
    'createGitignoreEntryFail'      => 'Failed creating entry in .gitignore.',
    'createGitignoreEntryDuplicate' => 'There was already an entry in .gitignore.',
    'createGitignoreEntrySuccess'   => 'Successfully created an entry to .gitignore.',

    // Logs
    'invalidLogHandler' => 'Log handler "{0}" is not an instance of "{1}". Got instance of "{2}" instead.',

    // Paths
    'invalidOriginPathFound'   => '"{0}" is not a valid origin file or directory.',
    'invalidAbsolutePathFound' => '"{0}" must be a relative path.',
    'invalidPathNotDirectory'  => '"{0}" is not a valid directory.',
    'invalidPathNotFile'       => '"{0}" is not a valid file.',

    // Upgrade
    'incompatibleUpgraderHandler' => 'Cannot use {0} as upgrader: "{1}".',

    // Application
    'fileSingular'                       => 'file',
    'filePlural'                         => 'files',
    'createdFilesAfterUpdate'            => '{0} created {1} after update.',
    'modifiedFilesAfterUpdate'           => '{0} modified {1} after update.',
    'deletedFilesAfterUpdate'            => '{0} deleted {1} after update.',
    'mergedFilesAfterConsolidation'      => '{0} {1} merged successfully.',
    'conflictingFilesAfterConsolidation' => '{0} {1} in conflict.',
];
