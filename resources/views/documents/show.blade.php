@extends('layouts.app')

@section('title', 'Document Details - MLOOK')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-72 max-w-full">
        <!-- Top Navigation Bar -->
        <nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4 min-w-0">
                        <!-- Mobile Menu Button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0">
                            <x-icon name="bars-3" class="w-6 h-6" />
                        </button>
                        <!-- Page Title -->
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white truncate">Document Details</h1>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4 flex-shrink-0">
                        <x-notifications />
                        <x-dark-mode-toggle />
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="px-4 sm:px-6 lg:px-8 py-8 max-w-full">
            @php
                // Default route based on user type
                $documentsHomeRoute = auth()->user()->isStudent()
                    ? route('documents.my-documents')
                    : route('documents.index');
                
                // Get the previous URL from referer or session
                $previousUrl = url()->previous();
                
                // Determine back URL and label
                $backUrl = $documentsHomeRoute;
                $backLabel = 'Back to Documents';
                
                if ($previousUrl !== url()->current() && str_starts_with($previousUrl, url('/'))) {
                    // Check if previous URL is a documents-related page and set appropriate label
                    if (str_contains($previousUrl, '/inbox')) {
                        $backUrl = $previousUrl;
                        $backLabel = 'Back to Inbox';
                    } elseif (str_contains($previousUrl, '/sent')) {
                        $backUrl = $previousUrl;
                        $backLabel = 'Back to Sent';
                    } elseif (str_contains($previousUrl, '/archive')) {
                        $backUrl = $previousUrl;
                        $backLabel = 'Back to Archive';
                    } elseif (str_contains($previousUrl, '/completed')) {
                        $backUrl = $previousUrl;
                        $backLabel = 'Back to Completed';
                    } elseif (str_contains($previousUrl, '/documents')) {
                        $backUrl = $previousUrl;
                        $backLabel = 'Back to My Documents';
                    }
                }
            @endphp
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ $backUrl }}" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                    {{ $backLabel }}
                </a>
            </div>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6 sm:mb-8 max-w-full">
                <div class="min-w-0 flex-1 overflow-hidden">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2 break-words">{{ $document->title }}</h1>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 break-words">Tracking #: <span class="font-mono break-all">{{ $document->tracking_number }}</span></p>
                </div>
                <div class="flex gap-2 flex-shrink-0 flex-wrap">
                    @php
                        $statusColors = [
                            'draft' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                            'routing' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                            'received' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                            'in_review' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                            'for_approval' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
                            'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                            'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                            'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                            'returned' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                            'archived' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                        ];
                        $urgencyColors = [
                            'low' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                            'normal' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                            'high' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                            'urgent' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                        ];
                        $statusColor = $statusColors[$document->status] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                        $urgencyColor = $urgencyColors[$document->urgency_level] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                    @endphp
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusColor }}">
                        {{ \Illuminate\Support\Str::headline($document->status) }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $urgencyColor }} capitalize">
                        {{ $document->urgency_level }}
                    </span>
                    @if($document->expiration_date)
                        @php
                            $isExpired = $document->is_expired;
                            $daysUntilExpiration = !$isExpired ? \Carbon\Carbon::parse($document->expiration_date)->diffInDays(now(), false) : 0;
                            $expirationColor = $isExpired 
                                ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400' 
                                : ($daysUntilExpiration <= 7 
                                    ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400' 
                                    : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400');
                        @endphp
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $expirationColor }}">
                            @if($isExpired)
                                ⚠️ Expired
                            @elseif($daysUntilExpiration <= 7)
                                ⏰ Expires in {{ abs($daysUntilExpiration) }}d
                            @else
                                📅 Expires {{ \Carbon\Carbon::parse($document->expiration_date)->format('M d, Y') }}
                            @endif
                        </span>
                    @endif
                    <span class="hidden">{{ $document->urgency_level }}
                    </span>
                </div>
            </div>

            <!-- Read-Only Notice for Final Statuses -->
            @if(in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]))
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-icon name="lock-closed" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-1">Document is Read-Only</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-400">
                            This document has reached a final status ({{ \Illuminate\Support\Str::headline($document->status) }}) and can no longer be edited or forwarded.
                            @if(auth()->user()->isAdmin())
                                <span class="font-medium">As an admin, you can still change the status if needed.</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="space-y-6 overflow-hidden">
                    <!-- Document Info Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Document Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Document Type</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">{{ $document->documentType->name }}</p>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created By</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">{{ $document->creator->full_name }}</p>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created On</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">{{ $document->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Current Holder</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">{{ $document->currentHolder ? $document->currentHolder->full_name : 'N/A' }}</p>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Origin Department</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">{{ $document->originDepartment ? $document->originDepartment->name : 'N/A' }}</p>
                            </div>
                            @if($document->expiration_date)
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Expiration Date</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words">
                                    {{ \Carbon\Carbon::parse($document->expiration_date)->format('M d, Y') }}
                                    @if($document->is_expired)
                                        <span class="ml-2 text-red-600 dark:text-red-400 font-semibold">(Expired)</span>
                                    @elseif(\Carbon\Carbon::parse($document->expiration_date)->diffInDays(now(), false) <= 7)
                                        <span class="ml-2 text-orange-600 dark:text-orange-400 font-semibold">(Expiring Soon)</span>
                                    @endif
                                </p>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Auto-Archive on Expiration</label>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $document->auto_archive_on_expiration ? 'Yes' : 'No' }}
                                </p>
                            </div>
                            @endif
                            @if($document->tags && $document->tags->count() > 0)
                            <div class="md:col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Tags</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($document->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" 
                                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        @if($document->description)
                            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words whitespace-pre-wrap">{{ $document->description }}</p>
                            </div>
                        @endif
                        
                        @if($document->remarks)
                            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Remarks</label>
                                <p class="text-sm text-gray-900 dark:text-white break-words whitespace-pre-wrap">{{ $document->remarks }}</p>
                            </div>
                        @endif
                        
                        <!-- Attachments Section -->
                        <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Attached Files</label>
                            </div>
                            
                            @if(!in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]) && ($document->current_holder_id === auth()->id() || auth()->user()->isAdmin()))
                                <!-- Drag and Drop File Upload -->
                                <div class="mb-4" id="inline-attachment-upload" data-auto-upload="true" data-document-id="{{ $document->id }}">
                                    <x-file-upload 
                                        name="files[]" 
                                        :multiple="true" 
                                        :maxSize="20" 
                                        acceptedTypes=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
                                </div>
                            @endif
                            
                            <div class="space-y-3" id="attachments-list" data-attachments-list>
                                @if($document->attachments->count() > 0)
                                    @foreach($document->attachments as $attachment)
                                        @php
                                            $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $iconMap = [
                                                'pdf' => ['bg' => 'bg-red-500', 'text' => 'PDF'],
                                                'doc' => ['bg' => 'bg-blue-500', 'text' => 'DOC'],
                                                'docx' => ['bg' => 'bg-blue-500', 'text' => 'DOCX'],
                                                'xls' => ['bg' => 'bg-green-500', 'text' => 'XLS'],
                                                'xlsx' => ['bg' => 'bg-green-500', 'text' => 'XLSX'],
                                                'jpg' => ['bg' => 'bg-purple-500', 'text' => 'JPG'],
                                                'jpeg' => ['bg' => 'bg-purple-500', 'text' => 'JPEG'],
                                                'png' => ['bg' => 'bg-purple-500', 'text' => 'PNG'],
                                            ];
                                            $icon = $iconMap[$ext] ?? ['bg' => 'bg-gray-500', 'text' => strtoupper($ext)];
                                        @endphp
                                        <div class="file-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center gap-4" data-attachment-id="{{ $attachment->id }}">
                                            <!-- File Icon/Thumbnail -->
                                            @if($isImage)
                                                <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                                    <img src="{{ asset('storage/' . $attachment->file_path) }}" 
                                                         alt="{{ $attachment->file_name }}" 
                                                         class="w-full h-full object-cover"
                                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-purple-500 text-white text-xs font-semibold\'>{{ strtoupper($ext) }}</div>';">
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 w-12 h-12 {{ $icon['bg'] }} rounded-lg flex items-center justify-center text-white font-semibold text-xs">
                                                    {{ $icon['text'] }}
                                                </div>
                                            @endif
                                            
                                            <!-- File Info -->
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                                   target="_blank"
                                                   download="{{ $attachment->file_name }}"
                                                   class="block">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                        {{ $attachment->file_name }}
                                                    </p>
                                                </a>
                                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $attachment->formatted_file_size }}</span>
                                                    @if($attachment->uploader)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">•</span>
                                                        <span class="text-xs text-gray-600 dark:text-gray-400">
                                                            Uploaded by <span class="font-medium text-gray-900 dark:text-white">{{ $attachment->uploader->full_name }}</span>
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Uploaded
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex-shrink-0 flex items-center gap-2">
                                                @php
                                                    $isPreviewable = in_array(strtolower($ext), ['pdf', 'jpg', 'jpeg', 'png']);
                                                @endphp
                                                @if($isPreviewable)
                                                    <button type="button" onclick="previewFile({{ $attachment->id }}, '{{ addslashes($attachment->file_name) }}', '{{ $attachment->file_type }}', '{{ asset('storage/' . $attachment->file_path) }}', '{{ $attachment->formatted_file_size }}')" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-blue-500 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Preview">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <a href="{{ asset('storage/' . $attachment->file_path) }}" 
                                                   target="_blank"
                                                   download="{{ $attachment->file_name }}"
                                                   class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-orange-500 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                                   title="Download">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                                                    </svg>
                                                </a>
                                                @if(!in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]) && ($attachment->uploaded_by === auth()->id() || $document->current_holder_id === auth()->id() || auth()->user()->isAdmin()))
                                                    <button type="button" onclick="deleteAttachment({{ $attachment->id }})" class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif($document->file_path)
                                    @php
                                        $ext = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $iconMap = [
                                            'pdf' => ['bg' => 'bg-red-500', 'text' => 'PDF'],
                                            'doc' => ['bg' => 'bg-blue-500', 'text' => 'DOC'],
                                            'docx' => ['bg' => 'bg-blue-500', 'text' => 'DOCX'],
                                            'xls' => ['bg' => 'bg-green-500', 'text' => 'XLS'],
                                            'xlsx' => ['bg' => 'bg-green-500', 'text' => 'XLSX'],
                                            'jpg' => ['bg' => 'bg-purple-500', 'text' => 'JPG'],
                                            'jpeg' => ['bg' => 'bg-purple-500', 'text' => 'JPEG'],
                                            'png' => ['bg' => 'bg-purple-500', 'text' => 'PNG'],
                                        ];
                                        $icon = $iconMap[$ext] ?? ['bg' => 'bg-gray-500', 'text' => strtoupper($ext)];
                                    @endphp
                                    <div class="file-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center gap-4">
                                        <!-- File Icon/Thumbnail -->
                                        @if($isImage)
                                            <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                                <img src="{{ route('documents.download', $document) }}" 
                                                     alt="{{ $document->file_name }}" 
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-purple-500 text-white text-xs font-semibold\'>{{ strtoupper($ext) }}</div>';">
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 w-12 h-12 {{ $icon['bg'] }} rounded-lg flex items-center justify-center text-white font-semibold text-xs">
                                                {{ $icon['text'] }}
                                            </div>
                                        @endif
                                        
                                        <!-- File Info -->
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('documents.download', $document) }}" class="block">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                    {{ $document->file_name }}
                                                </p>
                                            </a>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Main Document</span>
                                                <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Uploaded
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('documents.download', $document) }}" 
                                               class="p-2 min-w-[36px] min-h-[36px] flex items-center justify-center text-gray-400 hover:text-orange-500 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                               title="Download">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No attachments yet.</p>
                                @endif
                            </div>
                        </div>

                        @if($document->created_by === auth()->id() || auth()->user()->isAdmin())
                        <!-- QR Code Section -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Quick Access QR Code</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="bg-white dark:bg-gray-700 p-1 rounded-lg border-2 border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                    <img src="{{ route('documents.qr', $document) }}?t={{ time() }}" 
                                         alt="QR Code for {{ $document->tracking_number }}" 
                                         class="w-32 h-32 object-contain"
                                         onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'><rect width=\'200\' height=\'200\' fill=\'white\'/><text x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' font-family=\'Arial\' font-size=\'14\' fill=\'black\'>QR Code Error</text></svg>'"
                                         loading="lazy">
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                        Scan this QR code to quickly access this document from any device. Share it with authorized personnel for easy tracking.
                                    </p>
                                    <a href="{{ route('documents.qr', $document) }}" 
                                       download="{{ $document->tracking_number }}-qr.png"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors text-sm font-medium">
                                        <x-icon name="arrow-down-tray" class="w-4 h-4" />
                                        Download QR Code
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Document Tags -->
                    <x-document-tags :document="$document" />
                    
                    <!-- Document Signatures -->
                    <x-document-signatures :document="$document" />
                    
                    @if($document->created_by === auth()->id() || auth()->user()->isAdmin())
                    <!-- Document Timeline -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6">Document Timeline</h3>
                        
                        <div class="relative">
                            @forelse($document->tracking as $track)
                                @php
                                    $iconMap = [
                                        \App\Models\DocumentTracking::ACTION_CREATED => 'plus',
                                        \App\Models\DocumentTracking::ACTION_FORWARDED => 'paper-airplane',
                                        \App\Models\DocumentTracking::ACTION_ACKNOWLEDGED => 'check-circle',
                                        \App\Models\DocumentTracking::ACTION_REVIEW_STARTED => 'play',
                                        \App\Models\DocumentTracking::ACTION_REVIEW_COMPLETED => 'check-badge',
                                        \App\Models\DocumentTracking::ACTION_SENT_FOR_APPROVAL => 'clipboard-document-check',
                                        \App\Models\DocumentTracking::ACTION_APPROVED => 'hand-thumb-up',
                                        \App\Models\DocumentTracking::ACTION_REJECTED => 'hand-thumb-down',
                                        \App\Models\DocumentTracking::ACTION_COMPLETED => 'check-circle',
                                        \App\Models\DocumentTracking::ACTION_RETURNED => 'arrow-uturn-left',
                                        \App\Models\DocumentTracking::ACTION_ARCHIVED => 'archive-box',
                                    ];
                                    $iconName = $iconMap[$track->action] ?? 'information-circle';
                                    $timestamp = $track->sent_at ?? $track->created_at;
                                @endphp
                                <div class="flex gap-4 pb-6 last:pb-0">
                                    <div class="relative flex flex-col items-center">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-orange-500 dark:bg-orange-600 flex items-center justify-center text-white z-10">
                                            <x-icon :name="$iconName" class="w-4 h-4 sm:w-5 sm:h-5" />
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-full bg-gray-300 dark:bg-gray-600 absolute top-8 sm:top-10"></div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 -mt-1 pb-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-sm sm:text-base font-medium text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::headline($track->action) }}</p>
                                            @if($timestamp)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $timestamp->format('M d, Y h:i A') }}
                                            </span>
                                            @endif
                                        </div>
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                            @if($track->fromUser)
                                                From: {{ $track->fromUser->full_name }}
                                                @if($track->fromDepartment)
                                                    ({{ $track->fromDepartment->name }})
                                                @endif
                                            @endif
                                            @if($track->toUser)
                                                <br>To: {{ $track->toUser->full_name }}
                                                @if($track->toDepartment)
                                                    ({{ $track->toDepartment->name }})
                                                @endif
                                            @endif
                                        </p>
                                        @if($track->remarks)
                                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                <span class="font-medium">Remarks:</span> {{ $track->remarks }}
                                            </p>
                                        @endif
                                        @if($track->instructions)
                                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                <span class="font-medium">Instructions:</span> {{ $track->instructions }}
                                            </p>
                                        @endif
                                        @if($track->received_at)
                                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                                Received: {{ $track->received_at->format('M d, Y h:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">No tracking history available.</p>
                            @endforelse
                        </div>
                    </div>
                    @endif
                    
                    @if($document->created_by === auth()->id() || auth()->user()->isAdmin())
                    <!-- Activity Log -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 max-w-full">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6">Activity Log</h3>
                        
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            @forelse($document->actions as $action)
                                @php
                                    $actionColors = [
                                        'created' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                        'viewed' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400',
                                        'forwarded' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
                                        'received' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400',
                                        'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                        'returned' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400',
                                        'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                        'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                        'downloaded' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
                                        'edited' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                        'deleted' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                    ];
                                    $actionColor = $actionColors[$action->action_type] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400';
                                @endphp
                                <div class="flex gap-3 items-start text-sm pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0 min-w-0">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $actionColor }} flex-shrink-0 capitalize">
                                        {{ str_replace('_', ' ', $action->action_type) }}
                                    </span>
                                    <div class="flex-1 min-w-0 overflow-hidden">
                                        <p class="text-sm text-gray-900 dark:text-white font-medium truncate">
                                            {{ $action->user->full_name }}
                                        </p>
                                        @if($action->remarks)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 break-words line-clamp-2">{{ $action->remarks }}</p>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 whitespace-nowrap">
                                        {{ $action->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">No activity recorded.</p>
                            @endforelse
                        </div>
                    </div>
                    @endif
                
                <!-- Comments Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6">Comments & Discussion</h3>
                    
                    @php
                        // Disable comments for final statuses
                        $isFinalStatus = in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]);
                        
                        $canComment = !$isFinalStatus && (
                            auth()->id() === $document->created_by 
                            || auth()->id() === $document->current_holder_id
                            || $document->tracking()->where(function($q) {
                                $q->where('from_user_id', auth()->id())
                                  ->orWhere('to_user_id', auth()->id());
                            })->exists()
                            || auth()->user()->isAdmin()
                        );
                    @endphp
                    
                    @if($canComment)
                    <!-- Add Comment Form -->
                    <form id="comment-form" class="mb-6">
                        @csrf
                        <div class="mb-3">
                            <textarea id="comment-text" 
                                      name="comment"
                                      rows="3" 
                                      required
                                      class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                                      placeholder="Add a comment or discussion note..."></textarea>
                        </div>
                        <div class="flex items-center justify-between">
                            @if(auth()->user()->isAdmin())
                            <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <input type="checkbox" id="is-internal" name="is_internal" value="1" class="mr-2 rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500">
                                <span>Internal comment (admin only)</span>
                            </label>
                            @else
                            <div></div>
                            @endif
                            <button type="submit" class="btn-primary text-sm min-h-[44px] px-4 sm:px-6">
                                <x-icon name="chat-bubble-left-right" class="w-4 h-4 mr-2" />
                                Post Comment
                            </button>
                        </div>
                    </form>
                    @endif
                    
                    <!-- Comments List -->
                    <div id="comments-list" class="space-y-4">
                        @forelse($document->comments()->with('user')->orderBy('created_at', 'desc')->get() as $comment)
                            @if(!$comment->is_internal || auth()->user()->isAdmin())
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0" id="comment-{{ $comment->id }}">
                                <div class="flex items-start gap-3">
                                    <!-- User Avatar -->
                                    <div class="flex-shrink-0">
                                        @if($comment->user->avatar)
                                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->full_name }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                        @else
                                            @php
                                                $firstLetter = strtoupper(substr($comment->user->first_name, 0, 1));
                                                $colors = [
                                                    'A' => ['bg' => 'bg-red-500', 'dark' => 'dark:bg-red-600'],
                                                    'B' => ['bg' => 'bg-orange-500', 'dark' => 'dark:bg-orange-600'],
                                                    'C' => ['bg' => 'bg-amber-500', 'dark' => 'dark:bg-amber-600'],
                                                    'D' => ['bg' => 'bg-yellow-500', 'dark' => 'dark:bg-yellow-600'],
                                                    'E' => ['bg' => 'bg-lime-500', 'dark' => 'dark:bg-lime-600'],
                                                    'F' => ['bg' => 'bg-green-500', 'dark' => 'dark:bg-green-600'],
                                                    'G' => ['bg' => 'bg-emerald-500', 'dark' => 'dark:bg-emerald-600'],
                                                    'H' => ['bg' => 'bg-teal-500', 'dark' => 'dark:bg-teal-600'],
                                                    'I' => ['bg' => 'bg-cyan-500', 'dark' => 'dark:bg-cyan-600'],
                                                    'J' => ['bg' => 'bg-sky-500', 'dark' => 'dark:bg-sky-600'],
                                                    'K' => ['bg' => 'bg-blue-500', 'dark' => 'dark:bg-blue-600'],
                                                    'L' => ['bg' => 'bg-indigo-500', 'dark' => 'dark:bg-indigo-600'],
                                                    'M' => ['bg' => 'bg-violet-500', 'dark' => 'dark:bg-violet-600'],
                                                    'N' => ['bg' => 'bg-purple-500', 'dark' => 'dark:bg-purple-600'],
                                                    'O' => ['bg' => 'bg-fuchsia-500', 'dark' => 'dark:bg-fuchsia-600'],
                                                    'P' => ['bg' => 'bg-pink-500', 'dark' => 'dark:bg-pink-600'],
                                                    'Q' => ['bg' => 'bg-rose-500', 'dark' => 'dark:bg-rose-600'],
                                                    'R' => ['bg' => 'bg-red-600', 'dark' => 'dark:bg-red-700'],
                                                    'S' => ['bg' => 'bg-orange-600', 'dark' => 'dark:bg-orange-700'],
                                                    'T' => ['bg' => 'bg-green-600', 'dark' => 'dark:bg-green-700'],
                                                    'U' => ['bg' => 'bg-teal-600', 'dark' => 'dark:bg-teal-700'],
                                                    'V' => ['bg' => 'bg-blue-600', 'dark' => 'dark:bg-blue-700'],
                                                    'W' => ['bg' => 'bg-indigo-600', 'dark' => 'dark:bg-indigo-700'],
                                                    'X' => ['bg' => 'bg-purple-600', 'dark' => 'dark:bg-purple-700'],
                                                    'Y' => ['bg' => 'bg-pink-600', 'dark' => 'dark:bg-pink-700'],
                                                    'Z' => ['bg' => 'bg-rose-600', 'dark' => 'dark:bg-rose-700'],
                                                ];
                                                $avatarColor = $colors[$firstLetter] ?? ['bg' => 'bg-gray-500', 'dark' => 'dark:bg-gray-600'];
                                            @endphp
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 {{ $avatarColor['bg'] }} {{ $avatarColor['dark'] }} rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ $firstLetter }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Comment Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ $comment->user->full_name }}</p>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            @if($comment->is_internal)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">
                                                    Internal
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 break-words whitespace-pre-wrap">{{ $comment->comment }}</p>
                                    </div>
                                    
                                    <!-- Delete Button -->
                                    @if(!in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]) && ($comment->user_id === auth()->id() || auth()->user()->isAdmin()))
                                    <button onclick="deleteComment({{ $comment->id }})" 
                                            class="flex-shrink-0 p-2 min-w-[36px] min-h-[36px] text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                            title="Delete comment">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                                <x-icon name="chat-bubble-left-right" class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                                No comments yet. Be the first to start the discussion!
                            </p>
                        @endforelse
                    </div>
                </div>
                
                <!-- Action Buttons (if current holder and authorized) -->
                @php
                    $pendingTracking = $document->tracking()->where('to_user_id', auth()->id())->whereNull('received_at')->latest()->first();
                    $lastInbound = $document->tracking()->where('to_user_id', auth()->id())->latest()->first();
                @endphp
                @if($document->current_holder_id === auth()->id() && !in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]))
                    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                        @if($pendingTracking)
                            <button onclick="acknowledgeTracking({{ $pendingTracking->id }})" class="btn-secondary text-sm w-full sm:w-auto min-h-[44px]">
                                <x-icon name="check-circle" class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Mark as Received</span>
                                <span class="sm:hidden">Mark Received</span>
                            </button>
                        @endif
                        @can('forward', $document)
                            <button onclick="openForwardModal()" class="btn-primary text-sm w-full sm:w-auto min-h-[44px]">
                                <x-icon name="paper-airplane" class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Forward Document</span>
                                <span class="sm:hidden">Forward</span>
                            </button>
                        @endcan
                        
                        @can('complete', $document)
                            @if($document->created_by !== auth()->id())
                            <button onclick="openCompleteModal()" class="btn-success text-sm w-full sm:w-auto min-h-[44px]">
                                <x-icon name="check-circle" class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Mark as Completed</span>
                                <span class="sm:hidden">Complete</span>
                            </button>
                            @endif
                        @endcan
                        @can('approve', $document)
                            <button onclick="openApproveModal()" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                                <x-icon name="hand-thumb-up" class="w-4 h-4 mr-2" />
                                Approve
                            </button>
                        @endcan
                        @can('reject', $document)
                            <button onclick="openRejectModal()" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                                <x-icon name="hand-thumb-down" class="w-4 h-4 mr-2" />
                                Reject
                            </button>
                        @endcan
                        @if($lastInbound && $document->current_holder_id === auth()->id() && $document->created_by !== auth()->id())
                            <button onclick="openReturnModal({{ $lastInbound->id }})" class="btn-secondary text-sm w-full sm:w-auto min-h-[44px]">
                                <x-icon name="arrow-uturn-left" class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Return to Sender</span>
                                <span class="sm:hidden">Return</span>
                            </button>
                        @endif
                @endif

                <!-- Admin Archive Button (for completed/approved documents) -->
                @if(auth()->user()->isAdmin() && in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED]) && $document->status !== \App\Models\Document::STATUS_ARCHIVED)
                    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                        <button onclick="archiveDocument({{ $document->id }})" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                            <x-icon name="archive-box" class="w-4 h-4 mr-2" />
                            <span class="hidden sm:inline">Archive Document</span>
                            <span class="sm:hidden">Archive</span>
                        </button>
                    </div>
                @endif

                <!-- Admin Status Override Button -->
                @if(auth()->user()->isAdmin())
                    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                        <button onclick="openChangeStatusModal()" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600 text-white rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                            <x-icon name="arrow-path" class="w-4 h-4 mr-2" />
                            <span class="hidden sm:inline">Change Status (Admin)</span>
                            <span class="sm:hidden">Change Status</span>
                        </button>
                    </div>
                @endif

                <!-- Delete Document Button (if user has permission and not final status) -->
                @if(!in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_APPROVED, \App\Models\Document::STATUS_REJECTED, \App\Models\Document::STATUS_ARCHIVED]))
                    @can('delete', $document)
                        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                            <button onclick="deleteDocument({{ $document->id }})" class="inline-flex items-center justify-center px-4 py-2.5 min-h-[44px] bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 text-white rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                                <x-icon name="trash" class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Delete Document</span>
                                <span class="sm:hidden">Delete</span>
                            </button>
                        </div>
                    @endcan
                @endif

                @if($document->current_holder_id === auth()->id() && !in_array($document->status, [\App\Models\Document::STATUS_COMPLETED, \App\Models\Document::STATUS_ARCHIVED]))
                    <div class="flex flex-wrap gap-3" style="display: none;">
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

