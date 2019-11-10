<p align="center"><a href="https://www.aphiria.com" target="_blank" title="Aphiria"><img src="https://www.aphiria.com/images/aphiria-logo.svg"></a></p>

<p align="center">
<a href="https://travis-ci.com/aphiria/aphiria"><img src="https://travis-ci.com/aphiria/aphiria.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/stable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/unstable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/license.svg"></a>
</p>

> **Note:** This framework is not stable yet.

<h1>Introduction</h1>

Aphiria is a suite of small, decoupled PHP libraries that make up a REST API framework.  It simplifies content negotiation without bleeding into your code, allowing you to write expressive code.  Aphiria also provides the following functionality out of the box:

* <a href="https://www.aphiria.com/docs/master/http-requests.html" target="_blank">An HTTP wrapper that fixes the issues with PSR-7</a>, including <a href="https://www.aphiria.com/docs/master/content-negotiation.html" target="_blank">automatic content negotiation</a>
* <a href="https://www.aphiria.com/docs/master/routing.html" target="_blank">One of the fastest, most feature-full routers in PHP</a>
* <a href="https://www.aphiria.com/docs/master/di-container.html" target="_blank">A DI container with bootstrappers to simplify configuring your app</a>
* <a href="https://www.aphiria.com/docs/master/console.html" target="_blank">A console library for running commands from the terminal</a>
* Optional support for annotations of <a href="https://www.aphiria.com/docs/master/routing.html#route-annotations" target="_blank">routes</a> and <a href="https://www.aphiria.com/docs/master/console.html#command-annotations" target="_blank">console commands</a>

<h1>Documentation</h1>

Full documentation is available at <a href="https://www.aphiria.com" target="_blank">the Aphiria website</a>.

<h1>Requirements</h1>

* PHP 7.4
* OpenSSL
* mbstring

<h1>Contributing</h1>

We appreciate any and all contributions to Aphiria.  Please read the [documentation](https://www.aphiria.com/docs/master/contributing.html) to learn how to contribute.

<h1>Directory Structure</h1>

Aphiria is organized as a mono repo.  Each library is contained in _src/{library}_, and contains _src_ and _tests_ directories.

<h1>License</h1>

This software is licensed under the MIT license.  Please read the LICENSE for more information.

<h1>Author</h1>

Aphiria was written by [David Young](https://github.com/davidbyoung).
