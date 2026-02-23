<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Unusualify\Modularity\Services\MessageStage;

/**
 * Provides consistent JSON/redirect response patterns for auth controllers.
 */
trait RespondsWithJsonOrRedirect
{
    /**
     * Send success response (JSON or redirect).
     *
     * @param  array<string, mixed>  $data  Additional data for JSON response (e.g. redirector, message)
     */
    protected function sendSuccessResponse(Request $request, string $message, string $redirectUrl, array $data = []): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse(array_merge([
                'message' => $message,
                'variant' => MessageStage::SUCCESS,
                'redirector' => $redirectUrl,
            ], $data), 200);
        }

        return redirect($redirectUrl)->with('status', $message);
    }

    /**
     * Send failed response (JSON or redirect with errors).
     */
    protected function sendFailedResponse(
        Request $request,
        string $message,
        string $field = 'email',
        int $jsonStatus = 200
    ): JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->wantsJson()) {
            return new JsonResponse([
                $field => [$message],
                'message' => $message,
                'variant' => MessageStage::WARNING,
            ], $jsonStatus);
        }

        return redirect()->back()
            ->withInput($request->only($field))
            ->withErrors([$field => $message]);
    }

    /**
     * Send validation failed response.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected function sendValidationFailedResponse(Request $request, $validator): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
                'variant' => MessageStage::WARNING,
            ], 200);
        }

        return redirect()->back()
            ->withErrors($validator->errors())
            ->withInput($request->input());
    }
}