<!-- Success Modal -->
<x-success-modal />

<!-- Delete Modal -->
<x-delete-modal />

<!-- File Preview Modal -->
<x-file-preview-modal :attachments="$document->attachments" />

<!-- Forward Document Modal -->
<div id="forwardModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeForwardModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Forward Document</h3>
            <button type="button" onclick="closeForwardModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="forwardForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div x-data="{
                search: '',
                selectedReceivers: [],
                showDropdown: false,
                users: @js($users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'type' => ucfirst($user->usertype),
                        'department' => $user->department ? $user->department->name : null,
                        'display' => $user->full_name . ' - ' . ucfirst($user->usertype) . ($user->department ? ' (' . $user->department->name . ')' : '')
                    ];
                })->toArray()),
                get filteredUsers() {
                    if (!this.search) return this.users.filter(u => !this.selectedReceivers.find(r => r.id === u.id));
                    const query = this.search.toLowerCase();
                    return this.users.filter(user => 
                        !this.selectedReceivers.find(r => r.id === user.id) &&
                        (user.name.toLowerCase().includes(query) ||
                        user.type.toLowerCase().includes(query) ||
                        (user.department && user.department.toLowerCase().includes(query)))
                    );
                },
                addReceiver(user) {
                    if (!this.selectedReceivers.find(r => r.id === user.id)) {
                        this.selectedReceivers.push(user);
                    }
                    this.search = '';
                    this.showDropdown = false;
                },
                removeReceiver(userId) {
                    this.selectedReceivers = this.selectedReceivers.filter(r => r.id !== userId);
                },
                closeDropdown() {
                    this.showDropdown = false;
                }
            }" 
            @click.away="showDropdown = false"
            class="relative">
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Forward To <span class="text-red-500">*</span>
                </label>
                
                <!-- Selected Receivers -->
                <div x-show="selectedReceivers.length > 0" class="mb-2 flex flex-wrap gap-2">
                    <template x-for="receiver in selectedReceivers" :key="receiver.id">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-lg text-xs">
                            <span x-text="receiver.name"></span>
                            <button type="button" @click="removeReceiver(receiver.id)" class="hover:text-orange-900 dark:hover:text-orange-100">
                                <x-icon name="x-mark" class="w-3.5 h-3.5" />
                            </button>
                            <input type="hidden" name="receiver_ids[]" :value="receiver.id">
                        </div>
                    </template>
                </div>
                
                <div class="relative">
                    <input 
                        type="text"
                        x-model="search"
                        @focus="showDropdown = true"
                        @click.stop="showDropdown = true"
                        @keydown.escape="showDropdown = false"
                        placeholder="Search and add recipients..."
                        class="w-full px-3 py-2 pr-10 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                    >
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                        <x-icon name="magnifying-glass" class="w-5 h-5" />
                    </div>
                </div>
                
                <!-- Hidden input for primary receiver (first in list) -->
                <input type="hidden" name="to_user_id" :value="selectedReceivers.length > 0 ? selectedReceivers[0].id : ''" required>
                
                <!-- Dropdown Results -->
                <div x-show="showDropdown && filteredUsers.length > 0" 
                     @click.stop
                     x-transition
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <div @click.stop="addReceiver(user)" 
                             class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm">
                            <div class="font-medium text-gray-900 dark:text-white" x-text="user.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="user.type + (user.department ? ' • ' + user.department : '')"></div>
                        </div>
                    </template>
                </div>
                <div x-show="showDropdown && search && filteredUsers.length === 0" 
                     @click.stop
                     class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                    No users found
                </div>
                
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <x-icon name="information-circle" class="w-3.5 h-3.5 inline mr-1" />
                    Add multiple recipients. The first person will be the primary receiver.
                </p>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Forward Purpose
                </label>
                <select name="intent" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    <option value="route" selected>Route for processing</option>
                    <option value="approval">Request approval</option>
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Select "Request approval" when sending the document to a dean, registrar, or administrator for sign-off.
                </p>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Instructions
                </label>
                <textarea name="instructions" rows="3" 
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                          placeholder="Enter forwarding instructions"></textarea>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Remarks
                </label>
                <textarea name="remarks" rows="2" 
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                          placeholder="Additional remarks"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeForwardModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" class="btn-primary text-sm">
                    Forward
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Approve Document Modal -->
<div id="approveModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeApproveModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Approve Document</h3>
            <button type="button" onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="approveForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Optional remarks"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeApproveModal()" class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-success text-sm">Approve</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Document Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeRejectModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Reject Document</h3>
            <button type="button" onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="rejectForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Reason <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Provide reason for rejection"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeRejectModal()" class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-danger text-sm">Reject</button>
            </div>
        </form>
    </div>
