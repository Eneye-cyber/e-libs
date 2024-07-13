<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="APIs For E-Libs",
 *    version="1.0.0",
 * ),
 *   @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 *    description="JWT Authorization header using the Bearer scheme. Example: 'Authorization: Bearer {token}'"
 *   )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
