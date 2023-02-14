<h1><strong><code>ANU-CRUD</code> PHP</strong> FRAMEWORK USAGE</h1>

<br>

<h3>@author: <strong>Anubis</strong></h3>
<h3>@license: <strong>MIT</strong></h3>

<br>

<h2>Framework usage:</h2>

<ul>
    <li>
        <a href="#htaccess">The .htaccess file</a>
    </li>
    <li>
        <a href="#index">The index.html file</a>
    </li>
    <li>
        <a href="#app">The app.php file</a>
    </li>
    <ul>
        <li>
            <a href="#constants-and-imports">Defining constants and importing</a>
        </li>
        <li>
            <a href="#router-and-request">Instantiating the Router with its Request parameter</a>
        </li>
        <li>
            <a href="#router-methods">Router methods</a>
        </li>
        <li>
            <a href="#ui-routes">Routes for UI</a>
        </li>
        <li>
            <a href="#xhr-routes">Routes for XMLHttpRequests</a>
        </li>
        <ul>
            <li>
                <a href="#accessing-url-params">Accessing URL params</a>
            </li>
            <li>
                <a href="#accessing-url-query-string">Accessing params from URL query string</a>
            </li>
            <li>
                <a href="#accessing-request-body">Accessing request body</a>
            </li>
        </ul>
    </ul>
</ul>

<br>
<hr>

<h1><strong><code>ANU-CRUD</code> PHP</strong> USAGE</h1>

<br>

To use <strong><code>ANU-CRUD</code> PHP</strong> backend server application, you will always need an <strong>index.html</strong>, an <strong>app.php</strong> and a <strong>.htaccess</strong> file.
- The <strong>index.html</strong> file is responsible for the presentation.
- The <strong>app.php</strong> file handles the API calls.
- The <strong>.htaccess</strong> file contains settings in order to call end-points using a CRUD URL syntax.

<br>

<h2 id="htaccess">The <strong>.htaccess</strong> file</h2>

- This file tells the server to redirect all backend calls to <strong>app.php</strong> file with the URL stored within <code>q</code> URI query param<br>
(i.e.: the "http://my-domain-name.com/my/endpoint/url" will be redirected to "http://my-domain-name.com/app.php?q=my/endpoint/url").<br>
With the settings below, handle backend calls in <strong>app.php</strong> file.

    ```
    <IfModule mod_rewrite.c>
      RewriteEngine on
      RewriteBase /
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{REQUEST_FILENAME} !-d
      RewriteRule ^(.*)$ app.php?q=$1 [NC,L]
    </IfModule>
    ```

<br>

<h2 id="index">The <strong>index.html</strong> file</h2>

- This file is required to be included for UI routes
- This includes the JS and CSS files within its <code>&lt;head&gt;</code> tag.

<br>

<h2 id="app">The <strong>app.php</strong> file</h2>

- This file will handle all URI calls within your domain.
- When UI routing framework is used (SPA), always include the <strong>index.html</strong> file to avoid 404 when reloading the page on a UI route other than index.
- When an <strong>XMLHttpRequest</strong> is triggered, it must start with <code>/app/</code> on the UI side (you can handle <code>/app/my-endpoint</code> on the server side as <code>/my-endpoint</code>).
- When dynamic URL-parts are used (e.g.: <code>/users/:users</code>), always keep in mind that every second parameter will be handled as a dynamic part (if exists - see next point).
- <strong><code>ANU-CRUD</code> PHP</strong> framework follows the CRUD URL design best practices:
    - The URLs describe resources using "<i>HTTP nouns</i>" and the corresponding values.
    In <strong><code>ANU-CRUD</code> PHP</strong>, there are some expectations ("<i>rules</i>") considering the RESTful URLs:
        - The <strong>nouns</strong> should aIlways be <strong>lowercase</strong>, in <strong>plural</strong> form! (i.e.: <code>/posts</code>)
        - To remove space between words, use <strong>kebab-case</strong> style (underscores are usually not visible when used as hyperlinks, because they are often underlined)!
        - Paths can indicate hierarchy of subresources (i.e.: <code>friends/:friends/posts</code>) but should not use more than <strong>2 levels</strong>.
        Every second parameter (i.e.: the second and fourth) is a (dynamic) identifier relating to the previous parameter ("<i>noun</i>"), i.e.: <code>friends/:friends/posts/:posts</code>.
        It is marked within the URL schema (the first argument of the 4 supported router methods) starting the '<code>:</code>' followed by the string value of the previous parameter
        ("<i>noun</i>", i.e.: <code>posts/:posts</code> URL schema will refer to <code>posts/1234</code> API call)
        - <i>URL query</i> can be used - typically used for search and filtering  (i.e.: <code>?page=0&limit=10</code>)

    <strong>Example</strong> - <i>valid URLs</i>:

    ```php
    '/about',
    '/users/:users',
    '/albums/:albums/images',
    '/albums/:albums/images/:images',
    '/friends/:friends/posts?page=0&limit=10',
    // Etc...
    ```

