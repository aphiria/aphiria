# Net

## Table of Contents
1. [Introduction](#introduction)
    1. [Requirements](#requirements)
    2. [Why Not Use PSR-7?](#why-not-use-psr-7)
2. [HTTP Headers](#http-headers)
    1. [Header Parsers](#header-parsers)
3. [HTTP Bodies](#http-bodies)
    1. [String Bodies](#string-bodies)
    2. [Stream Bodies](#stream-bodies)
4. [Requests](#requests)
    1. [Creating Requests](#creating-requests)
    2. [Reading Form Input](#requests-getting-form-input)
    3. [Reading JSON](#requests-reading-json)
    4. [Multipart Requests](#multipart-requests)
    5. [Getting Cookies](#getting-request-cookies)
    6. [Getting Client IP Address](#getting-client-ip-address)
    7. [Serializing Requests](#serializing-requests)
5. [Responses](#responses)
    1. [Creating Responses](#creating-responses)
    2. [Setting Cookies](#setting-response-cookies)
    3. [Writing Responses](#writing-responses)
    4. [Serializing Responses](#serializing-responses)
6. [URIs](#uris)
    1. [Parsing Query String Parameters](#uris-parsing-query-string-parameters)

<h2 id="introduction">Introduction</h2>

Opulence's network library provides abstractions for HTTP request and response messages, bodies, headers, and URIs.  It attempts to accurately model HTTP components, and aims to decouple developers from PHP's horrendous abstractions for HTTP requests and responses.

<h4 id="requirements">Requirements</h4>

In PHP POST requests, bodies are read from `$_POST` and `$_FILES` for form data and for uploaded files, respectively.  All other request methods must be manually parsed from the `php://input` stream.  To work around the inconsistencies of the PHP internals, Opulence requires the following setting in either a <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank">_.user.ini_</a> or your _php.ini_:

```
enable_post_data_reading = 0
```

This will disable automatically parsing POST data into `$_POST` and multipart data into `$_FILES`.

> **Note:** If you're developing any non-Opulence applications on your web server, use <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank">_.user.ini_</a> to limit this setting to only your Opulence application.  Alternatively, you can add `php_value enable_post_data_reading 0` to an _.htaccess_ file or to your _httpd.conf_.

<h4 id="why-not-use-psr-7">Why Not Use PSR-7?</h4>

PSR-7 was an attempt to standardize the models for HTTP components.  PHP does not have these things baked-in, and every framework has been rolling its own wrappers, which weren't interopible between frameworks.  Although a noble attempt, PSR-7 had many contested features:

1. Request and response immutability
    * This has often been considered cumbersome, bug-prone, and a bad use-case for immutability
2. HTTP message bodies were streams
    * Bodies aren't inherently streams - they should be _readable as_ streams, and _writable to_ streams
    * Since some streams are readable exactly once, this breaks the idea of immutability in the first point
3. PSR-7 improperly abstracted uploaded files - they are part of the body, not the request message
4. Headers were added through the HTTP message rather than encapsulated in a dictionary-like object that was contained in the message

<h2 id="http-headers">HTTP Headers</h2>

Headers provide metadata about the HTTP message.  In Opulence, they're implemented by `Opulence\Net\Http\HttpHeaders`, which extends  [`Opulence\Collections\HashTable`](collections#hash-tables).  On top of the methods provided by `HashTable`, they also provide the following methods:

* `getFirst(string $name) : mixed`
* `tryGetFirst(string $name, &$value) : bool`

> **Note:** Header names that are passed into the methods in `HttpHeaders` are automatically normalized to Train-Case.  In other words, `foo_bar` will become `Foo-Bar`.

<h4 id="header-parsers">Header Parsers</h4>

Opulence provides some tools to glean information about the HTTP messages via `HttpHeaderParser`.

<h5 id="checking-if-json">Checking if JSON</h5>

```php
use Opulence\Net\Http\HttpHeaderParser;

$headerParser = new HttpHeaderParser();
$isJson = $headerParser->isJson($request->getHeaders());
```

<h5 id="checking-if-multipart">Checking if Multipart</h5>

```php
$isMultipart = $headerParser->isMultipart($request->getHeaders());
```

<h5 id="parsing-header-parameters">Parsing Header Parameters</h5>

Some header values are semicolon delimited, eg `Content-Type: text/html; charset=utf-8`.  It's sometimes convenient to grab those key => value pairs:

```php
$contentTypeHeader = $request->getHeaders()->getFirst('Content-Type')
$contentTypeValues = $headerParser->parseParameters($contentTypeHeader);
// Keys without values will return null:
echo $contentTypeValues->get('text/html'); // null
echo $contentTypeValues->get('charset'); // "utf-8"
```

<h2 id="http-bodies">HTTP Bodies</h2>

HTTP bodies contain data associated with the HTTP message, and are optional.  They're represented by `Opulence\Net\Http\IHttpBody`.  They provide a few methods to read and write their contents to streams and to strings:

* `__toString() : string`
* `readAsStream() : IStream`
* `readAsString() : string`
* `writeToStream(IStream $stream) : void`

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

<h2 id="requests">Requests</h2>

Requests are HTTP messages sent by clients to servers.  They contain the following methods:

* `getBody() : ?IHttpBody`
* `getHeaders() : HttpHeaders`
* `getMethod() : string`
* `getProperties() : IDictionary`
* `getProtocolVersion() : string`
* `getUri() : Uri`
* `setBody(IHttpBody $body) : void`

> **Note:** The properties dictionary is a useful place to store metadata about a request, eg route variables.

<h4 id="creating-requests">Creating Requests</h4>

Creating a request is easy:

```php
use Opulence\Net\Http\Requests\Requests;
use Opulence\Net\Uri;

$request = new Request('GET', new Uri('https://example.com'));
```

You can set HTTP headers by calling

```php
$request->getHeaders()->add('Foo', 'bar');
```

You can either set the body via the constructor or via `Request::setBody()`:

```php
use Opulence\Net\Http\StringBody;

// Via constructor:
$body = new StringBody('foo');
$request = new Request('GET', new Uri('https://example.com'), null, $body);

// Or via setBody():
$request->setBody($body);
```

<h5 id="creating-request-from-globals">Creating a Request From Globals</h5>

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

In vanilla PHP, you can read URL-encoded form data via the `$_POST` superglobal.  Opulence gives you a helper to parse the body of form requests into a [dictionary](collections#hash-tables).

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

<h4 id="multipart-requests">Multipart Requests</h4>

Multipart requests contain multiple bodies, each with headers.  That's actually how file upload files work - each file gets a body with headers indicating the name, type, and size of the file.  Opulence can parse these multipart bodies into a `MultipartBody`, which extends `StreamBody`.  It contains an additional method to get the list of `MultipartBodyPart` objects that make up the body:

* `getParts() : MultipartBodyPart[]`

To parse a request body as a multipart body, call

```php
use Opulence\Net\Http\Requests\RequestParser;

$multipartBody = (new RequestParser)->readAsMultipart($request);
```

Each `MultipartBodyPart` contains the following methods:

* `getBody() : ?IHttpBody`
* `getHeaders() : HttpHeaders`

<h5 id="saving-uploaded-files">Saving Uploaded Files</h5>

To save a multipart body part to a file in a memory-efficient manner, read it as a stream and copy it to the destination path:

```php
$bodyStream = $multipartBodyPart->getBody()->readAsStream();
$bodyStream->rewind();
$bodyStream->copyToStream(new Stream(fopen('path/to/copy/to', 'w')));
```

<h5 id="getting-mime-type-of-body">Getting MIME Type of Body</h5>

To grab the MIME type of an HTTP body, call

```php
(new HttpBodyParser)->getMimeType($multipartBody->getBody());
```

<h5 id="creating-multipart-requests">Creating Multipart Requests</h5>

The Net library makes it easy to create a multipart request manually:

```php
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\MultipartBody;
use Opulence\Net\Http\MultipartBodyPart;
use Opulence\Net\Http\Requests\Request;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;

$body = new MultipartBody([
    new MultipartBodyPart(new HttpHeaders(), new StringBody('foo')),
    new MultipartBodyPart(new HttpHeaders(), new StringBody('bar'))
]);
$request = new Request(
    'GET',
    new Uri('https://example.com'),
    new HttpHeaders(),
    $body
);
```

<h4 id="getting-request-cookies">Getting Cookies</h4>

Opulence has a helper to grab cookies from request headers:

```php
use Opulence\Net\Http\Requests\RequestHeaderParser;

$cookies = (new RequestHeaderParser)->parseCookies($request->getHeaders());
$cookies->get('userid');
```

`RequestHeaderParser::parseCookies()` returns an [immutable dictionary](collections#immutable-hash-tables).

<h4 id="getting-client-ip-address">Getting Client IP Address</h4>

If you use the [`RequestFactory`](#creating-request-from-globals) to create your request, the client IP address will be added to the request property `CLIENT_IP_ADDRESS`.  To make it easier to grab this value, you can use `RequestParser` to retrieve it:

```php
use Opulence\Net\Http\Requests\RequestParser;

$clientIPAddress = (new RequestParser)->getClientIPAddress($request);
```

This will take into consideration any [trusted proxy header values](#trusted-proxies) when determining the original client IP address.

<h4 id="serializing-requests">Serializing Requests</h4>

You can serialize a request per <a href="https://tools.ietf.org/html/rfc7230" target="_blank">RFC 7230</a> by casting it to a string:

```php
echo (string)$request;
```

By default, this will use <a href="https://tools.ietf.org/html/rfc7230#section-5.3.1" target="_blank">origin-form</a> for the request target, but you can override the request type via the constructor:

```php
use Opulence\Net\Http\Requests\RequestTargetTypes;

$request = new Request(
    'GET',
    new Uri('https://example.com/foo?bar'),
    null,
    null,
    null,
    '1.1',
    RequestTargetTypes::AUTHORITY_FORM
);
```

The following request target types may be used:

* <a href="https://tools.ietf.org/html/rfc7230#section-5.3.2" target="_blank">`RequestTargetTypes::ABSOLUTE_FORM`</a>
* <a href="https://tools.ietf.org/html/rfc7230#section-5.3.4" target="_blank">`RequestTargetTypes::ASTERISK_FORM`</a>
* <a href="https://tools.ietf.org/html/rfc7230#section-5.3.3" target="_blank">`RequestTargetTypes::AUTHORITY_FORM`</a>
* <a href="https://tools.ietf.org/html/rfc7230#section-5.3.1" target="_blank">`RequestTargetTypes::ORIGIN_FORM`</a>

<h2 id="responses">Responses</h2>

Responses are HTTP messages that are sent by servers back to the client.  They contain the following methods:

* `getBody() : ?IHttpBody`
* `getHeaders() : HttpHeaders`
* `getProtocolVersion() : string`
* `getReasonPhrase() : ?string`
* `getStatusCode() : int`
* `setBody(IHttpBody $body) : void`
* `setStatusCode(int $statusCode, ?string $reasonPhrase = null) : void`

<h4 id="creating-responses">Creating Responses</h4>

Creating a response is easy:

```php
use Opulence\Net\Http\Responses\Response;

$response = new Response();
```

This will create a 200 OK response.  If you'd like to set a different status code, you can either pass it in the constructor or via `Response::setStatusCode()`:

```php
$response = new Response(404);
// Or...
$response->setStatusCode(404);
```

By default, headers will be set to an empty [hash table](collections#hash-tables), and can be accessed via `Response::getHeaders()`:

```php
$response->getHeaders()->add('Content-Type', 'application/json');
```

You can pass the body via the constructor or via `Response::setBody()`:

```php
$response = new Response(200, null, new StringBody('foo'));
// Or...
$response->setBody(new StringBody('foo'));
```

<h5 id="response-formatters">Response Formatters</h5>

Opulence provides a few easy ways to create common responses.  For example, to create a JSON response, use `ResponseFormatter`:

```php
use Opulence\Net\Http\Responses\Response;
use Opulence\Net\Http\Responses\ResponseFormatter;

$response = new Response();
(new ResponseFormatter)->writeJson($response, ['foo' => 'bar']);
```

This will set the contents of the response, as well as the appropriate `Content-Type` headers.

You can also create a redirect response:

```php
$response = new Response();
(new ResponseFormatter)->redirectToUri($response, 'http://example.com');
```

<h4 id="setting-response-cookies">Setting Cookies</h4>

Cookies are headers that are automatically appended to each request from the client to the server.  To set one, use `ResponseHeaderFormatter`:

```php
use Opulence\Net\Http\Responses\Cookie;
use Opulence\Net\Http\Responses\ResponseHeaderFormatter;

(new ResponseHeaderFormatter)->setCookie(
    $response->getHeaders(),
    new Cookie('userid', 123, 3600)
);
```

`Cookie` accepts the following parameters:

```php
public function __construct(
    string $name,
    $value,
    $expiration = null, // Either Unix timestamp or DateTime to expire
    ?string $path = null,
    ?string $domain = null,
    bool $isSecure = false,
    bool $isHttpOnly = true,
    ?string $sameSite = null
)
```

Use `ResponseHeaderFormatter::setCookies()` to set multiple cookies at once.

<h5 id="deleting-response-cookies">Deleting Cookies</h5>

To delete a cookie on the client, call

```php
(new ResponseHeaderFormatter)->deleteCookie($response->getHeaders(), 'userid');
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

<h4 id="serializing-responses">Serializing Responses</h4>

Opulence can serialize responses per <a href="https://tools.ietf.org/html/rfc7230" target="_blank">RFC 7230</a>:

```php
echo (string)$response;
```

<h2 id="uris">URIs</h2>

A URI identifies a resource, typically over a network.  They contain such information as the scheme, host, port, path, query string, and fragment.  Opulence represents them in `Opulence\Net\Uri`, and they include the following methods:

* `__toString() : string`
* `getAuthority(bool $includeUserInfo = true) : ?string`
* `getFragment() : ?string`
* `getHost() : ?string`
* `getPassword() : ?string`
* `getPath() : ?string`
* `getPort() : ?int`
* `getQueryString() : ?string`
* `getScheme() : ?string`
* `getUser() : ?string`

To create an instance of `Uri`, pass the raw URI string into the constructor:

```php
use Opulence\Net\Uri;

$uri = new Uri('https://example.com/foo?bar=baz#blah');
```

<h4 id="uris-parsing-query-string-parameters">Parsing Query String Parameters</h4>

`Uri::getQueryString()` returns the raw query string.  To parse them into an [immutable dictionary](collections#immutable-hash-tables) (similar to PHP's `$_GET`), use `UriParser`:

```php
use Opulence\Net\UriParser;

$uri = new Uri('https://example.com?foo=bar');
$queryStringParams = (new UriParser)->parseQueryString($uri);
echo $queryStringParams->get('foo'); // "bar"
```