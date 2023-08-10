<p align="center"><a href="https://www.aphiria.com" target="_blank" title="Aphiria"><img src="https://www.aphiria.com/images/aphiria-logo.svg" width="200" height="56"></a></p>

<p align="center">
<a href="https://github.com/aphiria/aphiria/actions"><img src="https://github.com/aphiria/aphiria/workflows/ci/badge.svg"></a>
<a href="https://coveralls.io/github/aphiria/aphiria?branch=1.x"><img src="https://coveralls.io/repos/github/aphiria/aphiria/badge.svg?branch=1.x" alt="Coverage Status"></a>
<a href="https://psalm.dev"><img src="https://shepherd.dev/github/aphiria/aphiria/level.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/stable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/v/unstable.svg"></a>
<a href="https://packagist.org/packages/aphiria/aphiria"><img src="https://poser.pugx.org/aphiria/aphiria/license.svg"></a>
</p>

> **Note:** This framework is not stable yet.

## Introduction

Aphiria is a suite of small, decoupled PHP libraries that make up a REST API framework.  It simplifies content negotiation without bleeding into your code, allowing you to write expressive code.  Aphiria also provides the following functionality out of the box:

* <a href="https://www.aphiria.com/docs/1.x/content-negotiation.html" target="_blank">Automatic content negotiation</a> of your POPOs
* <a href="https://www.aphiria.com/docs/1.x/routing.html" target="_blank">One of the fastest, most feature-full routers in PHP</a>
* <a href="https://www.aphiria.com/docs/1.x/configuration.html#application-builders" target="_blank">A modular way of building your apps from reusable components</a>
* <a href="https://www.aphiria.com/docs/1.x/authentication.html" target="_blank">An extensible authentication scheme</a>
* <a href="https://www.aphiria.com/docs/1.x/authorization.html" target="_blank">A policy-based authorization control system</a>
* <a href="https://www.aphiria.com/docs/1.x/dependency-injection.html" target="_blank">A DI container with binders to simplify configuring your app</a>
* <a href="https://www.aphiria.com/docs/1.x/validation.html" target="_blank">A model validator for your POPOs</a>
* Support for <a href="https://www.aphiria.com/docs/1.x/routing.html#route-attributes" target="_blank">route</a> and <a href="https://www.aphiria.com/docs/1.x/validation.html" target="_blank">validation</a> attributes

```php
// Define some controller endpoints
#[RouteGroup('/users')]
class UserController extends Controller
{
    public function __construct(private IUserService $users) {}

    #[Post('')]
    public function createUser(User $user): IResponse
    {
        $this->users->create($user);
        
        return $this->created("/users/{$user->id}", $user);
    }

    #[Get('/:id')]
    #[AuthorizeRoles('admin')]
    public function getUserById(int $id): User
    {
        return $this->users->getById($id);
    }
}

// Bind your dependency
$container->bindInstance(IUserService::class, new UserService());

// Run an integration test
$postResponse = $this->post('/users', new User('Dave'));
$user = $this->readResponseBodyAs(User::class, $postResponse);
$admin = (new PrincipalBuilder('example.com'))->withRoles('admin')
    ->build();
$getResponse = $this->actingAs($admin, fn () => $this->get("/users/$user->id"));
$this->assertParsedBodyEquals($user, $getResponse);
```

## Installation

Create an Aphiria app via Composer:

```bash
composer create-project aphiria/app --prefer-dist --stability dev
```

Refer to the [documentation](https://www.aphiria.com/docs/1.x/installation.html) for more details.

## Documentation

Full documentation is available at <a href="https://www.aphiria.com" target="_blank">the Aphiria website</a>.

## Requirements

* PHP 8.2

## Contributing

We appreciate any and all contributions to Aphiria.  Please read the [documentation](https://www.aphiria.com/docs/1.x/contributing.html) to learn how to contribute.

## Community

If you have general questions or comments about Aphiria, join our [GitHub Discussions](https://github.com/aphiria/aphiria/discussions).

## Directory Structure

Aphiria is organized as a monorepo.  Each library is contained in _src/{library}_, and contains _src_ and _tests_ directories.

## License

This software is licensed under the MIT license.  Please read the [LICENSE](LICENSE.md) for more information.

## Author

Aphiria was created and primarily written by [David Young](https://github.com/davidbyoung).
