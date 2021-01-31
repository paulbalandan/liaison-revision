# Overview

## Introduction

**Liaison Revision ("Revision")** handles the updating of your core CodeIgniter 4 ("CI4") files
beyond the `system/` directory. While using [Composer](https://getcomposer.org) to update your
dependencies, you will get a fresh set of `app/`, `public/`, `system/`, `writable/`, `spark`, and
`env` in your `vendor/` directory. After that, you have to manually check your project's corresponding
`app/`, `public/`, `writable/`, `spark`, and `env` if there are changes during the framework's update.
**Revision** takes this task away from you by doing it in an automated way leaving you only with the
decision whether to accept or not the changes compiled by Revision.

Run only the command `revision:update` in your terminal and Revision will update the framework and
other dependencies and gives you the changes to be made in your project.

## Configuration

Revision is configured to run using the `Liaison\Revision\Config\Revision` class situated in
**`src/Config/Revision.php`**. You can alter the default settings therein by using a simple CLI
command: `revision:config`.

Just run the command in your terminal:

    php spark revision:config

This will create a copy of the library's configuration file into your **`app/Config`** directory. The
resulting copy will have the class name `Config\Revision`.

At startup if Revision cannot locate the class `Config\Revision` it will fallback to using its
default `Liaison\Revision\Config\Revision`.

**NOTE: You SHOULD NOT DIRECTLY modify the core configuration file as these modifications will be
overwritten when the library updates itself. You SHOULD instead run the command to properly create
your own copy.**

For the detailed discussion of the available settings, please see the
[Configuration](configuration.md) page.

## Localisation

The labels and system prompts displayed in the terminal are mapped using the default English `[en]` language
strings located at **`src/Language/en/Revision.php`**. To provide your own language strings at your own
language, you can use the `revision:language` command.

    php spark revision:language

The following are the available options:

- `--lang` The specific language/locale directory to publish to.
- `--namespace` Set the root namespace. Defaults to the value of `APP_NAMESPACE`.
- `--force` Use this flag to force overwrite existing files in destination.

Examples:

- `php spark revision:language` This will create the file in `app/Language/en` directory.
- `php spark revision:language --lang fr` This will create the file in `app/Language/fr` directory.
- `php spark revision:language --lang fr --namespace Acme` This will create the `Language/fr` subdirectory
  inside the root path defined by the `Acme` namespace.

## Temporary Files Source Control

At every update operation, Revision will create temporary files in the `revision/` subfolder inside the
writable directory defined in the configuration file. By default, temporary files, and also the operation's
log files, will be created at **`writable/revision/`** directory. Since these temporary files may become
too many during actual update, your tracked files may become riddled with many untracked files. So, it is
recommended that you add the directory in your `.gitignore` file.

You can run this command to do it automatically:

    php spark revision:gitignore

Take note, however, that if Revision cannot find a `.gitignore` file, it will display a prompt asking for
permission to create a new file for you. You can authorise Revision to write a new `.gitignore` file
if none is found by using the `--write-if-missing` option.
