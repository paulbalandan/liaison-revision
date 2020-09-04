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

    // UpdateCommand
    'loadedConfigurationSettings' => 'Loaded configuration settings from: {0}.',
    'accessAllowed'               => 'Allowed',
    'accessDenied'                => 'Denied',
    'startUpdateText'             => 'Starting software updates...',
    'stopUpdateText'              => 'Software updates finished in {0}.',
    'logsLocationMessage'         => 'Logs for this run can be found here:',
    'seconds'                     => '{0} seconds',
    'minutes'                     => '{0} minutes',
    'hours'                       => '{0} hours',
    'filesCreatedLabel'           => 'Created',
    'filesModifiedLabel'          => 'Modified',
    'filesDeletedLabel'           => 'Deleted',
    'proceedAction'               => 'Proceed.',
    'abortAction'                 => 'Abort.',
    'confirmQuestionPrompt'       => 'What shall I do?',
    'emptyFilesToConsolidate'     => 'No files to consolidate!',
    'someFilesToConsolidate'      => 'Found {0} {1} to consolidate.',
    'listAllToConsolidate'        => 'List all files to consolidate.',
    'listCreatedFilesOnly'        => 'List created files only ({0}).',
    'listModifiedFilesOnly'       => 'List modified files only ({0}).',
    'listDeletedFilesOnly'        => 'List deleted files only ({0}).',
    'someFilesInConflict'         => 'Found {0} {1} in conflict.',
    'listAllInConflict'           => 'List all files in conflict.',
    'conflictsCreatedFile'        => 'This file was newly added from source but similar file was found in yours.',
    'conflictsModifiedFile'       => 'This file was modified from source and does not match with your file.',
    'conflictsDeletedFile'        => 'This file has been deleted from source.',
    'conflictsOverwriteAll'       => 'Overwrite all.',
    'conflictsOverwriteOne'       => 'Overwrite file in destination.',
    'conflictsSkipAll'            => 'Skip all.',
    'conflictsSkipOne'            => 'Skip this file.',
    'conflictsEachResolve'        => 'Resolve each conflict.',
    'conflictsDisplayDiff'        => 'Display diff.',
    'emptyFilesToRender'          => 'No files to render!',
    'renderFileLabel'             => 'File',
    'renderStatusLabel'           => 'Status',
    'renderDiffLabel'             => 'Diff',

    // Logs
    'invalidLogHandler'   => 'Log handler "{0}" is not an instance of "{1}". Got instance of "{2}" instead.',
    'cannotUseLogHandler' => 'Cannot use "{0}" as "{1}" is not installed.',

    // Pathfinder
    'invalidOriginPathFound'   => '"{0}" is not a valid origin file or directory.',
    'invalidAbsolutePathFound' => '"{0}" must be a relative path.',
    'invalidRelativePathFound' => '"{0}" must be an absolute path.',
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
    'terminateExecutionSuccess'          => 'Terminating: Application update was successful.',
    'terminateExecutionFailure'          => 'Terminating: Application errored on "{0}" event.',
];
