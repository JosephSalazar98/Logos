<?php

/*
|--------------------------------------------------------------------------
| Set up 404 handler
|--------------------------------------------------------------------------
|
| Leaf provides a default 404 page, but you can create your own
| 404 handler by uncommenting the code below. The function
| you set here will be called when a 404 error is encountered
|
*/
// app()->set404(function() {
//     response()->markup('Page not found', 404);
// });

/*
|--------------------------------------------------------------------------
| Set up 500 handler
|--------------------------------------------------------------------------
|
| Leaf provides a default 500 page, but you can create your own
| 500 handler by uncommenting the code below. The function
| you set here will be called when a 500 error is encountered
|
*/
// app()->setErrorHandler(function() {
//     response()->markup('An error occurred', 500);
// });

/*
|--------------------------------------------------------------------------
| Set middleware for all routes
|--------------------------------------------------------------------------
|
| You can use app()->use() to load middleware for all
| routes in your application.
|
*/
// app()->use(ExampleMiddleware::class);

/*
|--------------------------------------------------------------------------
| Your application routes
|--------------------------------------------------------------------------
|
| Leaf MVC automatically loads all files in the routes folder that
| start with "_". We call these files route partials. An example
| partial has been created for you.
|
| If you want to manually load routes, you can
| create a file that doesn't start with "_" and manually require
| it here like so:
|
*/
// require __DIR__ . '/custom-route.php';
