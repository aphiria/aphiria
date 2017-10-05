<h1>Requests</h1>

<h2>Create a request from globals</h2>

```php
$request = (new RequestFactory)->createFromGlobals($_SERVER, $_COOKIE, $_FILES);
```

<h2>Get/set headers</h2>

```php
$request->getHeaders()->add('Foo', 'bar');
$request->getHeaders()->get('Foo');
```

<h2>Read the request body as a string</h2>

```php
$request->getBody()->readAsString();
// Or...
(string)request->getBody();
```

<h2>Get the uploaded files</h2>

```php
$uploadedFiles = $request->getUploadedFiles();
```

<h2>Read a chunk of a stream body</h2>

```php
$request->getBody()->readAsStream()->read(64);
```

<h2>Get/set request properties</h2>

```php
$request->getProperties()->add('foo', 'bar');
$request->getProperties()->get('foo');
```

<h2>Set trusted proxy IP addresses</h2>

```php
$factory = new RequestFactory(['192.168.1.1', '192.168.1.2']);
$request = $factory->createFromGlobals($_SERVER, $_COOKIE, $_FILES);
```

<h2>Get client IP address</h2>

```php
// Create request (must use RequestFactory)
$request->getProperties()->get('CLIENT_IP_ADDRESS');
```

<h1>Request Parsers</h1>

<h2>Get a query string parameter</h2>

```php
$userId = (new UriParser)->parseQueryString($request->getUri())->get('userId');
```

<h2>Get a cookie</h2>

```php
$userId = (new HttpRequestHeaderParser)->parseCookie($request->getHeaders())->get('userId');
```

<h2>Get all form input</h2>

```php
$formData = (new HttpRequestMessageParser)->parseFormInput($request);
```

<h2>Get a specific form input</h2>

```php
$email = (new HttpRequestMessageParser)->parseFormInput($request)->get('email');
```

<h2>Check if the request was JSON</h2>

```php
$isJson = (new HttpRequestHeaderParser)->isJson($request->getHeaders());
```

<h2>Read body as JSON</h2>

```php
$json = (new HttpRequestMessageParser)->parseJson($request);
```

<h1>Responses</h1>

<h2>Create a response</h2>

```php
$response = new Response();
```

<h2>Get/set headers</h2>

```php
$response->getHeaders()->add('Foo', 'bar');
$response->getHeaders()->get('Foo');
```

<h2>Specify a string body</h2>

```php
$response = new Response();
$response->setBody(new StringBody('This is my response'));
```

<h2>Specify a stream body</h2>

```php
$response = new Response();
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('This is my response');
$response->setBody(new StreamBody($stream));
```

<h2>Actually write the response to the output stream</h2>

```php
$response = new Response();
// Set the body...
(new ResponseWriter)->writeResponse($response);
```

Or specify the output stream to send to (defaults to PHP's output buffer):

```php
$outputStream = new Stream(fopen('php://temp', 'r+'));
(new ResponseWriter($outputStream))->writeResponse($response);
```

<h1>Response formatters</h1>

<h2>Create a JSON response</h2>

```php
$response = new Response();
(new HttpResponseMessageFormatter)->writeJson($response, $someArray);
```

Or manually:

```php
$response = new Response();
$response->setBody(new StringBody(json_encode($someArray)));
$response->getHeaders()->add('Content-Type', 'application/json');
```

<h2>Create a redirect response</h2>

```php
$response = new Response();
(new HttpResponseMessageFormatter)->redirectToUri($response, 'https://google.com');
```

Or manually:

```php
$response = new Response(302);
$response->getHeaders()->add('Location', 'https://google.com');
```

<h2>Set a cookie</h2>

```php
$response = new Response();
$cookie = new Cookie('userid', '123', new DateTime('+1 day'));
(new HttpResponseHeaderFormatter)->setCookie($response->getHeaders(), $cookie);
```

<h1>URI</h1>

<h2>Create URI from string</h2>

```php
$uri = Uri::createFromString('http://foo.com/bar?baz');
```

<h2>Serialize URI to string</h2>

```php
(string)$uri;
```