<h3 id="constants-and-imports">Defining constants and importing</h3>

```php
// If we need to use the $_SESSION superglobal array:
session_start();
define('BASE_PATH', dirname(realpath(__FILE__)) . '/');
define('CORE_PATH', BASE_PATH . 'core/');
// Must-have imports:
include_once(CORE_PATH . 'config.php');
include_once(CORE_PATH . 'Request.php');
include_once(CORE_PATH . 'Router.php');
```

<h3 id="router-and-request">Instantiating the <code>Router</code> with its <code>Request</code> parameter</h3>

```php
$router = new Router(new Request);
```

<h3 id="router-methods">Router methods</h3>

- There are <strong>4 methods</strong> ("<i>HTTP verbs</i>") to support <strong>CRUD</strong> (<strong>C</strong>reate - <strong>R</strong>ead - <strong>U</strong>pdate - <strong>D</strong>elete):
    - <strong>Create</strong> can be achieved using <strong>POST</strong> HTTP method (accessible as <code>$router->post()</code>).
        - Can have <strong>request body</strong> which is an associative array, accessible via <code>$request->getBody()</code> (parsed from HTTP Request body)
        - Can have access to the uploaded files (if any) via <code>$request->getFiles()</code>
        - <strong>Example</strong> -- Creating a new entry (we don't know the <strong>ID</strong> attribute yet)
    - <strong>Read</strong> can be achieved using <strong>GET</strong> HTTP method (accessible as <code>$router->get()</code>).
        - Can have <strong>URL query params</strong> which is an associative array accessible via <code>$request->getUrlQuery()</code> (parsed from the query part of the URL, i.e.: <code>/link?key1=val1&Key2=val2</code>)
        - <strong>Example</strong> -- Getting multiple items or data of a requested item (usually referred by an <strong>ID</strong> attribute)
    - <strong>Update</strong> can be achieved using <strong>PUT</strong> HTTP method (accessible as <code>$router->put()</code>).
        - Can have <strong>request body</strong> which is an associative array, accessible via <code>$request->getBody()</code> (parsed from HTTP Request body)
        - <strong>Example</strong> -- Updating an existing entry (we refer to the entry we want to modify using an <strong>ID</strong> attribute)
    - <strong>Delete</strong> can be achieved using <strong>DELETE</strong> HTTP method (accessible as <code>$router->delete()</code>).
        - Can have <strong>URL query params</strong> which is an associative array accessible via <code>$request->getUrlQuery()</code> (parsed from the query part of the URL, i.e.: <code>/link?key1=val1&Key2=val2</code>)
        - <strong>Example</strong> -- Deleting an item specified by an <strong>ID</strong> attribute

<h3 id="ui-routes">Routes for <strong>UI</strong></h3>

- Must include them to avoid 404 when refreshing page on one of the following URLs (<strong>ALWAYS</strong> use <strong>GET</strong> HTTP method):

    ```php
    $router->get('/', function($request) {
        include_once(BASE_PATH . '/index.html');
    });
    // Or:
    $router->get('/index', function($request) {
        include_once(BASE_PATH . '/index.html');
    });
    // For other pages:
    $router->get('/about', function($request) {
        include_once(BASE_PATH . '/index.html');
    });
    $router->get('/topics', function($request) {
        include_once(BASE_PATH . '/index.html');
    });
    $router->get('/topics/:topics', function($request) {
        include_once(BASE_PATH . '/index.html');
    });
    ```

<h3 id="xhr-routes">Routes for <strong>XMLHttpRequests</strong></h3>

- The <strong>URL params</strong> (those are parts of the end-point URL) can <strong>ALWAYS</strong> be accessed using the <code>$request->urlParams</code> associative array<br>
(i.e.: <code>$imageId</code> from route <code>'/images/:images'</code> can be extracted using <code>$imageId = $request->urlParams['images'];</code>).
- If handling  <strong>POST</strong> or <strong>PUT</strong> requests, the <strong>request body</strong> sent to server can be accessed as an associative array using <code>$request->getBody()</code>.
    - If file(s) were uploaded, you can access them via <code>$request->getFiles()</code>.<br>
    <strong>Sending files</strong> to the server only works using <strong>POST</strong> requests!
- If handling <strong>GET</strong> or <strong>DELETE</strong> HTTP requests, the <strong>URL query</strong> params can be accessed as an associative array using <code>$request->getUrlQuery()</code><br>
(i.e.: if <code>/posts?page=0&limit=10</code> has been called, <code>page</code> and <code>limit</code> query params will be accessible as <code>$request->getUrlQuery()['page']</code> and <code>$request->getUrlQuery()['limit']</code> respectively).

<h4 id="accessing-url-params">Accessing <strong>URL params</strong> (supported HTTP methods: <strong>GET</strong>, <strong>POST</strong>, <strong>PUT</strong> and <strong>DELETE</strong>)</h4>

```php
$router->get(
    "/albums/:albums/images/:images",
    function($request) {
        $albumId = $request->urlParams['albums'];
        $imageId = $request->urlParams['images'];

        $response = [
            'imageId' => $imageId,
            'albumId' => $albumId
        ];
        
        http_response_code(200);
        return json_encode($response);
    }
);
```

<h4 id="accessing-url-query-string">Accessing params from <strong>URL query string</strong> (supported HTTP methods: <strong>GET</strong> and <strong>DELETE</strong>)</h4>

```php
$router->get("/posts", function($request) { // Full URL is '/posts?page=0&limit=10'
    $query = $request->getUrlQuery();
    $posts = [];
    $start = (int) $query['page'];
    $limit = $start + (int) $query['limit'];

    for($i = $start; $i < $limit; $i++) {
        array_push($posts, [
            'id' => "post-$i",
            'content' => 'Lorem ipsum dolor sit amet...'
        ]);
    }

    $response = [
        'page' => $query['page'] + $query['limit'],
        'posts' => $posts
    ];
    
    http_response_code(200);
    return json_encode($response);
});
```

<h4 id="accessing-request-body">Accessing <strong>request body</strong> (supported HTTP methods: <strong>POST</strong> and <strong>PUT</strong>)</h4>

```php
$router->post('/send-message', function($request) {
    $data = $request->getBody();

    $response = [
        'author' => $data['userId'],
        'recipient' => $data['recipientId'],
        'msg' => $data['msg']
    ];

    http_response_code(200);
    return json_encode($response);
});
```

<h4 id="accessing-request-body">Accessing <strong>files</strong> (supported HTTP method: <strong>POST</strong>)</h4>

```php
$router->post('/upload-images', function($request) {
    $files = $request->getFiles();

    $response = [
        'fileName' => $files[0]['name'],
        'fileType' => $files[0]['type'],
        'fileSize' => $files[0]['size']
    ];

    http_response_code(200);
    return json_encode($response);
});
```

<strong>Example</strong> - <i>login and logout functionalities</i>:

```php
// Login via POST method:
$router->post('/login', function($request) {
    $currentTime = time();
    $currentDate = date("Y-m-d H:i:s", $currentTime);
    $expirationTime = $currentTime + (30 * 24 * 60 * 60);
    
    $response = $request->getBody();

    // Custom code here...

    // Store user ID in session
    $_SESSION['userId'] = $userId;

    // Set user ID and expiry date in cookie
    setcookie('userId', $userId, $expirationTime, '/');
    setcookie('expires', $expirationTime, $expirationTime, '/');

    // Return 200 status code (OK) and return response JSON
    http_response_code(200);
    return json_encode($response);
});
// Logout via GET method:
$router->get('/logout', function() {
    // End session and destroying user ID
    session_destroy();
    unset($_SESSION["userId"]);

    // Unset cookies
    setcookie('userId', null, -1, '/');
    setcookie('expires', null, -1, '/');

    // Redirect user to the index page
    header('Location: /');
});
```