</div>

<!-- Return Document Modal -->
<div id="returnModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeReturnModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Return Document</h3>
            <button type="button" onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="returnForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <input type="hidden" id="return_tracking_id" name="tracking_id" value="">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Remarks <span class="text-red-500">*</span></label>
                <textarea name="remarks" rows="3" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Provide reason/remarks for return"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeReturnModal()" class="btn-secondary text-sm">Cancel</button>
                <button type="submit" class="btn-secondary text-sm">Return</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Status Modal (Admin Only) -->
<div id="changeStatusModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeChangeStatusModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Change Document Status</h3>
            <button type="button" onclick="closeChangeStatusModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="changeStatusForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                <div class="flex items-start">
                    <x-icon name="exclamation-triangle" class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2 flex-shrink-0" />
                    <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-300">
                        <strong>Admin Override:</strong> Manually changing status bypasses normal workflow. Use with caution.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Current Status
                </label>
                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-900 dark:text-white capitalize">
                    {{ str_replace('_', ' ', $document->status) }}
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    New Status <span class="text-red-500">*</span>
                </label>
                <select name="status" required class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="">Select new status...</option>
                    <option value="draft">Draft</option>
                    <option value="routing">Routing</option>
                    <option value="received">Received</option>
                    <option value="in_review">In Review</option>
                    <option value="for_approval">For Approval</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                    <option value="returned">Returned</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Reason for Status Change <span class="text-red-500">*</span>
                </label>
                <textarea name="reason" rows="3" required 
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                          placeholder="Explain why you are manually changing the status..."></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    This will be logged in the audit trail.
                </p>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeChangeStatusModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm font-medium">
                    <x-icon name="check" class="w-4 h-4 mr-2" />
                    Change Status
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Document Modal -->
<div id="completeModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeCompleteModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl z-10">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Mark Document as Completed</h3>
            <button type="button" onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="completeForm" class="px-4 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6">
            @csrf
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to mark this document as completed?
            </p>
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Final Remarks
                </label>
                <textarea name="remarks" rows="3" 
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                          placeholder="Enter final remarks (optional)"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeCompleteModal()" class="btn-secondary text-sm">
                    Cancel
                </button>
                <button type="submit" class="btn-success text-sm">
                    Complete Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
