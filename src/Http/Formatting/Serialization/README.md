# Serialization

> **Note:** This library is still in development.

## Table of Contents
1. [Introduction](#introduction)
2. [Serializers](#serializers)
    1. [JSON Serializer](#json-serializer)
3. [Contracts](#contracts)
    1. [Dictionary Object Contracts](#dictionary-object-contracts)
    2. [Value Object Contracts](#value-object-contracts)
    3. [Default Contracts](#default-contracts)
4. [Encoding Interceptors](#encoding-interceptors)

<h2 id="introduction">Introduction</h2>

By default, PHP does not have any way to serialize and deserialize POPO objects.  This library gives that functionality with an easy-to-use, flexible syntax.  Once your [contracts](#contracts) are set up, serializing an object is as easy as:

```php
$user = new User(123, 'foo@bar.com');
$jsonSerializer->serialize($user); // {"id":123,"email":"foo@bar.com"}
```

Similarly, deserializing an object is simple:

```php
$serializedUser = '{"id":123,"email":"foo@bar.com"}';
$user = $jsonSerializer->deserialize($serializedUser, User::class);
```

Additionally, Opulence can also automatically serialize and deserialize nested objects with no extra work on your part.

<h2 id="serializers">Serializers</h2>

Opulence provides the following serializers:

* [`JsonSerializer`](#json-serializer)

Under the hood, serializing works like this:

1. Your value is converted to an encoded value via a ["contract"](#contracts)
2. [Interceptors](#encoding-interceptors) are run on your encoded value
3. The encodable value is serialized by your serializer

Deserializing works in the reverse order:

1. Your serialized value is decoded by a ["contract"](#contract)
2. [Interceptors](#encoding-interceptors) are run on your decoded value
3. An instance of your value (eg an object) is created from the decoded value

<h4 id="json-serializer">JSON Serializer</h4>

`JsonSerializer` is able to serialize and deserialize value to and from JSON.  You can create an instance like this:

```php
use Opulence\Net\Http\Formatting\Serialization\ContractRegistry;
use Opulence\Net\Http\Formatting\Serialization\JsonSerializer;

$contracts = new ContractRegistry();
// Register your models' contracts...
$jsonSerializer = new JsonSerializer($contracts);
```

If you're using [encoding interceptors](#encoding-interceptors), simply pass an array of them into the constructor:

```php
$jsonSerializer = new JsonSerializer($contracts, [$interceptor1, $interceptor2]);
```

<h2 id="contracts">Contracts</h2>

Contracts are a way to define the properties that make up your POPOs in a way the serializer can understand.  There are two types of contracts:  [`DictionaryObjectContract`](#dictionary-object-contracts) and [`ValueObjectContract`](#value-object-contracts).

<h4 id="dictionary-object-contracts">Dictionary Object Contracts</h4>

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

You can set up your contract for this class like so:

```php
$contracts->registerDictionaryObjectContract(
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

Now, you can serialize and deserialize and User object.  Here's the cool part - you don't have to worry about how to deserialize or decode any of the values in `$properties` - Opulence does it for you.  So, you can be sure that `$properties['registrationDate']` will be an instance of `DateTime`.

<h4 id="value-object-contracts">Value Object Contracts</h4>

Some objects or values don't have a mapping of property names to values.  Some examples include `DateTime` and `string`.  Opulence provides [default contracts](#default-contracts) for the most common value types, but you can register your own value object contracts like so:

```php
$contracts->registerValueObjectContract(
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

The following value types have default contracts built into `ContractRegistry`:

* `bool`
* `DateTime`
* `float`
* `int`
* `string`

`DateTime` is a special case because it allows you to specify the format used when serializing and deserializing.  By default, ISO-8601 is enabled, by you can customize the format by passing in a parameter:

```php
$contractRegistry = new ContractRegistry('F j, Y');
```

<h2 id="encoding-interceptors">Encoding Interceptors</h2>

Occasionally, you might want to do some custom logic when encoding and decoding values prior to serialization and after deserialization, respectively.  For example, you might want to make all property names camelCase when serializing objects.  The following interceptors are bundled with Opulence:

* `CamelCasePropertyNameFormatter`
* `SnakeCasePropertyNameFormatter`

To create your own interceptor, implement `IEncodingInterceptor`, and pass it into the serializer's constructor:

```php
$jsonSerializer = new JsonSerializer($contracts, [new MyCustomInterceptor()]);
```