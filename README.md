<h1>Opulence's Net Library</h1>

<h2>Ideas</h2>

* Should set the HTTP protocol version, matched route variables in the request message's `properties`

<h2>Requests</h2>

<h3>Create a request from globals</h3>

```php
$request = (new RequestFactory)->createFromGlobals($_SERVER, $_COOKIE, $_FILES);
```

<h3>Read the request body as a string</h3>

```php
// Create request...
$request->getBody()->readAsString();
// Or...
(string)request->getBody();
```

<h3>Get a query string var</h3>

```php
// Create request...
$userId = (new HttpRequestMessageParser)->getQueryStringParam($request, 'userId');
```

<h3>Get a cookie</h3>

```php
// Create request...
$userId = (new HttpRequestHeaderParser)->getCookie($request->getHeaders(), 'userId');
```

<h3>Get all form data</h3>

```php
// Create request...
$formData = (new HttpRequestMessageParser)->getFormData($request);
```

<h3>Get a specific form input</h3>

```php
// Create request...
$email = (new HttpRequestMessageParser)->getInput($request, 'email');
```

<h3>Get the uploaded files</h3>

```php
// Create request...
$uploadedFiles = $request->getUploadedFiles();
```

<h3>Check if the request was JSON</h3>

```php
// Create request...
$isJson = (new HttpRequestHeaderParser)->isJson($request->getHeaders());
```

<h3>Read a chunk of a stream body</h3>

```php
// Create request...
$request->getBody()->readAsStream()->read(64);
```

<h3>Get/set request properties</h3>

```php
// Create request...
$request->getProperties()->get('foo');
$request->getProperties()->set('foo', 'bar');
```

<h3>Set trusted proxy IP addresses</h3>

```php
$factory = new RequestFactory(['192.168.1.1', '192.168.1.2']);
$request = $factory->createFromGlobals($_SERVER, $_COOKIE, $_FILES);
```

<h3>Get client IP address</h3>

```php
// Create request (must use RequestFactory)
$request->getProperties()->get('CLIENT_IP_ADDRESS');
```

<h2>Responses</h2>

<h3>Create a response</h3>

```php
$response = new Response();
```

<h3>Create a JSON response</h3>

```php
$response = (new JsonHttpResponseFactory)->createResponse($someArray);

// Or, manually:
$response = new Response();
$response->setBody(new StringBody(json_encode($someArray)));
$response->getHeaders()->set('Content-Type', 'application/json');
```

<h3>Create a redirect response</h3>

```php
$response = (new RedirectHttpResponseFactory)->createResponse('https://google.com');
```

<h3>Set a cookie</h3>

```php
$response = new Response();
$cookie = new Cookie('userid', '123', new DateTime('+1 day'));
(new HttpResponseHeaderFormatter)->setCookie($response->getHeaders(), $cookie);
```

<h3>Specify a string body</h3>

```php
$response = new Response();
$response->setBody(new StringBody('This is my response'));
```

<h3>Specify a stream body</h3>

```php
$response = new Response();
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('This is my response');
$response->setBody(new StreamBody($stream));
```

<h3>Actually write the response to the output stream</h3>

```php
$response = new Response();
// Set the body...
(new ResponseWriter)->writeResponse($response);

// Or specify the output stream to send to (defaults to PHP's output buffer):
$outputStream = new Stream(fopen('php://temp', 'r+'));
(new ResponseWriter($outputStream))->writeResponse($response);
```