# Net

## Table of Contents
1. [Introduction](#introduction)
    1. [Why Not Use PSR-7?](#why-not-use-psr-7)
2. [HTTP Messages](#http-messages)
3. [HTTP Bodies](#http-bodies)
    1. [String Bodies](#string-bodies)
    2. [Stream Bodies](#stream-bodies)
4. [HTTP Headers](#http-headers)
5. [Requests](#requests)
    1. [Creating a Request From Globals](#creating-request-from-globals)
    2. [Reading Form Inpu](#requests-getting-form-input)
    3. [Reading JSON](#requests-reading-json)
    4. [Reading Multipart Requests](#requests-reading-multipart-requests)
    5. Todo: Add docs for request header parser
6. [Responses](#responses)
    1. Todo: Setting bodies
    2. Todo: Setting cookies
    3. [Writing Responses](#writing-responses)
    4. Todo: Add docs for response message formatters
    5. Todo: Add docs for response header formatters
7. [URIs](#uris)
    1. [Creating URIs From Strings](#creating-uris-from-strings)
    2. [Parsing Query String Parameters](#uris-parsing-query-string-parameters)

<h2 id="introduction">Introduction</h2>

Opulence's network library provides abstractions for URIs, HTTP request and response messages, bodies, and headers.  It also has handy methods for helpers for things like [parsing URI query strings](#uris-parsing-query-string-parameters), [reading request bodies as form input](#requests-reading-body-as-form-input), and [setting cookies in the response headers](#responses-setting-cookies).  It adheres as closely to the HTTP spec as possible, and aims to decouple developers from PHP's horrendous abstractions for HTTP requests and responses.

<h4 id="why-not-use-psr-7">Why Not Use PSR-7?</h4>

PSR-7 was an attempt to standardize the representation of HTTP requests and responses, as well as routing middleware.  PHP does not have these things baked-in, and every framework had previously been rolling its own wrappers, which weren't interopible between frameworks.  Although a noble attempt, PSR-7 had many contested features:

1. Request and response immutability
    * This has often been considered cumbersome, bug-prone, and a bad use-case for immutability
2. HTTP message bodies were streams
    * Bodies aren't inherently streams - they should be _readable as_ streams, and _writable to_ streams
    * Since some streams are readable exactly once, this breaks the idea of immutability in the first point
3. PSR-7 improperly abstracted uploaded files - they are part of the body, not the request as a whole
4. Headers were added through the HTTP message rather than encapsulated in a dictionary-like object that was contained in the message

Opulence's network library aims to fix these shortcomings.

<h2 id="http-messages">HTTP Messages</h2>

HTTP messages are ASCII-encoded text messages that contain headers and bodies.  In Opulence, they're represented by `Opulence\Net\Http\IHttpMessage`.  They come with a few basic methods:

```php
interface IHttpMessage
{
    /**
     * Gets the body of the HTTP message
     *
     * @return IHttpBody|null The body if there is one, otherwise null
     */
    public function getBody() : ?IHttpBody;

    /**
     * Gets the headers of the HTTP message
     *
     * @return HttpHeaders The headers
     */
    public function getHeaders() : HttpHeaders;

    /**
     * Gets the protocol version (eg '1.1' or '2.0') from the HTTP message
     *
     * @return string The protocol version
     */
    public function getProtocolVersion() : string;

    /**
     * Sets the body of the HTTP message
     *
     * @param IHttpBody $body The body
     */
    public function setBody(IHttpBody $body) : void;
}
```

Requests and responses are specific types of HTTP messages.

<h2 id="http-bodies">HTTP Bodies</h2>

HTTP bodies contain data associated with the HTTP message, and are optional.  They're repsented by `Opulence\Net\Http\IHttpBody`.  They provide a few methods to read and write their contents to streams and to strings:

```php
interface IHttpBody
{
    /**
     * Reads the HTTP body as a string
     *
     * @return string The string
     */
    public function __toString() : string;

    /**
     * Reads the HTTP body as a stream
     *
     * @return IStream The stream
     * @throws RuntimeException Thrown if there was an error reading as a stream
     */
    public function readAsStream() : IStream;

    /**
     * Reads the HTTP body as a string
     *
     * @return string The string
     */
    public function readAsString() : string;

    /**
     * Writes the HTTP body to a stream
     *
     * @param IStream $stream The stream to write to
     * @throws RuntimeException Thrown if there was an error writing to the stream
     */
    public function writeToStream(IStream $stream) : void;
}
```

<h4 id="string-bodies">String Bodies</h4>

HTTP bodies are most commonly represented as strings.  Opulence makes it easy to create a string body via `StringBody`:

```php
use Opulence\Net\Http\StringBody;

$body = new StringBody('foo');
```

<h4 id="stream-bodies">Stream Bodies</h4>

Sometimes, bodies might be too big to hold entirely in memory.  This is where `StreamBody` comes in handy:

```php
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\StreamBody;

$stream = new Stream(fopen('foo.txt', 'r+'));
$body = new StreamBody($stream);
```

<h2 id="http-headers">HTTP Headers</h2>

Headers provide metadata about the HTTP message.  In Opulence, they're implemented by `Opulence\Net\Http\HttpHeaders`, which extends  [`Opulence\Collections\HashTable`](collections#hash-tables).  On top of the methods provided by `HashTable`, they also provide the following methods:

```php
/**
 * Gets the first values for a header
 *
 * @param string $name The name of the header whose value we want
 * @return mixed The first value of the header
 * @throws OutOfBoundsException Thrown if the key could not be found
 */
public function getFirst($name);

/**
 * Tries to get the first value of a header
 *
 * @param mixed $name The name of the header whose value we want
 * @param mixed $value The value, if it is found
 * @return bool True if the key exists, otherwise false
 */
public function tryGetFirst($name, &$value) : bool;
```

Header names that are passed into the methods in `HttpHeaders` are normalized to Train-Case.  In other words, `foo_bar` will become `Foo-Bar`.

<h2 id="requests">Requests</h2>

Requests are HTTP messages sent by clients to servers.  They contain a few more methods than `IHttpMessage`:

```php
interface IHttpRequestMessage extends IHttpMessage
{
    /**
     * Gets the HTTP method for the request
     *
     * @return string The HTTP method
     */
    public function getMethod() : string;

    /**
     * Gets the properties of the request
     * These are custom pieces of metadata that the application can attach to the request
     *
     * @return IDictionary The collection of properties
     */
    public function getProperties() : IDictionary;

    /**
     * Gets the URI of the request
     *
     * @return Uri The URI
     */
    public function getUri() : Uri;
}
```

<h4 id="creating-request-from-globals">Creating a Request From Globals</h4>

PHP has superglobal arrays that store information about the requests.  They're a mess, architecturally-speaking.  Opulence attempts to insulate developers from the nastiness of superglobals by giving you a simple method to create requests and responses.  To create a request, use `RequestFactory`:

```php
use Opulence\Net\Http\Requests\RequestFactory;

$request = (new RequestFactory)->createRequestFromGlobals($_SERVER);
```

Opulence reads all the information it needs from the `$_SERVER` superglobal - it doesn't need the others.

<h5 id="trusted-proxies">Trusted Proxies</h5>

If you're using a load balancer or some sort of proxy server, you'll need to add it to the list of trusted proxies:

```php
$factory = new RequestFactory(['192.168.1.1', '192.168.1.2']);
$request = $factory->createRequestFromGlobals($_SERVER);
```

If you want to use your proxy to set custom, trusted headers, you may add them to the factory:

```php
$factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_IP' => 'X-My-Proxy-Ip']);
$request = $factory->createRequestFromGlobals($_SERVER);
```

<h4 id="requests-getting-form-input">Reading Form Input</h4>

In vanilla PHP, you can read URL-encoded form input data via the `$_POST` superglobal.  Opulence gives you a helper to parse the body of URL-encoded form requests into a [dictionary](collections#hash-tables).

```php
use Opulence\Net\Http\HttpBodyParser;

// Let's assume the raw body is "email=foo%40bar.com"
$formInput = (new HttpBodyParser)->readAsFormInput($request->getBody());
echo $formInput->get('email'); // "foo@bar.com"
```

<h4 id="requests-reading-json">Reading JSON</h4>

Rather than having to parse a JSON body yourself, you can use `HttpBodyParser` to do it for you:

```php
use Opulence\Net\Http\HttpBodyParser;

$json = (new HttpBodyParser)->readAsJson($request->getBody());
```

<h4 id="requests-reading-multipart-requests">Reading Multipart Requests</h4>

Multipart requests contain multiple bodies, each with headers.  That's actually how file upload files work - each file gets a body with headers indicating the name, type, and size of the file.  Opulence can parse these multipart bodies into a list of `MultipartBodyPart` objects.  Each `MultipartBodyPart` has an instance of `HttpHeaders` and `IHttpBody`.

```php
use Opulence\Net\Http\Requests\RequestParser;

$multipartBodies = (new RequestParser)->readAsMultipart($request);

foreach ($multipartBodies as $multipartBody) {
    // Get the headers of the body part
    $multipartBody->getHeaders();
    // Get the body of the body part
    $multipartBody->getBody();
}
```

<h5 id="saving-uploaded-files">Saving Uploaded Files</h5>

To save a multipart body part to a file, use `copyBodyToFile()`:

```php
$multipartBody->copyBodyToFile('path/to/copy/to');
```

If you need to copy the body to some other form of storage, eg a CDN, you can read the body as a stream and [copy it to another stream](io#copying-to-another-stream):

```php
use Opulence\IO\Streams\Stream;

$destinationStream = new Stream(fopen('path/to/copy/to', 'w'));
$multipartBody->getBody()->readAsStream()->copyToStream($destinationStream);
```

<h2 id="responses">Responses</h2>

Responses are HTTP messages that are sent by servers back to the client.

```php
interface IHttpResponseMessage extends IHttpMessage
{
    /**
     * Gets the reason phrase of the response
     *
     * @return string|null The reason phrase if one is set, otherwise null
     */
    public function getReasonPhrase() : ?string;

    /**
     * Gets the HTTP status code of the response
     *
     * @return int The HTTP status code of the response
     */
    public function getStatusCode() : int;

    /**
     * Sets the HTTP status code of the response
     *
     * @param int $statusCode The HTTP status code of the response
     * @param string|null $reasonPhrase The reason phrase if there is one, otherwise null
     */
    public function setStatusCode(int $statusCode, ?string $reasonPhrase = null) : void;
}
```

<h4 id="writing-responses">Writing Responses</h4>

Once you're ready to start sending the response back to the client, you can use `ResponseWriter`:

```php
use Opulence\Net\Http\Responses\ResponseWriter;

(new ResponseWriter)->writeResponse($response);
```

By default, this will write the response to the `php://output` stream.  You can override the stream it writes to via the constructor:

```php
$outputStream = new Stream(fopen('path/to/output', 'w'));
(new ResponseWriter($outputStream))->writeResponse($response);
```

<h2 id="uris">URIs</h2>

A URI identifies a resource, typically over a network.  They contain such information as the scheme, host, port, path, query string, and fragment.  Opulence represents them in `Opulence\Net\Uri`, and they include the following methods:

```php
/**
 * Converts the URI to a string
 *
 * @return string The URI as a string
 */
public function __toString() : string;

/**
 * Gets the fragment
 *
 * @return string|null The fragment if set, otherwise null
 */
public function getFragment() : ?string;

/**
 * Gets the host
 *
 * @return string The host
 */
public function getHost() : string;

/**
 * Gets the password
 *
 * @return string|null The password if set, otherwise null
 */
public function getPassword() : ?string;

/**
 * Gets the path
 *
 * @return string The path
 */
public function getPath() : string;

/**
 * Gets the port
 *
 * @return int|null The port if set, otherwise null
 */
public function getPort() : ?int;

/**
 * Gets the query string
 *
 * @return string|null The query string if set, otherwise null
 */
public function getQueryString() : ?string;

/**
 * Gets the scheme
 *
 * @return string The scheme
 */
public function getScheme() : string;

/**
 * Gets the user
 *
 * @return string|null The user if set, otherwise null
 */
public function getUser() : ?string;
```

<h4 id="creating-uris-from-strings">Creating URIs From Strings</h4>

To create an instance of `Uri` from a raw string, eg `https://example.com/foo?bar=baz`, use `UriFactory`:

```php
use Opulence\Net\UriFactory;

$uri = (new UriFactory)->createUriFromString($rawString);
```

<h4 id="uris-parsing-query-string-parameters">Parsing Query String Parameters</h4>

`Uri::getQueryString()` returns the raw query string.  To parse them into an immutable [dictionary](collections#immutable-hash-tables) (similar to PHP's `$_GET`), use `UriParser`:

```php
use Opulence\Net\UriFactory;
use Opulence\Net\UriParser;

$uri = (new UriFactory)->createUriFromString('https://example.com?foo=bar');
$queryStringParams = (new UriParser)->parseQueryString($uri);
echo $queryStringParams->get('foo'); // "bar"
```