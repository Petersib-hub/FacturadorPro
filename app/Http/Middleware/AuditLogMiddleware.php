<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse $response */
        $response = $next($request);

        if (in_array($request->method(), ['POST','PUT','PATCH','DELETE'])) {
            $routeName = optional($request->route())->getName();
            $routeParams = optional($request->route())->parameters();

            $entityType = null;
            $entityId   = null;

            if (isset($routeParams['invoice'])) {
                $entityType = 'invoice';
                $entityId   = is_object($routeParams['invoice']) ? $routeParams['invoice']->id : $routeParams['invoice'];
            } elseif (isset($routeParams['budget'])) {
                $entityType = 'budget';
                $entityId   = is_object($routeParams['budget']) ? $routeParams['budget']->id : $routeParams['budget'];
            }

            $action = $routeName ?: ($entityType ? $entityType.'.'.$request->method() : 'unknown');

            AuditLog::create([
                'user_id'     => optional($request->user())->id,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'meta'        => [
                    'status'     => $response->getStatusCode(),
                    'payload'    => $request->except(['password','password_confirmation','_token']),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 190),
                ],
                'route'  => $request->path(),
                'method' => $request->method(),
                'ip'     => $request->ip(),
            ]);
        }

        return $response;
    }
}
