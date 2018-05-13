# Serialization

> **Note:** This library is still in development.

## Table of Contents
1. [Introduction](#introduction)
2. [Serializers](#serializers)
    1. [JSON Serializer](#json-serializer)
3. [Contracts](#contracts)
    1. [Object Contracts](#object-contracts)
    2. [Struct Contracts](#struct-contracts)
    3. [Default Contracts](#default-contracts)
    4. [DateTime Formatting](#datetime-formatting)
4. [Encoding Interceptors](#encoding-interceptors)

<h2 id="introduction">Introduction</h2>

By default, PHP does not have any way to serialize and deserialize POPO objects.  Opulence provides this functionality without bleeding into your code.  The best part is that you don't have to worry about how to (de)serialize nested objects - Opulence does it for you.  Once your [contracts](#contracts) are set up, serializing an object is as easy as:

```php
$user = new User(123, 'foo@bar.com');
$jsonSerializer->serialize($user); // {"id":123,"email":"foo@bar.com"}
```

Similarly, deserializing an object is simple:

```php
$serializedUser = '{"id":123,"email":"foo@bar.com"}';
$user = $jsonSerializer->deserialize($serializedUser, User::class);
```

<h2 id="serializers">Serializers</h2>

Opulence provides the following serializers:

* [`JsonSerializer`](#json-serializer)

Under the hood, serializing works like this:

1. Your value is converted to an encoded value via a "[contract](#contracts)"
2. [Interceptors](#encoding-interceptors) are run on your encoded value
3. The encoded value is serialized by your serializer

Deserializing works in the reverse order:

1. Your serialized value is decoded by a "[contract](#contracts)"
2. [Interceptors](#encoding-interceptors) are run on your decoded value
3. An instance of your value (eg an object) is created from the decoded value

<h4 id="json-serializer">JSON Serializer</h4>

`JsonSerializer` is able to serialize and deserialize values to and from JSON.  You can create an instance like this:

```php
use Opulence\Net\Http\Formatting\Serialization\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\JsonSerializer;

$contracts = new ContractRegistry();
// Register your models' contracts...
$jsonSerializer = new JsonSerializer($contracts);
```

<h2 id="contracts">Contracts</h2>

Contracts are a way to define the properties that make up your POPOs in a way the serializer can understand.  There are two types of contracts:  [`ObjectContract`](#object-contracts) and [`StructContract`](#struct-contracts).

<h4 id="object-contracts">Object Contracts</h4>

These contracts are the primary way of defining most objects.  They take in a type, a constructor for your object, and a list of properties that make up the object.  Let's say your `User` class looks like this:

```php
class User
{
    private $username;
    private $registrationDate;

    public function __construct(string $username, DateTime $registrationDate)
    {
        $this->username = $username;
        $this->registrationDate = $registrationDate;
    }

    public function getRegistrationDate(): DateTime
    {
        return $this->registrationDate;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
```

You can set up your contract for the `User` class like so:

```php
$contracts->registerObjectContract(
    User::class,
    // Define how to construct User from the properties defined below
    function ($properties) {
        return new User($properties['username'], $properties['registrationDate']);
    },
    new Property('username', 'string', function (User $user) {
        return $user->getUsername();
    }),
    new Property('registrationDate', DateTime::class, function (User $user) {
        return $user->getRegistrationDate();
    })
);
```

Now, you can (de)serialize a `User` object.  Here's the cool part - you don't have to worry about how to deserialize or decode any of the values in `$properties` - Opulence does it for you.  So, you can be sure that `$properties['registrationDate']` will be an instance of `DateTime`.

<h5 id="nullable-properties">Nullable Properties</h5>

If any of your contract properties are nullable, use `NullableProperty` instead of `Property`.

<h4 id="struct-contracts">Struct Contracts</h4>

Some values, such as `DateTime` and `string`, are better thought of as structs.  Opulence provides [default contracts](#default-contracts) for the most common struct types, but you can register your own contracts like so:

```php
$contracts->registerStructContract(
    DateTime::class,
    // Define how to construct DateTime
    function ($value) {
        return DateTime::createFromFormat(DateTime::ISO8601, $value);
    },
    // Define how to encode a DateTime
    function (DateTime $dateTime) {
        return $dateTime->format(DateTime::ISO8601);
    }
);
```

<h4 id="default-contracts">Default Contracts</h4>

The following structs have default contracts built into `ContractRegistry`:

* `bool`
* `DateTime`
* `DateTimeImmutable`
* `float`
* `int`
* `string`

<h4 id="datetime-formatting">DateTime Formatting</h4>

By default, Opulence uses <a href="https://en.wikipedia.org/wiki/ISO_8601" target="_blank">ISO 8601</a> when (de)serializing `DateTime` objects, but you can customize the format:

```php
$contractRegistry = new ContractRegistry('F j, Y');
```

<h2 id="encoding-interceptors">Encoding Interceptors</h2>

Occasionally, you might want to do some custom logic when encoding and decoding values prior to serialization and after deserialization, respectively.  For example, you might want to make all property names camelCase when serializing objects.  The following interceptors are bundled with Opulence:

* `CamelCasePropertyNameFormatter`
* `SnakeCasePropertyNameFormatter`

You can use an interceptor in your serializer by passing it as a parameter:

```php
// Set the JSON serializer to user camelCase property names:
$jsonSerializer = new JsonSerializer($contracts, [new CamelCasePropertyNameFormatter()]);
```

To create your own interceptor, simply implement `IEncodingInterceptor` and pass it into the serializer.