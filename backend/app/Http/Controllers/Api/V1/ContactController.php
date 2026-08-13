<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Http\JsonResponse;

class ContactController extends BaseController
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'message' => $request->validated('message'),
        ]);

        return $this->success(
            data: [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'message' => $contact->message,
                'createdAt' => $contact->created_at?->toIso8601String(),
            ],
            message: 'Pesan Anda telah berhasil dikirim. Terima kasih!',
            status: 201
        );
    }
}
