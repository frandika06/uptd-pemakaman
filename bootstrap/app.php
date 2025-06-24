<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\ApiProtection;
use App\Http\Middleware\Editor;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\Kontributor;
use App\Http\Middleware\LastSeen;
use App\Http\Middleware\Operator;
use App\Http\Middleware\Penulis;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\VerifyRecaptcha;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        // api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CustomCors::class,
        ]);
        $middleware->redirectGuestsTo(fn(Request $request) => route('auth.index'));
        $middleware->alias([
            'pbh'           => PreventBackHistory::class,
            'LastSeen'      => LastSeen::class,
            'SuperAdmin'    => SuperAdmin::class,
            'Admin'         => Admin::class,
            'Editor'        => Editor::class,
            'Penulis'       => Penulis::class,
            'Kontributor'   => Kontributor::class,
            'Operator'      => Operator::class,
            'json'          => ForceJsonResponse::class,
            'ApiProtection' => ApiProtection::class,
            'recaptcha'     => VerifyRecaptcha::class,
        ]);

        // middleware json ke dalam grup api
        $middleware->group('api', [
            'throttle:60,1,ip',
            'json',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $exception, Request $request) {
            // Cek apakah request adalah dari API
            if ($request->expectsJson() || $request->is('api/*')) {
                // 1. ValidationException (422)
                if ($exception instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Validation failed',
                        'errors'  => $exception->errors(),
                    ], \Illuminate\Http\JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                // 2. ModelNotFoundException (404)
                if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Resource not found',
                    ], \Illuminate\Http\JsonResponse::HTTP_NOT_FOUND);
                }

                // 3. NotFoundHttpException (404)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Endpoint not found',
                    ], \Illuminate\Http\JsonResponse::HTTP_NOT_FOUND);
                }

                // 4. MethodNotAllowedHttpException (405)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'HTTP method not allowed',
                    ], \Illuminate\Http\JsonResponse::HTTP_METHOD_NOT_ALLOWED);
                }

                // 5. AuthenticationException (401)
                if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unauthenticated',
                    ], \Illuminate\Http\JsonResponse::HTTP_UNAUTHORIZED);
                }

                // 6. AuthorizationException (403)
                if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Forbidden',
                    ], \Illuminate\Http\JsonResponse::HTTP_FORBIDDEN);
                }

                // 7. ThrottleRequestsException (429)
                if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Too many requests',
                    ], \Illuminate\Http\JsonResponse::HTTP_TOO_MANY_REQUESTS);
                }

                // 8. QueryException (500)
                if ($exception instanceof \Illuminate\Database\QueryException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Database error',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 9. BadRequestHttpException (400)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Bad request',
                    ], \Illuminate\Http\JsonResponse::HTTP_BAD_REQUEST);
                }

                // 10. ServiceUnavailableHttpException (503)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Service unavailable',
                    ], \Illuminate\Http\JsonResponse::HTTP_SERVICE_UNAVAILABLE);
                }

                // 11. UnsupportedMediaTypeHttpException (415)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unsupported media type',
                    ], \Illuminate\Http\JsonResponse::HTTP_UNSUPPORTED_MEDIA_TYPE);
                }

                // 12. ConflictHttpException (409)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\ConflictHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Conflict',
                    ], \Illuminate\Http\JsonResponse::HTTP_CONFLICT);
                }

                // 13. UnprocessableEntityHttpException (422)
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unprocessable entity',
                    ], \Illuminate\Http\JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                // 14. FileException (500) - Untuk masalah saat mengunggah file
                if ($exception instanceof \Symfony\Component\HttpFoundation\File\Exception\FileException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'File error',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 15. InvalidArgumentException (500) - Kesalahan parameter atau argumen tidak valid
                if ($exception instanceof \InvalidArgumentException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Invalid argument',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 16. RuntimeException (500) - Error terkait runtime, seperti pembagian dengan nol
                if ($exception instanceof \RuntimeException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Runtime error',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 17. LogicException (500) - Kesalahan logika
                if ($exception instanceof \LogicException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Logic error',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 18. BindingResolutionException (500) - Jika ada masalah dengan dependency injection
                if ($exception instanceof \Illuminate\Contracts\Container\BindingResolutionException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Dependency resolution error',
                        'error'   => $exception->getMessage(),
                    ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                }

                // 19. MissingScopeException (403) - Jika user tidak memiliki izin atau scope API
                if ($exception instanceof \Laravel\Passport\Exceptions\MissingScopeException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Forbidden - missing scope',
                    ], \Illuminate\Http\JsonResponse::HTTP_FORBIDDEN);
                }

                // 20. ValidationException - Gagal Autentikasi JWT (Misalnya pada Laravel JWT)
                if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Invalid token',
                    ], \Illuminate\Http\JsonResponse::HTTP_UNAUTHORIZED);
                }

                // Fallback untuk Error Internal
                return response()->json([
                    'status'  => false,
                    'message' => 'Something went wrong',
                    'error'   => $exception->getMessage(),
                ], \Illuminate\Http\JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Jika bukan request API, kembalikan exception seperti biasa
            return null;
        });
    })
    ->create();
