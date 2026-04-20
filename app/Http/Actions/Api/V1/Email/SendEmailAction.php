<?php

namespace App\Http\Actions\Api\V1\Email;

use App\Services\Emails\EmailSendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendEmailAction
{
    public function __construct(private EmailSendService $sender) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'case_type' => ['required', 'in:chargeback,prevention,order,order_validation,rdr'],
            'case_id' => ['required', 'integer'],
            'to_email' => ['required', 'email'],
            'template_id' => ['nullable', 'integer'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $record = $this->sender->queue(
            $request->user(),
            $data['case_type'],
            (int) $data['case_id'],
            $data,
        );

        unset($record['Model']);
        return response()->json($record, 202);
    }
}
