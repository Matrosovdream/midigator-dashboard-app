<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Http\Actions\Api\V1\Search\GlobalSearchAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, GlobalSearchAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
