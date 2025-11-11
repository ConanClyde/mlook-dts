<div>
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Security Settings</h2>
    
    @php
        $user = auth()->user();
        $recoveryCodes = $user->two_factor_enabled 
            ? (new \App\Services\TwoFactorService())->getRecoveryCodes($user) 
            : [];
    @endphp

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- 2FA Status Card -->
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-2.5 sm:p-3 rounded-full {{ $user->two_factor_enabled ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 {{ $user->two_factor_enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        Two-Factor Authentication
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        Status: 
                        <span class="font-medium {{ $user->two_factor_enabled ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                            {{ $user->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </p>
                    @if($user->two_factor_confirmed_at)
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            Enabled on {{ $user->two_factor_confirmed_at->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            </div>

            @if($user->two_factor_enabled)
                <button onclick="openDisable2FAModal()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                    Disable 2FA
                </button>
            @else
                <button onclick="openEnable2FAModal()"
                    class="bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                    Enable 2FA
                </button>
            @endif
        </div>
    </div>


    <!-- Info Box -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">About Two-Factor Authentication:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Adds an extra layer of security to your account</li>
                    <li>Requires a 6-digit code from your authenticator app when logging in</li>
                    <li>Compatible with Google Authenticator, Authy, and other TOTP apps</li>
                    <li>Recovery codes provided in case you lose access to your device</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recovery Codes (if 2FA enabled) -->
    @if($user->two_factor_enabled)
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recovery Codes</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Store these recovery codes in a secure location. They can be used to access your account if you lose your 2FA device.
            </p>

            @if(count($recoveryCodes) > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($recoveryCodes as $code)
                            <code class="text-sm font-mono text-gray-900 dark:text-white">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>

                <button onclick="openRegenerate2FAModal()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                    Regenerate Recovery Codes
                </button>
            @else
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        All recovery codes have been used. Please regenerate new codes.
                    </p>
                </div>
                <button onclick="openRegenerate2FAModal()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 min-h-[44px] rounded-lg transition-colors text-sm font-medium w-full sm:w-auto">
                    Regenerate Recovery Codes
                </button>
            @endif
        </div>
    @endif
</div>

<!-- Disable 2FA Modal -->
<div id="disable-2fa-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDisable2FAModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl flex-shrink-0">
            <h3 class="text-lg sm:text-xl font-semibold text-red-600 dark:text-red-400">Disable Two-Factor Authentication</h3>
            <button type="button" onclick="closeDisable2FAModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <form onsubmit="disable2FAWithPassword(event)" class="flex flex-col flex-1 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    Enter your password to confirm disabling 2FA.
                </p>
                <div>
                    <label for="disable-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="disable-password" required
                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white">
                        <button type="button"
                                onclick="togglePasswordVisibility('disable-password', 'disable_password_eye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="disable_password_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div id="disable-password-error" class="text-sm text-red-600 dark:text-red-400 mt-1 hidden"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button type="button" onclick="closeDisable2FAModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Disable 2FA
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Regenerate Codes Modal -->
<div id="regenerate-codes-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeRegenerate2FAModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-md max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl flex-shrink-0">
            <h3 class="text-lg sm:text-xl font-semibold text-yellow-600 dark:text-yellow-400">Regenerate Recovery Codes</h3>
            <button type="button" onclick="closeRegenerate2FAModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <form onsubmit="regenerate2FAWithPassword(event)" class="flex flex-col flex-1 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    This will invalidate your old recovery codes. Enter your password to confirm.
                </p>
                <div>
                    <label for="regen-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="regen-password" required
                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-yellow-500 dark:bg-gray-700 dark:text-white">
                        <button type="button"
                                onclick="togglePasswordVisibility('regen-password', 'regen_password_eye')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg id="regen_password_eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div id="regen-password-error" class="text-sm text-red-600 dark:text-red-400 mt-1 hidden"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button type="button" onclick="closeRegenerate2FAModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                    Regenerate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Enable 2FA Modal -->
<div id="enable-2fa-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeEnable2FAModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 flex items-center justify-between rounded-t-lg sm:rounded-t-xl flex-shrink-0">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Enable Two-Factor Authentication</h3>
            <button type="button" onclick="closeEnable2FAModal()" class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 min-w-[36px] min-h-[36px] flex items-center justify-center">
                <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
        </div>

        <!-- Body -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex gap-3">
                        <x-icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p class="font-semibold mb-2">Before you continue:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Make sure you have an authenticator app installed</li>
                                <li>You'll need to scan a QR code or enter a secret key</li>
                                <li>Save your recovery codes in a secure location</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button type="button" onclick="closeEnable2FAModal()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="enable2FAWithAjax()" class="flex-1 px-4 py-2.5 min-h-[44px] text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Continue to Setup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Setup Modal Container (dynamically filled) -->
<div id="setup-2fa-modal-container"></div>

<!-- 2FA Setup Modal (shown after enabling via page load) -->
@if(session('2fa_setup') && !$user->two_factor_enabled)
    @php
        $setupData = session('2fa_setup');
    @endphp
    <div id="setup-2fa-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeSetup2FAModal()">
        <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-t-lg sm:rounded-t-xl flex-shrink-0 flex items-center justify-between">
                <div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Complete 2FA Setup</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Follow these steps to secure your account</p>
                </div>
                <form action="{{ route('two-factor.cancel') }}" method="POST" class="inline" id="cancel-2fa-form">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <x-icon name="x-mark" class="w-5 h-5 sm:w-6 sm:h-6" />
                    </button>
                </form>
            </div>
            
            <div class="px-4 sm:px-6 py-4 sm:py-6 space-y-6 overflow-y-auto flex-1">
                <!-- Step 1: QR Code -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-semibold">1</div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Scan QR Code</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Open your authenticator app and scan this QR code:
                    </p>
                    <div class="flex justify-center bg-white p-6 rounded-lg">
                        {!! $setupData['qr_code'] !!}
                    </div>
                </div>

                <!-- Step 2: Manual Entry -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-semibold">2</div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Or Enter Manually</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Can't scan? Enter this code in your authenticator app:
                    </p>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <code class="text-base font-mono text-gray-900 dark:text-white break-all">{{ $setupData['secret'] }}</code>
                    </div>
                </div>

                <!-- Step 3: Verify -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-semibold">3</div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Verify Code</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Enter the 6-digit code from your authenticator app:
                    </p>
                    <form action="{{ route('two-factor.confirm') }}" method="POST">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" required
                                class="flex-1 px-4 py-3 text-center text-xl sm:text-2xl font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white min-h-[44px]"
                                placeholder="000000" autofocus>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 sm:px-8 py-3 min-h-[44px] rounded-lg transition-colors font-medium whitespace-nowrap">
                                Verify & Enable
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 4: Recovery Codes -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-yellow-600 text-white text-sm font-semibold">4</div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Save Recovery Codes</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <strong class="text-yellow-800 dark:text-yellow-200">Important:</strong> Save these codes in a secure location. You can use them to access your account if you lose your device.
                    </p>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 mb-4">
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($setupData['recovery_codes'] as $code)
                                <code class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="printRecoveryCodes()" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Recovery Codes
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0 bg-white dark:bg-gray-800 rounded-b-lg sm:rounded-b-xl">
                <form action="{{ route('two-factor.cancel') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 min-h-[44px] text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel Setup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function printRecoveryCodes() {
        window.print();
    }
    </script>
@endif

<script>
// Open/Close functions for 2FA modals
function openDisable2FAModal() {
    const modal = document.getElementById('disable-2fa-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDisable2FAModal() {
    const modal = document.getElementById('disable-2fa-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function openRegenerate2FAModal() {
    const modal = document.getElementById('regenerate-codes-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeRegenerate2FAModal() {
    const modal = document.getElementById('regenerate-codes-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function openEnable2FAModal() {
    const modal = document.getElementById('enable-2fa-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeEnable2FAModal() {
    const modal = document.getElementById('enable-2fa-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Disable 2FA with password validation
function disable2FAWithPassword(event) {
    event.preventDefault();
    
    const password = document.getElementById('disable-password').value;
    const errorDiv = document.getElementById('disable-password-error');
    
    errorDiv.classList.add('hidden');
    
    fetch('{{ route('two-factor.disable') }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Invalid password.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.dispatchAlpineEvent('2fa-disabled', data);
            // Soft update UI: close modal and update status text without reload
            closeDisable2FAModal();
            const statusSpan = document.querySelector('span.font-medium');
            if (statusSpan) {
                statusSpan.textContent = 'Disabled';
                statusSpan.classList.remove('text-green-600','dark:text-green-400');
                statusSpan.classList.add('text-gray-600','dark:text-gray-400');
            }
        }
    })
    .catch(error => {
        errorDiv.textContent = error.message || 'Invalid password.';
        errorDiv.classList.remove('hidden');
        document.getElementById('disable-password').value = '';
        document.getElementById('disable-password').focus();
    });
}

// Regenerate recovery codes with password validation
function regenerate2FAWithPassword(event) {
    event.preventDefault();
    
    const password = document.getElementById('regen-password').value;
    const errorDiv = document.getElementById('regen-password-error');
    
    errorDiv.classList.add('hidden');
    
    fetch('{{ route('two-factor.recovery-codes.regenerate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Invalid password.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Soft update: close modal and re-open recovery codes list
            closeRegenerate2FAModal();
            // Trigger a minimal refresh by fetching the page fragment (optional)
            // For now, show a simple alert and let user reopen page section
            alert('Recovery codes regenerated. Please re-open Security to view new codes.');
        }
    })
    .catch(error => {
        errorDiv.textContent = error.message || 'Invalid password.';
        errorDiv.classList.remove('hidden');
        document.getElementById('regen-password').value = '';
        document.getElementById('regen-password').focus();
    });
}

// Close modals on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDisable2FAModal();
        closeRegenerate2FAModal();
        closeEnable2FAModal();
        closeSetup2FAModal();
    }
});

// Enable 2FA with AJAX (no page reload)
function enable2FAWithAjax() {
    closeEnable2FAModal();
    
    fetch('{{ route('two-factor.enable') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSetup2FAModal(data.secret, data.qr_code, data.recovery_codes);
        } else {
            alert(data.message || 'Failed to enable 2FA');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Close setup modal
function closeSetup2FAModal() {
    const container = document.getElementById('setup-2fa-modal-container');
    if (container) {
        container.innerHTML = '';
    }
    const modal = document.getElementById('setup-2fa-modal');
    if (modal) {
        modal.remove();
    }
    document.body.style.overflow = 'auto';
}

// Global variables for 2FA setup
let setup2FAData = {
    secret: '',
    qrCode: '',
    recoveryCodes: []
};

// Show Step 1: QR Code modal
function showSetup2FAModal(secret, qrCode, recoveryCodes) {
    setup2FAData = { secret, qrCode, recoveryCodes };
    showQRCodeModal();
}

// Step 1: QR Code Modal
function showQRCodeModal() {
    const container = document.getElementById('setup-2fa-modal-container');
    container.innerHTML = `
        <div id="setup-2fa-modal-dynamic" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeSetup2FAModal()">
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-t-lg sm:rounded-t-xl flex-shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Complete 2FA Setup</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Follow these steps to secure your account</p>
                    </div>
                    <button type="button" onclick="closeSetup2FAModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                    <div class="text-center mb-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Step 1 of 3</span>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scan QR Code</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Open your authenticator app (Google Authenticator, Authy, etc.) and scan this QR code:</p>
                    <div class="flex justify-center bg-white dark:bg-gray-900 p-6 rounded-lg mb-6">
                        ${setup2FAData.qrCode}
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Can't scan? Enter this code manually:</p>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                            <code class="text-sm font-mono text-gray-900 dark:text-white break-all">${setup2FAData.secret}</code>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button type="button" onclick="closeSetup2FAModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="button" onclick="showVerifyCodeModal()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Next
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.style.overflow = 'hidden';
}

// Verify 2FA code with AJAX
function verify2FACode(event) {
    event.preventDefault();
    
    const code = document.getElementById('verify-code-input').value;
    const errorDiv = document.getElementById('verify-error');
    
    // Hide previous error
    errorDiv.classList.add('hidden');
    
    fetch('{{ route('two-factor.confirm') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code: code })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Invalid verification code. Please try again.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showRecoveryCodesModal();
        } else {
            errorDiv.textContent = data.message || 'Invalid verification code. Please try again.';
            errorDiv.classList.remove('hidden');
            document.getElementById('verify-code-input').value = '';
            document.getElementById('verify-code-input').focus();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = error.message || 'Invalid verification code. Please try again.';
        errorDiv.classList.remove('hidden');
        document.getElementById('verify-code-input').value = '';
        document.getElementById('verify-code-input').focus();
    });
}

// Step 2: Verify Code Modal
function showVerifyCodeModal() {
    const container = document.getElementById('setup-2fa-modal-container');
    container.innerHTML = `
        <div id="setup-2fa-modal-dynamic" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeSetup2FAModal()">
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-t-lg sm:rounded-t-xl flex-shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Complete 2FA Setup</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Verify your authenticator code</p>
                    </div>
                    <button type="button" onclick="closeSetup2FAModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                    <div class="text-center mb-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Step 2 of 3</span>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Verify Code</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Enter the 6-digit code from your authenticator app:</p>
                    <form onsubmit="verify2FACode(event)" id="verify-2fa-form">
                        <div class="flex justify-center mb-2">
                            <input type="text" id="verify-code-input" maxlength="6" pattern="[0-9]{6}" required
                                class="w-48 px-4 py-3 text-center text-xl font-mono border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                placeholder="000000" autofocus>
                        </div>
                        <div id="verify-error" class="text-center text-sm text-red-600 dark:text-red-400 mb-4 hidden"></div>
                        <div class="flex gap-3">
                            <button type="button" onclick="showQRCodeModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Back
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors">
                                Verify & Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
}

// Download recovery codes as TXT file
function downloadRecoveryCodes() {
    const codes = setup2FAData.recoveryCodes.join('\n');
    const content = `Two-Factor Authentication Recovery Codes\n` +
                   `Generated: ${new Date().toLocaleString()}\n` +
                   `\n` +
                   `IMPORTANT: Save these codes in a secure location.\n` +
                   `You can use them to access your account if you lose your authenticator device.\n` +
                   `Each code can only be used once.\n` +
                   `\n` +
                   `Recovery Codes:\n` +
                   `${codes}`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '2fa-recovery-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Step 3: Recovery Codes Modal
function showRecoveryCodesModal() {
    const container = document.getElementById('setup-2fa-modal-container');
    const recoveryCodesHtml = setup2FAData.recoveryCodes.map(code => 
        `<code class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">${code}</code>`
    ).join('');
    
    container.innerHTML = `
        <div id="setup-2fa-modal-dynamic" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeSetup2FAModal()">
            <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl w-full max-w-lg max-h-[90vh] flex flex-col shadow-xl" onclick="event.stopPropagation()">
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-4 rounded-t-lg sm:rounded-t-xl flex-shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">Complete 2FA Setup</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Save your recovery codes</p>
                    </div>
                    <button type="button" onclick="closeSetup2FAModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-4 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1">
                    <div class="text-center mb-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Step 3 of 3</span>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Save Recovery Codes</h4>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>Important:</strong> Save these codes in a secure location. You can use them to access your account if you lose your authenticator device.
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 mb-4">
                        <div class="grid grid-cols-2 gap-3">${recoveryCodesHtml}</div>
                    </div>
                    <button onclick="downloadRecoveryCodes()" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download as TXT
                    </button>
                </div>

                <div class="flex gap-3 pt-4 px-4 sm:px-6 pb-4 sm:pb-6 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <button type="button" onclick="closeSetup2FAModal()" class="w-full px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        Done
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        // Eye-slash icon (solid)
        icon.innerHTML = '<path d="M3.53 2.47a.75.75 0 00-1.06 1.06l18 18a.75.75 0 101.06-1.06l-18-18zM22.676 12.553a11.249 11.249 0 01-2.631 4.31l-3.099-3.099a5.25 5.25 0 00-6.71-6.71L7.759 4.577a11.217 11.217 0 014.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113z" /><path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0115.75 12zM12.53 15.713l-4.243-4.244a3.75 3.75 0 004.243 4.243z" /><path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 00-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 016.75 12z" />';
    } else {
        input.type = 'password';
        // Eye icon (solid)
        icon.innerHTML = '<path d="M12 15a3 3 0 100-6 3 3 0 000 6z" /><path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 010-1.113zM17.25 12a5.25 5.25 0 11-10.5 0 5.25 5.25 0 0110.5 0z" clip-rule="evenodd" />';
    }
}
</script>
