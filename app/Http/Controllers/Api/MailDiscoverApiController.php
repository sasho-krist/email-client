<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Services\MailDiscoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MailDiscoverApiController extends Controller
{
    use ApiResponses;

    public function store(Request $request, MailDiscoveryService $discoveryService): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $settings = $discoveryService->discover($validated['email']);

            if ($settings === null) {
                return $this->fail('Не са открити автоматични настройки за този домейн.', 404);
            }

            return $this->ok(['discovery' => $settings]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Невалиден имейл адрес.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Грешка при откриване на настройки.', 500);
        }
    }
}
