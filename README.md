<p align="center"><a href="https://www.aphiria.com" target="_blank" title="Aphiria"><img src="https://www.aphiria.com/images/aphiria-logo.svg"></a></p>

<p align="center">
<a href="https://github.com/aphiria/aphiria/actions"><img src="https://github.com/aphiria/aphiria/workflows/ci/badge.svg"></a>
<a href='https://coveralls.io/github/aphiria/aphiria?branch=0.x'><img src='https://coveralls.io/repos/github/aphiria/aphiria/badge.svg?branch=0.x' alt='Coverage Status' /></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/stable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/unstable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/license.svg"></a>
</p>

> **Note:** This framework is not stable yet.

## Introduction

Aphiria is a suite of small, decoupled PHP libraries that make up a REST API framework.  It simplifies content negotiation without bleeding into your code, allowing you to write expressive code.  Aphiria also provides the following functionality out of the box:

* <a href="https://www.aphiria.com/docs/0.x/http-requests.html" target="_blank">An HTTP wrapper that fixes the issues with PSR-7</a>, including <a href="https://www.aphiria.com/docs/0.x/content-negotiation.html" target="_blank">automatic content negotiation</a>
* <a href="https://www.aphiria.com/docs/0.x/routing.html" target="_blank">One of the fastest, most feature-full routers in PHP</a>
* <a href="https://www.aphiria.com/docs/0.x/configuration.html#application-builders" target="_blank">A modular way of building your apps from reusable components</a>
* <a href="https://www.aphiria.com/docs/0.x/dependency-injection.html" target="_blank">A DI container with binders to simplify configuring your app</a>
* <a href="https://www.aphiria.com/docs/0.x/validation.html" target="_blank">A model validator for your POPOs</a>
* <a href="https://www.aphiria.com/docs/0.x/console.html" target="_blank">A console library for running commands from the terminal</a>
* Optional support for annotations of <a href="https://www.aphiria.com/docs/0.x/routing.html#route-annotations" target="_blank">routes</a> and <a href="https://www.aphiria.com/docs/0.x/console.html#command-annotations" target="_blank">console commands</a>

## Installation

Refer to the [documentation](https://www.aphiria.com/docs/0.x/installation.html) for installation instructions.

## Documentation

Full documentation is available at <a href="https://www.aphiria.com" target="_blank">the Aphiria website</a>.

## Requirements

* PHP 8.0

## Contributing

We appreciate any and all contributions to Aphiria.  Please read the [documentation](https://www.aphiria.com/docs/0.x/contributing.html) to learn how to contribute.

## Directory Structure

Aphiria is organized as a mono repo.  Each library is contained in _src/{library}_, and contains _src_ and _tests_ directories.

## License

This software is licensed under the MIT license.  Please read the [LICENSE](LICENSE.md) for more information.

## Author

Aphiria was written by [David Young](https://github.com/davidbyoung).
