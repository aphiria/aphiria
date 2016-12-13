<h1>Opulence's Net Library</h1>

<h2>Requests</h2>

<h3>Read the request body as a string</h3>
```php
$request = (new RequestFactory)->createFromGlobals($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES, $_ENV);
$request->getBody()->readAsString();
```

<h3>Get a query string var</h3>
```php
// Create request...
$userId = (new HttpRequestMessageParser)->getQueryVar($request, "userId");
```

<h3>Get a cookie</h3>
```php
// Create request...
$userId = (new HttpRequestHeaderParser)->getCookie($request->getHeaders(), "userId");
```

<h3>Get form data</h3>
```php
// Create request...
$email = (new HttpRequestHeaderParser)->getFormData($request, "email");
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

<h2>Responses</h2>

<h3>Create a response</h3>
```php
$response = new Response();

// Or, with a custom output stream (wrapper around php://output is the default):
$response = new Response(new BufferStream());
```

<h3>Create a JSON response</h3>
```php
$response = (new JsonHttpResponseFactory)->createResponse($someArray);

// Or, manually:
$response = new Response();
$response->setBody(new StringBody(json_encode($someArray)));
$response->getHeaders()->set("Content-Type", "application/json");
```

<h3>Create a redirect response</h3>
```php
$response = (new RedirectHttpResponseFactory)->createResponse("https://google.com");
```

<h3>Set a cookie</h3>
```php
$response = new Response();
$cookie = new Cookie("userid", "123");
(new HttpResponseHeaderFormatter)->setCookie($response->getHeaders(), $cookie);
```

<h3>Write the response as a stream</h3>
```php
$response = new Response();
$response->setBody(new StringBody("This is my response"));
```

<h3>Actually write the response to the output stream</h3>
```php
$response = new Response();
// Set the body...
$response->getBody()->writeToStream($response->getOutputStream());
```