<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ForwardDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'to_user_id' => ['required_without:receiver_ids', 'integer', 'exists:users,id'],
            'receiver_ids' => ['required_without:to_user_id', 'array', 'min:1'],
            'receiver_ids.*' => ['integer', 'exists:users,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'intent' => ['nullable', 'in:route,approval,review'],
            'deadline' => ['nullable', 'date', 'after:now'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $document = $this->route('document');
            
            if (!$document) {
                return;
            }

            // Get receiver IDs
            $receiverIds = $this->input('receiver_ids', []);
            if ($this->filled('to_user_id')) {
                $receiverIds = [$this->input('to_user_id')];
            }

            // Prevent forwarding to self
            if (in_array(auth()->id(), $receiverIds)) {
                $validator->errors()->add('receiver_ids', 'You cannot forward a document to yourself.');
            }

            // Prevent forwarding completed or archived documents
            if (in_array($document->status, [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])) {
                $validator->errors()->add('document', 'Cannot forward completed or archived documents.');
            }

            // Check recipient permissions
            $document->loadMissing('documentType');
            foreach ($receiverIds as $userId) {
                $user = User::find($userId);
                
                if (!$user) {
                    continue;
                }

                // Prevent forwarding to students
                if ($user->isStudent()) {
                    $validator->errors()->add('receiver_ids', 'Documents cannot be forwarded to students.');
                    break;
                }

                // Check if recipient can receive this document type
                if ($document->documentType && !auth()->user()->isAdmin()) {
                    $recipientRole = $user->getUserRole();
                    if (!$document->documentType->canBeReceivedBy($recipientRole)) {
                        $validator->errors()->add(
                            'receiver_ids', 
                            "User {$user->full_name} ({$recipientRole}) cannot receive this document type."
                        );
                        break;
                    }
                }
            }

            // Validate approval intent
            if ($this->input('intent') === 'approval') {
                foreach ($receiverIds as $userId) {
                    $user = User::find($userId);
                    if ($user && !$user->hasAdminPrivileges() && !$user->isAdmin()) {
                        $validator->errors()->add(
                            'intent',
                            'Only administrative users (Admin, Registrar, Dean) can receive documents for approval.'
                        );
                        break;
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('intent')) {
            $this->merge([
                'intent' => 'route',
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'receiver_ids.required_without' => 'Please select at least one recipient.',
            'receiver_ids.min' => 'Please select at least one recipient.',
            'receiver_ids.*.exists' => 'One or more selected recipients are invalid.',
            'to_user_id.required_without' => 'Please select a recipient.',
            'to_user_id.exists' => 'The selected recipient is invalid.',
            'deadline.after' => 'The deadline must be a future date.',
            'priority.in' => 'Invalid priority level selected.',
        ];
    }
}
