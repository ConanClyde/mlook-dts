<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type_id' => 'required|exists:document_types,id',
            'urgency_level' => 'required|in:low,normal,high,urgent',
            'remarks' => 'nullable|string|max:500',
            'deadline' => 'nullable|date|after:today',
            'files' => 'nullable|array|max:10', // Maximum 10 files
            'files.*' => [
                'file',
                'max:20480', // 20MB = 20480 KB
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,zip,rar',
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Document title is required.',
            'title.max' => 'Document title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'document_type_id.required' => 'Please select a document type.',
            'document_type_id.exists' => 'Selected document type is invalid.',
            'urgency_level.required' => 'Please select urgency level.',
            'urgency_level.in' => 'Invalid urgency level selected.',
            'deadline.after' => 'Deadline must be a future date.',
            'files.max' => 'You can upload a maximum of 10 files.',
            'files.*.file' => 'Each upload must be a valid file.',
            'files.*.max' => 'Each file must not exceed 20MB.',
            'files.*.mimes' => 'Invalid file type. Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPG, JPEG, PNG, GIF, ZIP, RAR.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'document_type_id' => 'document type',
            'files.*' => 'file',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation: Check total upload size
            if ($this->hasFile('files')) {
                $totalSize = 0;
                foreach ($this->file('files') as $file) {
                    $totalSize += $file->getSize();
                }

                // Maximum total size: 100MB
                $maxTotalSize = 100 * 1024 * 1024; // 100MB in bytes
                if ($totalSize > $maxTotalSize) {
                    $validator->errors()->add(
                        'files',
                        'Total file size must not exceed 100MB. Current total: '.round($totalSize / 1024 / 1024, 2).'MB'
                    );
                }
            }

            // Validate file extensions match MIME types
            if ($this->hasFile('files')) {
                foreach ($this->file('files') as $index => $file) {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $mimeType = $file->getMimeType();

                    // Check for suspicious file extensions
                    $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'sh', 'app', 'deb', 'rpm'];
                    if (in_array($extension, $dangerousExtensions)) {
                        $validator->errors()->add(
                            "files.{$index}",
                            "File type '.{$extension}' is not allowed for security reasons."
                        );
                    }
                }
            }
        });
    }
}
