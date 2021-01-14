# Contributing Guidelines

Contributions are welcome! Anything that will contribute to the improvement of the project
will be gladly accepted.

## Quick Guide

- [Fork](https://help.github.com/articles/fork-a-repo/) this repository into your Github account.
- [Clone](https://help.github.com/en/articles/cloning-a-repository) your repository to your
    local machine. `git clone https://github.com/<username>/liaison-revision.git`
- Install the project's dependencies. `composer install`
- Create a new [branch](https://help.github.com/en/articles/about-branches) for every set of changes you want to make.
- Make the necessary changes.
- Run PHP-CS-Fixer to fix your code style. `vendor/bin/php-cs-fixer fix -v`
- Run PHPStan to analyze the whole codebase. `vendor/bin/phpstan analyse`
- Run unit tests. `vendor/bin/phpunit`
- If there were no reported errors, [commit](https://help.github.com/en/desktop/contributing-to-projects/committing-and-reviewing-changes-to-your-project) the changed files in your contribution branch.
- [Push](https://docs.github.com/en/github/using-git/pushing-commits-to-a-remote-repository) your local
    commits to your fork.
- Send a [pull request](https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request-from-a-fork).

## Notes

- The base branch is `develop`. Any contributions to other branches will be rejected.
- If you are adding a new feature or fixing a bug, don't forget to add a new test!
- The `Liaison` namespace adheres to a set of coding standards defined in the [`Nexus73`](https://github.com/NexusPHP/cs-config/blob/develop/src/Ruleset/Nexus73.php) ruleset. Contributors should likewise follow that standard.
- Code should be compatible with PHP 7.3+.

## Keeping your fork up-to-date

By default, your fork will have a remote named `origin` that points to your fork. You can, however, add
another remote named `upstream` that will point to `https://github.com/paulbalandan/liaison-revision.git`.
This is a read-only remote where you can use to pull recent changes to the develop branch to update your
own fork.

To view all remotes registered, run `git remote -v`.

## Signing your Work

You must [GPG sign](https://git-scm.com/book/en/v2/Git-Tools-Signing-Your-Work) your work, certifying
that you either wrote the work or have the right to pass it on an open-source project. A mere
"signed-off-by" commit is not sufficient.

Also, the base branch is protected to not accept unverified commits.