window.openForwardModal = function() {
    document.getElementById('forwardModal').classList.remove('hidden');
    document.getElementById('forwardModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

window.closeForwardModal = function() {
    document.getElementById('forwardModal').classList.add('hidden');
    document.getElementById('forwardModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('forwardForm').reset();
}

window.openCompleteModal = function() {
    document.getElementById('completeModal').classList.remove('hidden');
    document.getElementById('completeModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

window.closeCompleteModal = function() {
    document.getElementById('completeModal').classList.add('hidden');
    document.getElementById('completeModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('completeForm').reset();
}

// Open/Close Approve Modal
window.openApproveModal = function() {
    document.getElementById('approveModal').classList.remove('hidden');
    document.getElementById('approveModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
window.closeApproveModal = function() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('approveForm').reset();
}

// Open/Close Reject Modal
window.openRejectModal = function() {
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
window.closeRejectModal = function() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('rejectForm').reset();
}

// Open/Close Return Modal
window.openReturnModal = function(trackingId) {
    document.getElementById('return_tracking_id').value = trackingId;
    document.getElementById('returnModal').classList.remove('hidden');
    document.getElementById('returnModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
window.closeReturnModal = function() {
    document.getElementById('returnModal').classList.add('hidden');
    document.getElementById('returnModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('returnForm').reset();
}

// Open/Close Change Status Modal (Admin)
window.openChangeStatusModal = function() {
    document.getElementById('changeStatusModal').classList.remove('hidden');
    document.getElementById('changeStatusModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}
window.closeChangeStatusModal = function() {
    document.getElementById('changeStatusModal').classList.add('hidden');
    document.getElementById('changeStatusModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('changeStatusForm').reset();
}

// Handle forward form submission
document.getElementById('forwardForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route('documents.forward', $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeForwardModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document forwarded successfully');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            // Redirect to inbox
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            alert(result.message || 'Failed to forward document');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
});

// Acknowledge (Receive) tracking
window.acknowledgeTracking = async function(trackingId) {
    try {
        const response = await fetch(`/tracking/${trackingId}/receive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (result.success) {
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document received successfully');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            alert(result.message || 'Failed to acknowledge document');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
}

// Approve handler
document.getElementById('approveForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    try {
        const response = await fetch('{{ route('documents.approve', $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeApproveModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document approved successfully');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            alert(result.message || 'Failed to approve document');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
});

// Reject handler
document.getElementById('rejectForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    try {
        const response = await fetch('{{ route('documents.reject', $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeRejectModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document rejected');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            alert(result.message || 'Failed to reject document');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
});

// Return handler
document.getElementById('returnForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = new FormData(this);
    const trackingId = form.get('tracking_id');
    const data = { remarks: form.get('remarks') };
    try {
        const response = await fetch(`/tracking/${trackingId}/return`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            closeReturnModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document returned successfully');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            alert(result.message || 'Failed to return document');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
});

// Handle complete form submission
document.getElementById('completeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('{{ route('documents.complete', $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        // Check if response is JSON before parsing
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            alert('An error occurred. Please check the console for details.');
            return;
        }
        
        const result = await response.json();
        
        if (result.success) {
            closeCompleteModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document marked as completed!');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
            setTimeout(() => { window.location.href = '/inbox'; }, 300);
        } else {
            // Show error message from response
            const errorMsg = result.error || result.message || 'Error completing document. Please try again.';
            alert(errorMsg);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
});

// Change Status handler (Admin)
document.getElementById('changeStatusForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this).entries());
    
    try {
        const response = await fetch('{{ route("documents.change-status", $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeChangeStatusModal();
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document status changed successfully');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
        } else {
            alert(result.error || result.message || 'Failed to change status');
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred. Please try again.');
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeForwardModal();
        closeCompleteModal();
        closeApproveModal();
        closeRejectModal();
        closeReturnModal();
        closeChangeStatusModal();
    }
});

// Archive document function
window.archiveDocument = async function(documentId) {
    showDeleteModal(
        'Archive Document',
        'Are you sure you want to archive this document? Archived documents can only be viewed, not modified.',
        async () => {
            await performArchiveDocument(documentId);
        }
    );
}

window.performArchiveDocument = async function(documentId) {
    
    try {
        const response = await fetch(`/documents/${documentId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', result.message || 'Document archived successfully!');
            }
            // Update document status dynamically
            updateDocumentStatus(result.document);
        } else {
            alert(result.message || 'Error archiving document. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Add Attachment Modal Functions
window.openAddAttachmentModal = function() {
    document.getElementById('addAttachmentModal').classList.remove('hidden');
    document.getElementById('addAttachmentModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

window.closeAddAttachmentModal = function() {
    document.getElementById('addAttachmentModal').classList.add('hidden');
    document.getElementById('addAttachmentModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
    document.getElementById('addAttachmentForm').reset();
    
    // Clear file upload state
    const fileUploadContainer = document.querySelector('#addAttachmentModal .file-upload-container');
    if (fileUploadContainer) {
        const fileList = fileUploadContainer.querySelector('.file-list');
        if (fileList) {
            fileList.innerHTML = '';
        }
        // Clear file upload state for this modal
        if (window.fileUploadState) {
            const fileItems = fileList.querySelectorAll('.file-item');
            fileItems.forEach(item => {
                const fileId = item.dataset.fileId;
                window.fileUploadState.delete(fileId);
            });
        }
    }
}

// Upload attachments from inline component
window.uploadAttachments = async function() {
    // Find the file upload container in the attachments section (not in modal)
    const inlineUploadSection = document.getElementById('inline-attachment-upload');
    const fileUploadContainer = inlineUploadSection ? inlineUploadSection.querySelector('.file-upload-container') : null;
    if (!fileUploadContainer) {
        alert('File upload component not found.');
        return;
    }
    
    const fileList = fileUploadContainer.querySelector('.file-list');
    const fileItems = fileList.querySelectorAll('.file-item');
    
    // Check if at least one file is uploaded
    let hasCompletedFile = false;
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            hasCompletedFile = true;
        }
    });
    
    if (!hasCompletedFile) {
        alert('Please upload at least one file.');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    
    // Add files to FormData
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            formData.append('files[]', fileData.file);
        }
    });
    
    const uploadBtn = document.querySelector('button[onclick="uploadAttachments()"]');
    const originalText = uploadBtn ? uploadBtn.textContent : 'Upload Files';
    
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';
    }
    
    try {
        const response = await fetch('{{ route("documents.attachments.store", $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear file upload state
            fileItems.forEach(item => {
                const fileId = item.dataset.fileId;
                window.fileUploadState && window.fileUploadState.delete(fileId);
            });
            fileList.innerHTML = '';
            
            // Refresh attachments list dynamically
            await refreshAttachmentsList();
        } else {
            alert(result.message || 'Failed to upload attachments');
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while uploading attachments');
        if (uploadBtn) {
            uploadBtn.disabled = false;
            uploadBtn.textContent = originalText;
        }
    }
}

// Upload attachments from modal
document.getElementById('addAttachmentForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get files from file upload component
    const fileUploadContainer = this.querySelector('.file-upload-container');
    if (!fileUploadContainer) {
        alert('File upload component not found.');
        return;
    }
    
    const fileList = fileUploadContainer.querySelector('.file-list');
    const fileItems = fileList.querySelectorAll('.file-item');
    
    // Check if at least one file is uploaded
    let hasCompletedFile = false;
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            hasCompletedFile = true;
        }
    });
    
    if (!hasCompletedFile) {
        alert('Please upload at least one file.');
        return;
    }
    
    const formData = new FormData(this);
    
    // Add files to FormData
    fileItems.forEach(item => {
        const fileId = item.dataset.fileId;
        const fileData = window.fileUploadState && window.fileUploadState.get(fileId);
        if (fileData && fileData.status === 'completed') {
            formData.append('files[]', fileData.file);
        }
    });
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : 'Upload';
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
    }
    
    try {
        const response = await fetch('{{ route("documents.attachments.store", $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeAddAttachmentModal();
            // Refresh attachments list dynamically
            await refreshAttachmentsList();
        } else {
            alert(result.message || 'Failed to upload attachments');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while uploading attachments');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }
});

// Delete Document
window.deleteDocument = async function(id) {
    window.showDeleteModal(
        'Delete Document',
        'Are you sure you want to delete this document? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`/documents/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', data.message || 'Document deleted successfully!');
                    } else {
                        alert(data.message || 'Document deleted successfully!');
                    }
                    // Redirect to documents list after a short delay
                    setTimeout(() => {
                        @php
                            $documentsHomeRoute = auth()->user()->isStudent()
                                ? route('documents.my-documents')
                                : route('documents.inbox');
                        @endphp
                        window.location.href = '{{ $documentsHomeRoute }}';
                    }, 1500);
                } else {
                    alert(data.message || 'Error deleting document. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    );
}

// Delete attachment
window.deleteAttachment = async function(attachmentId) {
    window.showDeleteModal(
        'Delete Attachment',
        'Are you sure you want to delete this attachment? This action cannot be undone.',
        async () => {
            try {
                const response = await fetch(`/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove the attachment from UI
                    const attachmentElement = document.querySelector(`[data-attachment-id="${attachmentId}"]`);
                    if (attachmentElement) {
                        attachmentElement.remove();
                    }
                    
                    // Show success message
                    if (typeof window.showSuccessModal === 'function') {
                        window.showSuccessModal('Success', result.message || 'Attachment deleted successfully!');
                    } else {
                        alert(result.message || 'Attachment deleted successfully!');
                    }
                } else {
                    alert(result.message || 'Failed to delete attachment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the attachment');
            }
        }
    );
}

// Comment Form Submission
document.getElementById('comment-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        comment: formData.get('comment'),
        is_internal: formData.get('is_internal') === '1'
    };
    
    try {
        const response = await fetch('{{ route("documents.comments.store", $document) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success && result.comment) {
            // Clear form
            document.getElementById('comment-text').value = '';
            const internalCheckbox = document.getElementById('is-internal');
            if (internalCheckbox) internalCheckbox.checked = false;
            
            // Add comment to UI immediately (don't wait for broadcast)
            // The broadcast will be received by other users, but we add it immediately for the poster
            window.addCommentToUI(result.comment);
        } else {
            alert(result.message || result.error || 'Failed to post comment');
        }
    } catch (error) {
        alert('An error occurred while posting the comment');
    }
});

// Delete Comment
window.deleteComment = async function(commentId) {
    showDeleteModal(
        'Delete Comment',
        'Are you sure you want to delete this comment? This action cannot be undone.',
        async () => {
            await performDeleteComment(commentId);
        }
    );
}

window.performDeleteComment = async function(commentId) {
    
    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove comment from UI
            const commentElement = document.getElementById(`comment-${commentId}`);
            if (commentElement) {
                commentElement.remove();
            }
            
            // Show success message
            if (typeof window.showSuccessModal === 'function') {
                window.showSuccessModal('Success', 'Comment deleted successfully!');
            }
            
            // Refresh comments list dynamically
            await refreshCommentsList();
        } else {
            alert(result.message || result.error || 'Failed to delete comment');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the comment');
    }
}

// Real-time WebSocket listeners for comments
@if(config('broadcasting.default') !== 'null')
// Wait for Echo to be initialized before subscribing
function initializeCommentBroadcasting() {
    if (typeof window.Echo !== 'undefined' && !window.Echo._isDummy) {
        try {
            const channelName = 'document.{{ $document->id }}';
        
        // Listen for new comments on private channel
        const channel = window.Echo.private(channelName);
        
        channel.listen('.comment.created', (e) => {
            // Check if comment already exists (prevent duplicates)
            if (document.getElementById(`comment-${e.comment.id}`)) {
                return;
            }
            
            // Check if user can see internal comments
            const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
            if (e.comment.is_internal && !isAdmin) {
                return;
            }
            
            // Add comment to list
            window.addCommentToUI(e.comment);
        })
        .listen('.comment.deleted', (e) => {
            // Remove comment from UI
            const commentElement = document.getElementById(`comment-${e.comment_id}`);
            if (commentElement) {
                commentElement.style.transition = 'opacity 0.3s';
                commentElement.style.opacity = '0';
                setTimeout(() => {
                    commentElement.remove();
                    
                    // Show "no comments" message if list is empty
                    const commentsList = document.getElementById('comments-list');
                    if (commentsList && commentsList.children.length === 0) {
                        commentsList.innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">No comments yet.</div>';
                    }
                }, 300);
            }
        })
        .listen('.document.forwarded', (e) => {
            window.updateDocumentStatus(e.document);
            if (typeof window.refreshAttachmentsList === 'function') {
                window.refreshAttachmentsList();
            }
        })
        .listen('.document.received', (e) => {
            window.updateDocumentStatus(e.document);
        })
        .listen('.document.approved', (e) => {
            window.updateDocumentStatus(e);
        })
        .listen('.document.rejected', (e) => {
            window.updateDocumentStatus(e);
        })
        .listen('.document.returned', (e) => {
            window.updateDocumentStatus(e);
        })
        .listen('.document.completed', (e) => {
            window.updateDocumentStatus(e);
        })
        .listen('.document.updated', (e) => {
            window.updateDocumentStatus(e);
        })
        .listen('.attachment.uploaded', (e) => {
            if (typeof window.refreshAttachmentsList === 'function') {
                window.refreshAttachmentsList();
            }
        })
        .listen('.attachment.deleted', (e) => {
            const attachmentElement = document.querySelector(`[data-attachment-id="${e.attachment_id}"]`);
            if (attachmentElement) {
                attachmentElement.remove();
            }
        })
        .listen('.signature.created', (e) => {
            // Dispatch Alpine event to signature component
            window.dispatchEvent(new CustomEvent('signature-created', { detail: e }));
        })
        .listen('.signature.verified', (e) => {
            // Dispatch Alpine event to signature component
            window.dispatchEvent(new CustomEvent('signature-verified', { detail: e }));
        })
        .listen('.signature.deleted', (e) => {
            // Dispatch Alpine event to signature component
            window.dispatchEvent(new CustomEvent('signature-deleted', { detail: e }));
        });
        } catch (error) {
            // Silent fail
        }
    }
}

// Try to initialize immediately if Echo is already loaded
if (typeof window.Echo !== 'undefined' && !window.Echo._isDummy) {
    initializeCommentBroadcasting();
} else {
    // Wait for Echo to be initialized
    const checkEcho = setInterval(() => {
        if (typeof window.Echo !== 'undefined' && !window.Echo._isDummy) {
            clearInterval(checkEcho);
            initializeCommentBroadcasting();
        }
    }, 100);
    
    // Timeout after 5 seconds
    setTimeout(() => {
        clearInterval(checkEcho);
        if (typeof window.Echo === 'undefined' || window.Echo._isDummy) {
            console.error('❌ Echo failed to initialize after 5 seconds');
        }
    }, 5000);
}
@else
console.warn('⚠️ Broadcasting is disabled (config: null)');
@endif

// Function to add comment to UI
window.addCommentToUI = function(comment) {
    const commentsList = document.getElementById('comments-list');
    if (!commentsList) return;
    
    // Remove "no comments" message if exists
    const noComments = commentsList.querySelector('.text-center.py-8');
    if (noComments) {
        noComments.remove();
    }
    
    // Get first letter for avatar
    const firstLetter = comment.user.first_name.charAt(0).toUpperCase();
    
    // Color mapping (same as sidebar)
    const colors = {
        'A': 'bg-red-500 dark:bg-red-600',
        'B': 'bg-orange-500 dark:bg-orange-600',
        'C': 'bg-amber-500 dark:bg-amber-600',
        'D': 'bg-yellow-500 dark:bg-yellow-600',
        'E': 'bg-lime-500 dark:bg-lime-600',
        'F': 'bg-green-500 dark:bg-green-600',
        'G': 'bg-emerald-500 dark:bg-emerald-600',
        'H': 'bg-teal-500 dark:bg-teal-600',
        'I': 'bg-cyan-500 dark:bg-cyan-600',
        'J': 'bg-sky-500 dark:bg-sky-600',
        'K': 'bg-blue-500 dark:bg-blue-600',
        'L': 'bg-indigo-500 dark:bg-indigo-600',
        'M': 'bg-violet-500 dark:bg-violet-600',
        'N': 'bg-purple-500 dark:bg-purple-600',
        'O': 'bg-fuchsia-500 dark:bg-fuchsia-600',
        'P': 'bg-pink-500 dark:bg-pink-600',
        'Q': 'bg-rose-500 dark:bg-rose-600',
        'R': 'bg-red-600 dark:bg-red-700',
        'S': 'bg-orange-600 dark:bg-orange-700',
        'T': 'bg-green-600 dark:bg-green-700',
        'U': 'bg-teal-600 dark:bg-teal-700',
        'V': 'bg-blue-600 dark:bg-blue-700',
        'W': 'bg-indigo-600 dark:bg-indigo-700',
        'X': 'bg-purple-600 dark:bg-purple-700',
        'Y': 'bg-pink-600 dark:bg-pink-700',
        'Z': 'bg-rose-600 dark:bg-rose-700'
    };
    const avatarColor = colors[firstLetter] || 'bg-gray-500 dark:bg-gray-600';
    
    // Create avatar HTML
    let avatarHtml = '';
    if (comment.user.avatar_url) {
        avatarHtml = `<img src="${comment.user.avatar_url}" alt="${comment.user.full_name}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">`;
    } else {
        avatarHtml = `<div class="w-8 h-8 sm:w-10 sm:h-10 ${avatarColor} rounded-full flex items-center justify-center">
            <span class="text-white font-semibold text-sm">${firstLetter}</span>
        </div>`;
    }
    
    // Calculate time ago
    const timeAgo = window.getTimeAgo(new Date(comment.created_at));
    
    // Create internal badge if needed
    const internalBadge = comment.is_internal 
        ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">Internal</span>'
        : '';
    
    // Create delete button if user owns the comment or is admin
    const canDelete = comment.user_id === {{ auth()->id() }} || {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    const deleteButton = canDelete 
        ? `<button onclick="deleteComment(${comment.id})" class="flex-shrink-0 p-2 min-w-[36px] min-h-[36px] text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Delete comment">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>`
        : '';
    
    // Create comment HTML
    const commentHtml = `
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0 animate-fade-in" id="comment-${comment.id}">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    ${avatarHtml}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">${comment.user.full_name}</p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">${timeAgo}</span>
                        ${internalBadge}
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 break-words whitespace-pre-wrap">${comment.comment}</p>
                </div>
                ${deleteButton}
            </div>
        </div>
    `;
    
    // Insert at the top of comments list
    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
}

// Helper function to calculate time ago
window.getTimeAgo = function(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    if (seconds < 60) return 'just now';
    
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
    
    const weeks = Math.floor(days / 7);
    if (weeks < 4) return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
    
    const months = Math.floor(days / 30);
    if (months < 12) return `${months} month${months > 1 ? 's' : ''} ago`;
    
    const years = Math.floor(days / 365);
    return `${years} year${years > 1 ? 's' : ''} ago`;
}
</script>

<!-- Add Attachment Modal -->
<div id="addAttachmentModal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeAddAttachmentModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Add Attachments</h3>
            <button type="button" onclick="closeAddAttachmentModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Form -->
        <form id="addAttachmentForm" enctype="multipart/form-data" class="px-4 sm:px-6 py-4 sm:py-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Attach Files <span class="text-red-500">*</span>
                </label>
                <x-file-upload 
                    name="files[]" 
                    :multiple="true" 
                    :maxSize="20" 
                    acceptedTypes=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="closeAddAttachmentModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Notification & Confirmation Modals -->
<x-notification-modal />

<script>
// Handle notification redirects with modal opening
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check if we should open a specific modal based on URL parameters
    if (urlParams.has('open_modal')) {
        const modalType = urlParams.get('open_modal');
        
        setTimeout(() => {
            // Open the appropriate modal based on type
            switch(modalType) {
                case 'versions':
                    // Scroll to versions section
                    document.querySelector('[x-data*="documentVersions"]')?.scrollIntoView({ behavior: 'smooth' });
                    break;
                case 'signatures':
                    // Scroll to signatures section
                    document.querySelector('[x-data*="documentSignatures"]')?.scrollIntoView({ behavior: 'smooth' });
                    break;
                case 'tags':
                    // Scroll to tags section
                    document.querySelector('[x-data*="documentTags"]')?.scrollIntoView({ behavior: 'smooth' });
                    break;
            }
            
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }

    // Sidebar Toggle Functionality
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('hidden');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
            }
            sidebarOverlay.classList.add('hidden');
        });
    }

    if (sidebar) {
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.add('hidden');
                    }
                }
            });
        });
    }

    // Helper function to update document status dynamically
    window.updateDocumentStatus = function(docData) {
        if (!docData) return;
        
        // Update status badge
        const statusBadge = document.querySelector('[data-status-badge]');
        if (statusBadge && docData.status) {
            statusBadge.textContent = docData.status.replace('_', ' ').toUpperCase();
            statusBadge.className = getStatusBadgeClass(docData.status);
        }
        
        // Update current holder if exists
        const holderElement = document.querySelector('[data-current-holder]');
        if (holderElement && docData.current_holder) {
            holderElement.textContent = docData.current_holder.full_name || docData.current_holder.name;
        }
        
        // Update status in the page
        if (docData.status) {
            const statusElements = document.querySelectorAll('[data-document-status]');
            statusElements.forEach(el => {
                el.textContent = docData.status.replace('_', ' ').toUpperCase();
                el.className = getStatusBadgeClass(docData.status);
            });
        }
        
        // Dispatch event for other components
        window.dispatchAlpineEvent('document-updated', { document: docData });
    }
    
    // Get status badge class
    window.getStatusBadgeClass = function(status) {
        const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
        const statusClasses = {
            'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'routing': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'received': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'in_review': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'for_approval': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'completed': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300',
            'archived': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
        return `${baseClasses} ${statusClasses[status] || statusClasses['draft']}`;
    }
    
    // Refresh attachments list (make it globally accessible)
    window.refreshAttachmentsList = async function() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'Accept': 'text/html' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newAttachments = doc.querySelector('[data-attachments-list]');
            const currentAttachments = document.querySelector('[data-attachments-list]');
            
            if (newAttachments && currentAttachments) {
                currentAttachments.innerHTML = newAttachments.innerHTML;
            }
        } catch (error) {
            console.error('Error refreshing attachments:', error);
        }
    };
    
    // Refresh comments list
    window.refreshCommentsList = async function() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'Accept': 'text/html' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newComments = doc.querySelector('#comments-list');
            const currentComments = document.getElementById('comments-list');
            
            if (newComments && currentComments) {
                currentComments.innerHTML = newComments.innerHTML;
            }
        } catch (error) {
            console.error('Error refreshing comments:', error);
        }
    };
});
</script>

@endsection
