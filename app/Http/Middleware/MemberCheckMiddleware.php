<?php

namespace App\Http\Middleware;

use Closure;
use GenTux\Jwt\GetsJwtToken;

class MemberCheckMiddleware
{
	use GetsJwtToken;

	public function handle($request, Closure $next)
	{
		$payload = $this->jwtPayload();
		
		if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
			return $next($request);
		} else {
			return response(['error' => 'Not member API.'], 401);
		}
	}
}
