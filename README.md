# Serialization

> **Note:** This library is still in development.

## Table of Contents
1. [Introduction](#introduction)
2. [Serializers](#serializers)
    1. [JSON Serializer](#json-serializer)
    2. [Array of Values](#array-of-values)
3. [Encoders](#encoders)
    1. [Object Encoders](#object-encoders)
    2. [Struct Encoders](#struct-encoders)
    3. [Default Encoders](#default-encoders)
    4. [DateTime Formatting](#datetime-formatting)
4. [Encoding Interceptors](#encoding-interceptors)

<h2 id="introduction">Introduction</h2>

By default, PHP does not have any way to serialize and deserialize POPO objects.  Opulence provides this functionality without bleeding into your code.  The best part is that you don't have to worry about how to (de)serialize nested objects or arrays of objects - Opulence does it for you.  Once your [encoders](#encoders) are set up, serializing an object is as easy as:

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

Value &rarr; encode [encoder](#encoders) &rarr; [Interceptors](#encoding-interceptors) &rarr; serialized value

Deserializing works in the reverse order:

Serialized value &rarr; decode [encoder](#encoders) &rarr; [Interceptors](#encoding-interceptors) &rarr; deserialized value

<h4 id="json-serializer">JSON Serializer</h4>

`JsonSerializer` is able to serialize and deserialize values to and from JSON.  You can create an instance like this:

```php
use Opulence\Net\Http\Formatting\Serialization\Encoding\EncoderRegistry;
use Opulence\Net\Http\Formatting\Serialization\JsonSerializer;

$encoders = new EncoderRegistry();
// Register your models' encoders...
$jsonSerializer = new JsonSerializer($encoders);
```

<h4 id="array-of-values">Array of Values</h4>

To deserialize an array of values, specify pass `true` to the last parameter:

```php
$serializer->deserialize($serializedUsers, User::class, true);
```

This will cause each value in `$serializedUsers` to be deserialized as an instance of `User`.

You don't have to do anything special to serialize an array of values - just pass it in, and Opulence will know what to do:

```php
$serializer->serialize($users);
```

> **Note:** Opulence only supports arrays that contain a single type of value.  In other words, you cannot mix and match different types in a single array.

<h2 id="encoders">Encoders</h2>

Encoders are a way to define the properties that make up your POPOs in a way the serializer can understand.  There are two types of encoders:  [`ObjectEncoder`](#object-encoders) and [`StructEncoder`](#struct-encoders).

<h4 id="object-encoders">Object Encoders</h4>

These encoders are the primary way of defining most objects.  They take in a type, a constructor for your object, and a list of properties that make up the object.  Let's say your `User` class looks like this:

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

You can set up your encoder for the `User` class like so:

```php
$encoders->registerObjectEncoder(
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

Now, you can (de)serialize a `User` object.  Here's the cool part - you don't have to worry about how to (de)serialize any property values inside your encoders - Opulence does it for you.  In the example above, you can always be sure that `$properties['registrationDate']` will be an instance of `DateTime`.

<h5 id="array-properties">Array Properties</h5>

If a encoder property is an array of values rather than a single value, use `ArrayProperty` instead of `Property`.

> **Note:** Opulence only supports arrays that contain a single type of value.  In other words, you cannot mix and match different types in a single array.

<h5 id="nullable-properties">Nullable Properties</h5>

If any of your encoder properties are nullable, use `NullableProperty` instead of `Property`.

<h4 id="struct-encoders">Struct Encoders</h4>

Some values, such as `DateTime` and `string`, are better thought of as structs.  Opulence provides [default encoders](#default-encoders) for the most common struct types, but you can register your own encoders like so:

```php
$encoders->registerStructEncoder(
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

<h4 id="default-encoders">Default Encoders</h4>

The following structs have default encoders built into `EncoderRegistry`:

* `bool`
* `DateTime`
* `DateTimeImmutable`
* `float`
* `int`
* `string`

<h4 id="datetime-formatting">DateTime Formatting</h4>

By default, Opulence uses <a href="https://en.wikipedia.org/wiki/ISO_8601" target="_blank">ISO 8601</a> when (de)serializing `DateTime` objects, but you can customize the format:

```php
$encoders = new EncoderRegistry('F j, Y');
$jsonSerializer = new JsonSerializer($encoders);
```

<h2 id="encoding-interceptors">Encoding Interceptors</h2>

Occasionally, you might want to do some custom logic when encoding and decoding values prior to serialization and after deserialization, respectively.  For example, you might want to make all property names camelCase when serializing objects.  The following interceptors are bundled with Opulence:

* `CamelCasePropertyNameFormatter`
* `SnakeCasePropertyNameFormatter`

You can use an interceptor in your serializer by passing it as a parameter:

```php
// Set the JSON serializer to user camelCase property names:
$jsonSerializer = new JsonSerializer($encoders, [new CamelCasePropertyNameFormatter()]);
```

To create your own interceptor, simply implement `IEncodingInterceptor` and pass it into the serializer.