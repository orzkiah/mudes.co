<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Forces every concrete Form Request to explicitly write authorize() and
 * rules(), rather than silently inheriting FormRequest's default allow-all
 * authorize(). authorize() must delegate to a Policy, never check a role
 * inline (IMPLEMENTATION_RULES.md §8).
 */
abstract class BaseFormRequest extends FormRequest
{
    abstract public function authorize(): bool;

    /**
     * @return array<string, mixed>
     */
    abstract public function rules(): array;
}
