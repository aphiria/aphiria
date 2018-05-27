# Net

> **Note:** This library is still in development.

## Table of Contents
1. [Introduction](#introduction)
    1. [Requirements](#requirements)
    2. [Installation](#installation)
    3. [Why Not Use PSR-7?](#why-not-use-psr-7)
2. [Requests](#requests)
    1. [Creating Requests](#creating-requests)
    2. [Getting POST Data](#getting-post-data)
    3. [Getting Query String Data](#getting-query-string-data)
    4. [JSON Requests](#json-requests)
    5. [Multipart Requests](#multipart-requests)
    6. [Getting Cookies](#getting-request-cookies)
    7. [Getting Client IP Address](#getting-client-ip-address)
    8. [Header Parameters](#header-parameters)
    9. [Serializing Requests](#serializing-requests)
3. [Responses](#responses)
    1. [Creating Responses](#creating-responses)
    2. [JSON Responses](#json-responses)
    3. [Redirect Responses](#redirect-responses)
    4. [Setting Cookies](#setting-response-cookies)
    5. [Writing Responses](#writing-responses)
    6. [Serializing Responses](#serializing-responses)
4. [HTTP Headers](#http-headers)
5. [HTTP Bodies](#http-bodies)
    1. [String Bodies](#string-bodies)
    2. [Stream Bodies](#stream-bodies)
6. [URIs](#uris)
7. [Content Negotiation](#content-negotiation)
    1. [Media Type Formatters](#media-type-formatters)

<h2 id="introduction">Introduction</h2>

Opulence's network library provides better abstractions for HTTP requests, responses, bodies, headers, and URIs.  It also comes built-in with support for RFC-compliant content negotiation for request and response bodies.

<h4 id="requirements">Requirements</h4>

PHP POST request bodies are read from `$_POST` and `$_FILES` for form data and for uploaded files, respectively.  All other request methods must be manually parsed from the `php://input` stream.  To work around PHP's inconsistencies, Opulence requires the following setting in either a <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank">_.user.ini_</a> or your _php.ini_:

```
enable_post_data_reading = 0
```

This will disable automatically parsing POST data into `$_POST` and uploaded files into `$_FILES`.

> **Note:** If you're developing any non-Opulence applications on your web server, use <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank">_.user.ini_</a> to limit this setting to only your Opulence application.  Alternatively, you can add `php_value enable_post_data_reading 0` to an _.htaccess_ file or to your _httpd.conf_.

<h4 id="installation">Installation</h4>

To install the Net library, simply add `opulence/net: 1.0.*` to your _composer.json_.

<h4 id="why-not-use-psr-7">Why Not Use PSR-7?</h4>

PSR-7 was an attempt to standardize frameworks' HTTP components to be interopbile, but it contained many contested features:

1. Request and response immutability
    * This has often been considered cumbersome, bug-prone, and a bad use-case for immutability
    * The fact that some bodies' streams are readable exactly once breaks the idea of immutability
    * The `with*()` methods are more reminiscent of the <a href="https://en.wikipedia.org/wiki/Builder_pattern" target="_blank">builder pattern</a> rather than of a domain model
2. HTTP message bodies were streams
    * Bodies aren't inherently streams - they should be _readable as_ streams, and _writable to_ streams
3. PSR-7 improperly abstracted uploaded files
    * They are part of the body, not the request message

<h2 id="requests">Requests</h2>

Requests are HTTP messages sent by clients to servers.  They contain the following methods:

* `getBody(): ?IHttpBody`
* `getHeaders(): HttpHeaders`
* `getMethod(): string`
* `getProperties(): IDictionary`
* `getProtocolVersion(): string`
* `getUri(): Uri`
* `setBody(IHttpBody $body): void`

> **Note:** The properties dictionary is a useful place to store metadata about a request, eg route variables.

<h4 id="creating-requests">Creating Requests</h4>

Creating a request is easy:

```php
use Opulence\Net\Http\Request;
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
$request = new Request('POST', new Uri('https://example.com'), null, $body);

// Or via setBody():
$request->setBody($body);
```

<h5 id="creating-request-from-superglobals">Creating a Request From Superglobals</h5>

PHP has superglobal arrays that store information about the requests.  They're a mess, architecturally-speaking.  Opulence attempts to insulate developers from the nastiness of superglobals by giving you a simple method to create requests and responses.  To create a request, use `RequestFactory`:

```php
use Opulence\Net\Http\RequestFactory;

$request = (new RequestFactory)->createRequestFromSuperglobals($_SERVER);
```

Opulence reads all the information it needs from the `$_SERVER` superglobal - it doesn't need the others.

<h5 id="trusted-proxies">Trusted Proxies</h5>

If you're using a load balancer or some sort of proxy server, you'll need to add it to the list of trusted proxies.  You can also use your proxy to set custom, trusted headers.  You may specify them in the factory constructor:

```php
// The client IP will be read from the "X-My-Proxy-Ip" header when using a trusted proxy
$factory = new RequestFactory(['192.168.1.1'], ['HTTP_CLIENT_IP' => 'X-My-Proxy-Ip']);
$request = $factory->createRequestFromSuperglobals($_SERVER);
```

<h4 id="getting-post-data">Getting POST Data</h4>

In vanilla PHP, you can read URL-encoded form data via the `$_POST` superglobal.  Opulence gives you a helper to parse the body of form requests into a [dictionary](https://www.opulencephp.com/docs/1.1/collections#hash-tables).

```php
use Opulence\Net\Http\Formatting\RequestParser;

// Let's assume the raw body is "email=foo%40bar.com"
$formInput = (new RequestParser)->readAsFormInput($request);
echo $formInput->get('email'); // "foo@bar.com"
```

<h4 id="getting-query-string-data">Getting Query String Data</h4>

In vanilla PHP, query string data is read from the `$_GET` superglobal.  In Opulence, it's stored in the request's URI.  `Uri::getQueryString()` returns the raw query string - to return it as an [immutable dictionary](https://www.opulencephp.com/docs/1.1/collections#immutable-hash-tables), use `RequestParser`:

```php
use Opulence\Net\Http\Formatting\RequestParser;

// Assume the query string was "?foo=bar"
$queryStringParams = (new RequestParser)->parseQueryString($request);
echo $queryStringParams->get('foo'); // "bar"
```

<h4 id="json-requests">JSON Requests</h4>

To check if a request is a JSON request, call

```php
use Opulence\Net\Http\Formatting\RequestParser;

$isJson = (new RequestParser)->isJson($request);
```

Rather than having to parse a JSON body yourself, you can use `RequestParser` to do it for you:

```php
use Opulence\Net\Http\Formatting\RequestParser;

$json = (new RequestParser)->readAsJson($request);
```

<h4 id="multipart-requests">Multipart Requests</h4>

Multipart requests contain multiple bodies, each with headers.  That's actually how file uploads work - each file gets a body with headers indicating the name, type, and size of the file.  Opulence can parse these multipart bodies into a `MultipartBody`, which extends [`StreamBody`](#stream-bodies).  It contains additional methods to get the boundary and the list of `MultipartBodyPart` objects that make up the body:

* `getBoundary(): string`
* `getParts(): MultipartBodyPart[]`

You can check if a request is a multipart request:

```php
use Opulence\Net\Http\Formatting\RequestParser;

$isMultipart = (new RequestParser)->isMultipart($request);
```

To parse a request body as a multipart body, call

```php
use Opulence\Net\Http\Formatting\RequestParser;

$multipartBody = (new RequestParser)->readAsMultipart($request);
```

Each `MultipartBodyPart` contains the following methods:

* `getBody(): ?IHttpBody`
* `getHeaders(): HttpHeaders`

<h5 id="saving-uploaded-files">Saving Uploaded Files</h5>

To save a multipart body's parts to files in a memory-efficient manner, read each part as a stream and copy it to the destination path:

```php
foreach ($multipartBody->getParts() as $multipartBodyPart) {
    $bodyStream = $multipartBodyPart->getBody()->readAsStream();
    $bodyStream->rewind();
    $bodyStream->copyToStream(new Stream(fopen('path/to/copy/to/' . uniqid(), 'w')));
}
```

<h5 id="getting-mime-type-of-body">Getting MIME Type of Body</h5>

To grab the MIME type of an HTTP body, call

```php
(new RequestParser)->getMimeType($multipartBodyPart);
```

<h5 id="creating-multipart-requests">Creating Multipart Requests</h5>

The Net library makes it straightforward to create a multipart request manually.  The following example creates a request to upload two images:

```php
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\MultipartBody;
use Opulence\Net\Http\MultipartBodyPart;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Uri;

// Build the first image's headers and body
$image1Headers = new HttpHeaders();
$image1Headers->add('Content-Disposition', 'form-data; name="image1"; filename="foo.png"');
$image1Headers->add('Content-Type', 'image/png');
$image1Body = new StreamBody(fopen('path/to/foo.png', 'r'));

// Build the second image's headers and body
$image2Headers = new HttpHeaders();
$image2Headers->add('Content-Disposition', 'form-data; name="image2"; filename="bar.png"');
$image2Headers->add('Content-Type', 'image/png');
$image2Body = new StreamBody(fopen('path/to/bar.png', 'r'));

// Build the request's headers and body
$body = new MultipartBody([
    new MultipartBodyPart($image1Headers, $image1Body),
    new MultipartBodyPart($image2Headers, $image2Body)
]);
$headers = new HttpHeaders();
$headers->add('Content-Type', "multipart/form-data; boundary={$body->getBoundary()}");

// Build the request
$request = new Request(
    'POST',
    new Uri('https://example.com'),
    $headers,
    $body
);
```

<h4 id="getting-request-cookies">Getting Cookies</h4>

Opulence has a helper to grab cookies from request headers as an [immutable dictionary](https://www.opulencephp.com/docs/1.1/collections#immutable-hash-tables):

```php
use Opulence\Net\Http\Formatting\RequestParser;

$cookies = (new RequestParser)->parseCookies($request);
$cookies->get('userid');
```

<h4 id="getting-client-ip-address">Getting Client IP Address</h4>

If you use the [`RequestFactory`](#creating-request-from-superglobals) to create your request, the client IP address will be added to the request property `CLIENT_IP_ADDRESS`.  To make it easier to grab this value, you can use `RequestParser` to retrieve it:

```php
use Opulence\Net\Http\Formatting\RequestParser;

$clientIPAddress = (new RequestParser)->getClientIPAddress($request);
```

> **Note:** This will take into consideration any [trusted proxy header values](#trusted-proxies) when determining the original client IP address.

<h4 id="header-parameters">Header Parameters</h4>

Some header values are semicolon delimited, eg `Content-Type: text/html; charset=utf-8`.  It's sometimes convenient to grab those key => value pairs:

```php
$contentTypeValues = $requestParser->parseParameters($request, 'Content-Type');
// Keys without values will return null:
echo $contentTypeValues->get('text/html'); // null
echo $contentTypeValues->get('charset'); // "utf-8"
```

<h4 id="serializing-requests">Serializing Requests</h4>

You can serialize a request per <a href="https://tools.ietf.org/html/rfc7230#section-3" target="_blank">RFC 7230</a> by casting it to a string:

```php
echo (string)$request;
```

By default, this will use <a href="https://tools.ietf.org/html/rfc7230#section-5.3.1" target="_blank">origin-form</a> for the request target, but you can override the request type via the constructor:

```php
use Opulence\Net\Http\RequestTargetTypes;

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

* `getBody(): ?IHttpBody`
* `getHeaders(): HttpHeaders`
* `getProtocolVersion(): string`
* `getReasonPhrase(): ?string`
* `getStatusCode(): int`
* `setBody(IHttpBody $body): void`
* `setStatusCode(int $statusCode, ?string $reasonPhrase = null): void`

<h4 id="creating-responses">Creating Responses</h4>

Creating a response is easy:

```php
use Opulence\Net\Http\Response;

$response = new Response();
```

This will create a 200 OK response.  If you'd like to set a different status code, you can either pass it in the constructor or via `Response::setStatusCode()`:

```php
$response = new Response(404);
// Or...
$response->setStatusCode(404);
```

<h5 id="response-headers">Response Headers</h5>

You can set response [headers](#http-headers) via `Response::getHeaders()`:

```php
$response->getHeaders()->add('Content-Type', 'application/json');
```

<h5 id="response-bodies">Response Bodies</h5>

You can pass the [body](#http-bodies) via the response constructor or via `Response::setBody()`:

```php
$response = new Response(200, null, new StringBody('foo'));
// Or...
$response->setBody(new StringBody('foo'));
```

<h4 id="json-responses">JSON Responses</h4>

Opulence provides an easy way to create common responses.  For example, to create a JSON response, use `ResponseFormatter`:

```php
use Opulence\Net\Http\Formatting\ResponseFormatter;
use Opulence\Net\Http\Response;

$response = new Response();
(new ResponseFormatter)->writeJson($response, ['foo' => 'bar']);
```

This will set the contents of the response, as well as the appropriate `Content-Type` headers.

<h4 id="redirect-responses">Redirect Responses</h4>

You can also create a redirect response:

```php
use Opulence\Net\Http\Formatting\ResponseFormatter;
use Opulence\Net\Http\Response;

$response = new Response();
(new ResponseFormatter)->redirectToUri($response, 'http://example.com');
```

<h4 id="setting-response-cookies">Setting Cookies</h4>

Cookies are headers that are automatically appended to each request from the client to the server.  To set one, use `ResponseFormatter`:

```php
use Opulence\Net\Http\Cookie;
use Opulence\Net\Http\Formatting\ResponseFormatter;

(new ResponseFormatter)->setCookie(
    $response,
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

Use `ResponseFormatter::setCookies()` to set multiple cookies at once.

<h5 id="deleting-response-cookies">Deleting Cookies</h5>

To delete a cookie on the client, call

```php
(new ResponseFormatter)->deleteCookie($response, 'userid');
```

<h4 id="writing-responses">Writing Responses</h4>

Once you're ready to start sending the response back to the client, you can use `ResponseWriter`:

```php
use Opulence\Net\Http\Formatting\ResponseWriter;

(new ResponseWriter)->writeResponse($response);
```

By default, this will write the response to the `php://output` stream.  You can override the stream it writes to via the constructor:

```php
$outputStream = new Stream(fopen('path/to/output', 'w'));
(new ResponseWriter($outputStream))->writeResponse($response);
```

<h4 id="serializing-responses">Serializing Responses</h4>

Opulence can serialize responses per <a href="https://tools.ietf.org/html/rfc7230#section-3" target="_blank">RFC 7230</a>:

```php
echo (string)$response;
```

<h2 id="http-headers">HTTP Headers</h2>

Headers provide metadata about the HTTP message.  In Opulence, they're implemented by `Opulence\Net\Http\HttpHeaders`, which extends  [`Opulence\Collections\HashTable`](https://www.opulencephp.com/docs/1.1/collections#hash-tables).  On top of the methods provided by `HashTable`, they also provide the following methods:

* `getFirst(string $name): mixed`
* `tryGetFirst(string $name, &$value): bool`

> **Note:** Header names that are passed into the methods in `HttpHeaders` are automatically normalized to Train-Case.  In other words, `foo_bar` will become `Foo-Bar`.

<h2 id="http-bodies">HTTP Bodies</h2>

HTTP bodies contain data associated with the HTTP message, and are optional.  They're represented by `Opulence\Net\Http\IHttpBody`.  They provide a few methods to read and write their contents to streams and to strings:

* `__toString(): string`
* `readAsStream(): IStream`
* `readAsString(): string`
* `writeToStream(IStream $stream): void`

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

<h2 id="uris">URIs</h2>

A URI identifies a resource, typically over a network.  They contain such information as the scheme, host, port, path, query string, and fragment.  Opulence represents them in `Opulence\Net\Uri`, and they include the following methods:

* `__toString(): string`
* `getAuthority(bool $includeUserInfo = true): ?string`
* `getFragment(): ?string`
* `getHost(): ?string`
* `getPassword(): ?string`
* `getPath(): ?string`
* `getPort(): ?int`
* `getQueryString(): ?string`
* `getScheme(): ?string`
* `getUser(): ?string`

To create an instance of `Uri`, pass the raw URI string into the constructor:

```php
use Opulence\Net\Uri;

$uri = new Uri('https://example.com/foo?bar=baz#blah');
```

<h2 id="content-negotiation">Content Negotiation</h2>

Content negotiation is a process between the client and server to determine how to best process a request and serve content back to the client.  This negotiation is typically done via headers, where the client says "Here's the type of content I'd prefer (eg JSON, XMl, etc)", and the server trying to accomodate the client's preferences.  For example, the process can involve negotiating the following for requests and responses per the <a href="https://www.w3.org/Protocols/rfc2616/rfc2616-sec12.html" target="_blank">HTTP spec</a>:

* Content type
    * Controlled by the `Content-Type` and `Accept` headers
    * Dictates the [media type formatter](#media-type-formatters) to use
* Character encoding
    * Controlled by the `Content-Type` and `Accept-Charset` headers
* Language
    * Controlled by the `Content-Language` and `Accept-Language` headers

To negotiate the request content, simply call:

```php
use Opulence\Net\Http\Formatting\ContentNegotiator;
use Opulence\Net\Http\Formatting\FormUrlEncodedMediaTypeFormatter;
use Opulence\Net\Http\Formatting\JsonMediaTypeFormatter;

$mediaTypeFormatters = [
    new FormUrlEncodedMediaTypeFormatter(),
    new JsonMediaTypeFormatter()
];
$contentNegotiator = new ContentNegotiator();
$result = $contentNegotiator->negotiateRequestContent($request, $mediaTypeFormatters);
```

<h4 id="media-type-formatters">Media Type Formatters</h4>

Media type formatters can read and write a particular data format to a stream.  You can get the media type formatter from `ContentNegotiationResult`, and use it to deserialize a request body to a particular type (`User` in this example):

```php
$mediaTypeFormatter = $result->getMediaTypeFormatter();
$mediaTypeFormatter->readFromStream(User::class, $request->getBody());
```

Similarly, you can serialize a value and write it to the response body:

```php
$mediaTypeFormatter->writeToStream($valueToWrite, $response->getBody());
```

  Opulence provides the following formatters out of the box:

* `FormUrlEncodedMediaTypeFormatter`
* `JsonMediaTypeFormatter`

Under the hood, media type formatters use Opulence's <a href="https://github.com/opulencephp/serialization" target="_blank">serialization library</a> to read and write values to a particular data format.