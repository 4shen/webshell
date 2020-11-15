# Contributing to Pagekit

You want to contribute to Pagekit? Awesome. Please take a few moments to
review the following guidelines to get you started. Cheers.

* [Communication channels](#communication)
* [Team members](#team)
* [Documentation](#documentation)
* [Translation](#translation)
* [Issue tracker](#issues)
* [Bug reports](#bugs)
* [Pull requests](#pull-requests)
* [Coding Guidelines](#guidelines)
* [Versioning](#versioning)
* [License](#license)

<a name="communication"></a>
## Communication channels

Before you get lost in the repository, here are a few starting points
for you to check out. You might find that others have had similar
questions or that your question rather belongs in one place than another.

* Chat: https://discord.gg/e7Kw47E
* Community: https://plus.google.com/u/0/communities/104125443335488004107
* Blog: http://pagekit.com/blog
* Twitter: https://twitter.com/pagekit

<a name="team"></a>
## Team members

Pagekit is developed as an open source project by [YOOtheme](http://yootheme.com)
in Hamburg, Germany. The core maintainers you will encounter in this project
are all part of YOOtheme.

## Documentation

The Pagekit documentation is maintained as a collection of Markdown files in its
[own repository](https://github.com/pagekit/docs). Any pull requests are highly appreciated.

## Translation

Pagekit's backend is available in multiple languages. As the backend evolves, we
need active help of native speakers in all languages to keep translations up to date
and improve the wording if possible.

Although translation files are included in the main repository, please **do not**
change the translation files directly. Instead, please use Transifex to translate
to your language: [https://www.transifex.com/pagekit/pagekit-cms/](https://www.transifex.com/pagekit/pagekit-cms/)

The project's maintainers will regularly take care to sync translations from Transifex
to Pagekit.

<a name="issues"></a>
## Using the issue tracker

The issue tracker is the preferred channel for [bug reports](#bugs),
[features requests](#features) and [submitting pull
requests](#pull-requests), but please respect the following restriction:

Please **do not** use the issue tracker for personal support requests (use
[Google+ community](https://plus.google.com/u/0/communities/104125443335488004107) or
[Discord chat](https://discord.gg/e7Kw47E)).

<a name="bugs"></a>
## Bug reports

A bug is a _demonstrable problem_ that is caused by the code in the repository.
Good bug reports are extremely helpful - thank you!

A good bug report shouldn't leave others needing to chase you up for more
information. Please try to be as detailed as possible in your report. What is
your environment? What steps will reproduce the issue? What would you expect to
be the outcome? All these details will help people to fix any potential bugs.

<a name="pull-requests"></a>
## Pull requests

Good pull requests - patches, improvements, new features - are a fantastic
help. Thanks for taking the time to contribute.

**Please ask first** before embarking on any significant pull request,
otherwise you risk spending a lot of time working on something that the
project's developers might not want to merge into the project.

Pagekit follows the [GitFlow branching model](https://de.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow). The ```master``` branch always reflects a production-ready state while the latest development is taking place in the ```develop``` branch.

Each time you want to work on a fix or a new feature, create a new branch based on the ```develop``` branch: ```git checkout -b BRANCH_NAME develop```. Only pull requests to the ```develop``` branch will be merged.

<a name="guidelines"></a>
## Coding Guidelines

Pagekit follows the standards defined in the [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md), [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) documents.


## Versioning

Pagekit is maintained by using the [Semantic Versioning Specification (SemVer)](http://semver.org).

<a name="license"></a>
## License

By contributing your code, you agree to license your contribution under the [MIT license](LICENSE)
