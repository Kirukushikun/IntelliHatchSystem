# IntelliHatchSystem
Intelligence and Insights from hatchery inputs with the help of AI

Resolved issue on why photo uploading did not work:
- The issue was that the photo was not being uploaded to the server
- The issue was resolved by adding the following to the bootstrap\app.php taking note of the trust proxy:
```
$middleware->trustProxies(at: '*');
```

Example:
```
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
    ]);
})
```