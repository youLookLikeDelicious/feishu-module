<?php

namespace Modules\Feishu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Feishu\Services\SyncService;

class FeishuController extends Controller
{
    public function __construct(public SyncService $syncService)
    {
    }

    public function syncDoc(Request $request)
    {
        return $this->syncService->syncDoc($request);   
    }
}
