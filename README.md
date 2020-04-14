<p align="center"><a href="https://www.aphiria.com" target="_blank" title="Aphiria"><img src="https://www.aphiria.com/images/aphiria-logo.svg"></a></p>

<p align="center">

[![Build Status](https://travis-ci.com/aphiria/aphiria.svg)](https://travis-ci.com/aphiria/aphiria)
[![Coverage Status](https://coveralls.io/repos/github/aphiria/aphiria/badge.svg?branch=master)](https://coveralls.io/github/aphiria/aphiria?branch=master)
[![Latest Stable Version](https://poser.pugx.org/aphiria/aphiria/v/stable.svg)](https://packagist.org/packages/aphiria/aphiria)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/aphiria/v/unstable.svg)](https://packagist.org/packages/aphiria/aphiria)
[![License](https://poser.pugx.org/aphiria/aphiria/license.svg)](https://packagist.org/packages/aphiria/aphiria)

</p>

> **Note:** This framework is not stable yet.

## Introduction

Aphiria is a suite of small, decoupled PHP libraries that make up a REST API framework.  It simplifies content negotiation without bleeding into your code, allowing you to write expressive code.  Aphiria also provides the following functionality out of the box:

* <a href="https://www.aphiria.com/docs/master/http-requests.html" target="_blank">An HTTP wrapper that fixes the issues with PSR-7</a>, including <a href="https://www.aphiria.com/docs/master/content-negotiation.html" target="_blank">automatic content negotiation</a>
* <a href="https://www.aphiria.com/docs/master/routing.html" target="_blank">One of the fastest, most feature-full routers in PHP</a>
* <a href="https://www.aphiria.com/docs/master/application-builders.html" target="_blank">A modular way of building your apps from reusable components</a>
* <a href="https://www.aphiria.com/docs/master/di-container.html" target="_blank">A DI container with binders to simplify configuring your app</a>
* <a href="https://www.aphiria.com/docs/master/validation.html" target="_blank">A model validator for your POPOs</a>
* <a href="https://www.aphiria.com/docs/master/console.html" target="_blank">A console library for running commands from the terminal</a>
* Optional support for annotations of <a href="https://www.aphiria.com/docs/master/routing.html#route-annotations" target="_blank">routes</a> and <a href="https://www.aphiria.com/docs/master/console.html#command-annotations" target="_blank">console commands</a>

## Installation

Refer to the [documentation](https://www.aphiria.com/docs/master/installation.html) for installation instructions.

## Documentation

Full documentation is available at <a href="https://www.aphiria.com" target="_blank">the Aphiria website</a>.

## Requirements

* PHP 7.4

## Contributing

We appreciate any and all contributions to Aphiria.  Please read the [documentation](https://www.aphiria.com/docs/master/contributing.html) to learn how to contribute.

## Directory Structure

Aphiria is organized as a mono repo.  Each library is contained in _src/{library}_, and contains _src_ and _tests_ directories.

## License

This software is licensed under the MIT license.  Please read the [LICENSE](LICENSE.md) for more information.

## Author

Aphiria was written by [David Young](https://github.com/davidbyoung).
