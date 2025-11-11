@props(['name' => 'files[]', 'multiple' => true, 'maxSize' => 20, 'acceptedTypes' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png', 'maxFiles' => null])

<div class="file-upload-container" data-name="{{ $name }}" data-multiple="{{ $multiple ? 'true' : 'false' }}" data-max-size="{{ $maxSize }}" data-accepted-types="{{ $acceptedTypes }}" data-max-files="{{ $maxFiles }}">
    <!-- Drag and Drop Zone -->
    <div class="file-drop-zone border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 sm:p-6 lg:p-8 text-center transition-colors hover:border-orange-500 dark:hover:border-orange-500 bg-gray-50 dark:bg-gray-700/50 cursor-pointer" 
         ondrop="handleFileDrop(event)" 
         ondragover="handleDragOver(event)" 
         ondragleave="handleDragLeave(event)"
         onclick="if (!window.isDragging && event.target.tagName !== 'BUTTON' && !event.target.closest('button')) { document.getElementById('file-input-{{ str_replace(['[', ']'], ['-', ''], $name) }}').click(); }">
        <input type="file" 
               name="{{ $name }}" 
               id="file-input-{{ str_replace(['[', ']'], ['-', ''], $name) }}" 
               class="hidden" 
               {{ $multiple ? 'multiple' : '' }} 
               accept="{{ $acceptedTypes }}"
               onchange="handleFileSelect(event)">
        
        <!-- Cloud Icon -->
        <div class="flex justify-center mb-3 sm:mb-4">
            <svg class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
        </div>
        
        <!-- Instructions -->
        <p class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 sm:mb-2">
            Choose a file or drag & drop it here
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 sm:mb-4 px-2">
            {{ str_replace(',', ', ', strtoupper(str_replace('.', '', $acceptedTypes))) }} formats, up to {{ $maxSize }} MB
        </p>
        
        <!-- Browse Button -->
        <button type="button" 
                onclick="event.stopPropagation(); document.getElementById('file-input-{{ str_replace(['[', ']'], ['-', ''], $name) }}').click()" 
                class="px-4 py-2 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            Browse File
        </button>
    </div>
    
    <!-- File List -->
    <div class="file-list mt-4 space-y-3"></div>
</div>

<script>
// File upload handlers
window.isDragging = false;

function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    window.isDragging = true;
    event.currentTarget.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    window.isDragging = false;
    event.currentTarget.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
}

function handleFileDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    window.isDragging = false;
    
    const dropZone = event.currentTarget;
    dropZone.classList.remove('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900/20');
    
    const container = dropZone.closest('.file-upload-container');
    const inputName = container.dataset.name;
    const inputId = 'file-input-' + inputName.replace(/[\[\]]/g, '-');
    const fileInput = document.getElementById(inputId);
    
    if (event.dataTransfer.files.length > 0) {
        fileInput.files = event.dataTransfer.files;
        displayFiles(fileInput);
    }
}

function handleFileSelect(event) {
    console.log('handleFileSelect called');
    console.log('Files selected:', event.target.files);
    console.log('Files count:', event.target.files.length);
    
    // Store file globally for Alpine.js components to access
    if (event.target.files && event.target.files[0]) {
        window.lastSelectedFile = event.target.files[0];
        console.log('✅ File stored globally:', window.lastSelectedFile.name);
    }
    
    displayFiles(event.target);
}

function displayFiles(input) {
    const container = input.closest('.file-upload-container');
    const fileList = container.querySelector('.file-list');
    const maxSize = parseInt(container.dataset.maxSize) * 1024 * 1024; // Convert MB to bytes
    
    fileList.innerHTML = '';
    
    Array.from(input.files).forEach((file, index) => {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
        const isTooBig = file.size > maxSize;
        
        const fileItem = document.createElement('div');
        fileItem.className = 'flex items-center justify-between p-2 sm:p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg gap-2';
        
        fileItem.innerHTML = `
            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded flex items-center justify-center ${isTooBig ? 'bg-red-100 dark:bg-red-900/30' : 'bg-orange-100 dark:bg-orange-900/30'}">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ${isTooBig ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">${file.name}</p>
                    <p class="text-xs ${isTooBig ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'}">
                        ${fileSize} MB ${isTooBig ? '(Too large!)' : ''}
                    </p>
                </div>
            </div>
            <button type="button" onclick="removeFile(this, ${index})" class="flex-shrink-0 p-2 min-w-[36px] min-h-[36px] text-gray-400 hover:text-red-600 dark:hover:text-red-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        fileList.appendChild(fileItem);
    });
}

function removeFile(button, index) {
    const container = button.closest('.file-upload-container');
    const inputName = container.dataset.name;
    const inputId = 'file-input-' + inputName.replace(/[\[\]]/g, '-');
    const fileInput = document.getElementById(inputId);
    
    const dt = new DataTransfer();
    Array.from(fileInput.files).forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    
    fileInput.files = dt.files;
    displayFiles(fileInput);
}
</script>

