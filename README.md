# Serialization

> **Note:** This library is still in development.

## Table of Contents
1. [Introduction](#introduction)
2. [Serializers](#serializers)
    1. [JSON Serializer](#json-serializer)
3. [Encoders](#encoders)
    1. [Object Encoder](#object-encoder)
    2. [Custom Encoders](#custom-encoders)
    3. [DateTime Encoder](#datetime-encoder)

<h2 id="introduction">Introduction</h2>

By default, PHP does not have any way to serialize and deserialize POPO objects.  Opulence provides this functionality without bleeding into your code.  The best part is that you don't have to worry about how to (de)serialize nested objects or arrays of objects - Opulence does it for you.  Serializing an object is as easy as:

```php
$user = new User(123, 'foo@bar.com');
$jsonSerializer = new JsonSerializer();
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

Value &rarr; [encoded value](#encoders) &rarr; serialized value

Deserializing works in the reverse order:

Serialized value &rarr; [decoded value](#encoders) &rarr; deserialized value

<h4 id="json-serializer">JSON Serializer</h4>

`JsonSerializer` is able to serialize and deserialize values to and from JSON.  You can create an instance like this:

```php
use Opulence\Serialization\JsonSerializer;

$jsonSerializer = new JsonSerializer();
```

If you need to register any [custom encoders](#custom-encoders), set up your `Entity Registry`](#entity-registry), and pass it in to the constructor:

```php
use Opulence\Serialization\Encoding\EncoderRegistry;

$encoders = new EncoderRegistry();
// Set up $encoders...
$jsonSerializer = new JsonSerializer($encoders);
```

<h5 id="arrays-of-values">Arrays of Values</h5>

To deserialize an array of values, append the `$type` parameter with `[]`:

```php
$serializer->deserialize($serializedUsers, User::class . '[]');
```

This will cause each value in `$serializedUsers` to be deserialized as an instance of `User`.

You don't have to do anything special to serialize an array of values - just pass it in, and Opulence will know what to do:

```php
$serializer->serialize($users);
```

> **Note:** Opulence only supports arrays that contain a single type of value.  In other words, you cannot mix and match different types in a single array.

<h2 id="encoders">Encoders</h2>

Encoders are a way to define how to map your POPOs to values that a serializer can (de)serialize.  For most [objects](#object-encoders), this involves mapping an object to and from an associative array.

<h4 id="object-encoder">Object Encoder</h4>

`ObjectEncoder` uses reflection to get all the properties in a class, and creates an associative array of property names to encoded property values.  It even handles nested objects.  When decoding, `ObjectEncoder` scans the constructor parameters and decodes them using the type hints on the parameters, and then sets any public properties (only scalar properties are supported).  For best results, be sure to type your constructor parameters whenever possible.

> **Note:** Since PHP has no typed arrays, it's impossible for `ObjectEncoder` to know how to decode an array of objects by type hints alone.  If your constructor requires an array of objects, [register a custom encoder](#custom-encoders).

<h5 id="ignored-properties">Ignored Properties</h5>

Sometimes, you might want to ignore some properties when serializing your object.  You can specify them like so:

```php
$encoders = new EncoderRegistry();
$objectEncoder = new ObjectEncoder($encoders);
$objectEncoder->addIgnoredProperty(YourClass::class, 'nameOfPropertyToIgnore');
$encoders->registerDefaultObjectEncoder($objectEncoder);
// Pass $encoders into your serializer...
```

<h5 id="property-name-formatters">Property Name Formatters</h5>

You might find yourself wanting to make your property names' formats consistent.  For example, you might want to camelCase them.  `CamelCasePropertyNameFormatter` and `SnakeCasePropertyNameFormatter` come out of the box.  To use one (or your own), pass it into `ObjectEncoder`:

```php
$objectEncoder = new ObjectEncoder($encoders, new YourPropertyNameFormatter());
// Register the encoder...
```

<h4 id="custom-encoders">Custom Encoders</h4>

Due to PHP's type limitations, there are some objects that Opulence just can't (de)serialize automatically.  Some examples include:

* Classes that require custom instantiation/hydration logic
* Properties inside objects that contain an array of objects
* Non-scalar public properties

In these cases, you can register your own encoder (must implement `IEncoder`) to the `EntityRegistry`:

```php
$encoders = new EntityRegistry();
$encoders->registerEncoder('YourClass', new YourEncoder());
// Pass $encoders into your serializer...
```

Now, whenever an instance `YourClass` needs to be (de)serialized, `YourEncoder` will be used.

<h4 id="datetime-encoder">DateTime encoder</h4>

`DateTime` objects are typically serialized to a formatted date string, and deserialized from that string back to an instance of `DateTime`.  Opulence provides `DateTimeEncoder` to provide this functionality. By default, it uses <a href="https://en.wikipedia.org/wiki/ISO_8601" target="_blank">ISO 8601</a> when (de)serializing `DateTime`, `DateTimeImmutable`, and `DateTimeInterface` objects, but you can customize the format:

```php
use Opulence\Serialization\Encoding\DefaultEncoderRegistrant;
use Opulence\Serialization\Encoding\EncoderRegistry;

$customDateTimeFormat = 'F j, Y';
$encoders = new EncoderRegistry();
(new DefaultEncoderRegistrant($customDateTimeFormat))->registerDefaultEncoders($encoders);
$jsonSerializer = new JsonSerializer($encoders);
```