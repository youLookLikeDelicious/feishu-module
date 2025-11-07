<?php
namespace Modules\Feishu\Services;

use App\Services\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SyncService extends Service
{
    public function syncDoc(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'doc_id' => 'required',
        ]);

        Artisan::queue('feishu:cli', [
            'cmd' => 'sync-doc',
            '--doc-id' => $validated['doc_id'],
            '--wiki' => $validated['type'] === 'wiki'
        ]);

        return response()->noContent();
    }
}