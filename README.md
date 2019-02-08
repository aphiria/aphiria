# Serialization

[![Build Status](https://travis-ci.com/aphiria/serialization.svg)](https://travis-ci.com/aphiria/serialization)
[![Latest Stable Version](https://poser.pugx.org/aphiria/serialization/v/stable.svg)](https://packagist.org/packages/aphiria/serialization)
[![Latest Unstable Version](https://poser.pugx.org/aphiria/serialization/v/unstable.svg)](https://packagist.org/packages/aphiria/serialization)
[![License](https://poser.pugx.org/aphiria/serialization/license.svg)](https://packagist.org/packages/aphiria/serialization)

> **Note:** This library is still in development.

<h1>Table of Contents</h1>

1. [Introduction](#introduction)
    1. [Installation](#installation)
3. [Serializers](#serializers)
    1. [Form URL-Encoded Serializer](#form-url-encoded-serializer)
    2. [JSON Serializer](#json-serializer)
    3. [Arrays of Values](#arrays-of-values)
4. [Encoders](#encoders)
    1. [Default Encoders](#default-encoders)
    2. [Object Encoder](#object-encoder)
    3. [Custom Encoders](#custom-encoders)
    4. [DateTime Encoder](#datetime-encoder)

<h1 id="introduction">Introduction</h1>

By default, PHP does not have any way to serialize and deserialize POPO objects.  Aphiria provides this functionality without bleeding into your code.  The best part is that you don't have to worry about how to (de)serialize nested objects or arrays of objects - Aphiria does it for you.  Serializing an object is as easy as:

```php
$user = new User(123, 'foo@bar.com');
$serializer = new JsonSerializer();
$serializer->serialize($user); // {"id":123,"email":"foo@bar.com"}
```

Similarly, deserializing an object is simple:

```php
$serializedUser = '{"id":123,"email":"foo@bar.com"}';
$user = $serializer->deserialize($serializedUser, User::class);
```

<h1 id="installation">Installation</h1>

You can install this library by including the following package name in your _composer.json_:

```bash
"aphiria/serialization": "1.0.*"
```

<h1 id="serializers">Serializers</h1>

Aphiria provides the following serializers:

* [`FormUrlEncodedSerializer`](#form-url-encoded-serializer)
* [`JsonSerializer`](#json-serializer)

Under the hood, serializing works like this:

Value &rarr; [encoded value](#encoders) &rarr; serialized value

Deserializing works in the reverse order:

Serialized value &rarr; [decoded value](#encoders) &rarr; deserialized value

<h2 id="form-url-encoded-serializer">Form URL-Encoded Serializer</h2>

`FormUrlEncodedSerializer` can (de)serialize values to and from form URL-encoded strings.  It's useful for things like (de)serializing values for use in a query string or in request/response bodies.  Creating one is simple:

```php
use Aphiria\Serialization\FormUrlEncodedSerializer;

$serializer = new FormUrlEncodedSerializer();
```

<h2 id="json-serializer">JSON Serializer</h2>

`JsonSerializer` is able to serialize and deserialize values to and from JSON.  You can create an instance like this:

```php
use Aphiria\Serialization\JsonSerializer;

$serializer = new JsonSerializer();
```

<h2 id="arrays-of-values">Arrays of Values</h2>

To deserialize an array of values, append the `$type` parameter with `[]`:

```php
$serializer->deserialize($serializedUsers, 'User[]');
```

This will cause each value in `$serializedUsers` to be deserialized as an instance of `User`.

You don't have to do anything special to serialize an array of values - just pass it in, and Aphiria will know what to do:

```php
$serializer->serialize($users);
```

> **Note:** Aphiria only supports arrays that contain a single type of value.  In other words, you cannot mix and match different types in a single array.

<h1 id="encoders">Encoders</h1>

Encoders define how to map your POPOs to values that a serializer can (de)serialize.  For most [objects](#object-encoder), this involves mapping an object to and from an associative array.  An `EncodingContext` is passed during encoding/decoding to track things like circular references.

<h2 id="default-encoders">Default Encoders</h2>

To make it easier for you, Aphiria encodes/decodes `array` and `DateTime` values via the `ArrayEncoder` and [`DateTimeEncoder`](#datetime-encoder).  `DefaultEncoderRegistrant` registers these default encoders for you.  If you use this registrant, but want to customize some behavior, you can pass in a [property name formatter](#property-name-formatters) and [date format](#datetime-encoder):

```php
$encoders = new EncoderRegistry();
$encoderRegistrant = new DefaultEncoderRegistrant(
    new CamelCasePropertyNameFormatter(),
    'F j, Y'
);
$encoderRegistrant->registerDefaultEncoders($encoders);
// Pass $encoders into your serializer constructor...
```

<h2 id="object-encoder">Object Encoder</h2>

`ObjectEncoder` uses reflection to get all the properties in a class, and creates an associative array of property names to encoded property values.  It even handles nested objects.  When decoding, `ObjectEncoder` scans the constructor parameters and decodes them using the type hints on the parameters, and then sets any public properties (only scalar properties are supported).  For best results, be sure to type your constructor parameters whenever possible.

> **Note:** Since PHP has no typed arrays, it's impossible for `ObjectEncoder` to know how to decode an array of objects by type hints alone.  If your constructor requires an array of objects, [register a custom encoder](#custom-encoders).

<h3 id="ignored-properties">Ignored Properties</h3>

Sometimes, you might want to ignore some properties when serializing your object.  You can specify them like so:

```php
// Create encoder registry...
$objectEncoder = new ObjectEncoder($encoders);
$objectEncoder->addIgnoredProperty(YourClass::class, 'nameOfPropertyToIgnore');
$encoders->registerDefaultObjectEncoder($objectEncoder);
// Pass $encoders into your serializer constructor...
```

You can also specify an array of property names in `addIgnoredProperty()`.

<h3 id="property-name-formatters">Property Name Formatters</h3>

You might find yourself wanting to make your property names' formats consistent (eg camelCase).  You can use an `IPropertyNameFormatter` to accomplish this.  `CamelCasePropertyNameFormatter` and `SnakeCasePropertyNameFormatter` come out of the box.  To use one (or your own), pass it into [`DefaultEncoderRegistrant`](#default-encoders).

<h2 id="custom-encoders">Custom Encoders</h2>

Due to PHP's type limitations, there are some objects that Aphiria simply can't (de)serialize automatically.  Some examples include:

* Classes that require custom instantiation/hydration logic
* Object properties that contain an array of objects
* Non-scalar public properties

In these cases, you can register your own encoder (which must implement `IEncoder`) to the encoder registry:

```php
// Create encoder registry...
$encoders->registerEncoder(YourClass::class, new YourEncoder());
// Pass $encoders into your serializer constructor...
```

Now, whenever an instance of `YourClass` needs to be (de)serialized, `YourEncoder` will be used.

<h2 id="datetime-encoder">DateTime Encoder</h2>

`DateTime` objects are typically serialized to a formatted date string, and deserialized from that string back to an instance of `DateTime`.  Aphiria provides `DateTimeEncoder` to provide this functionality. By default, it uses <a href="https://en.wikipedia.org/wiki/ISO_8601" target="_blank">ISO 8601</a> when (de)serializing `DateTime`, `DateTimeImmutable`, and `DateTimeInterface` objects, but you can [customize the format](#default-encoders).