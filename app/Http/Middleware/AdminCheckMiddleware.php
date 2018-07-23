<?php

namespace App\Http\Middleware;

use Closure;
use GenTux\Jwt\GetsJwtToken;

class AdminCheckMiddleware
{
	use GetsJwtToken;

	public function handle($request, Closure $next)
	{
		$payload = $this->jwtPayload();
		
		if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'admin') {
			return $next($request);
		} else {
			return response(['error' => 'Admin permission is required.'], 401);
		}
	}
}
