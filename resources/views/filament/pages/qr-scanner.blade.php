<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Scanner Input Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <x-heroicon-o-qr-code class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Scan Subscription QR Code</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Paste the scanned QR code data below to process a subscription drink redemption.</p>
                </div>
            </div>

            <form wire:submit="processRedemption" class="space-y-4">
                <div>
                    <label for="qr-data" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        QR Code Data (JSON)
                    </label>
                    <textarea
                        id="qr-data"
                        wire:model="qrData"
                        rows="5"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                        placeholder='{"type":"subscription_redeem","user_id":"1","subscription_id":"1","user_subscription_id":"1","timestamp":"2026-07-12T10:00:00.000Z"}'
                    ></textarea>
                    @error('qrData')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-150 shadow-sm"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <x-heroicon-m-check-circle class="w-5 h-5" />
                        <span wire:loading.remove wire:target="processRedemption">Process Redemption</span>
                        <span wire:loading wire:target="processRedemption">Processing...</span>
                    </button>

                    @if($qrData)
                        <button
                            type="button"
                            wire:click="$set('qrData', '')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors duration-150"
                        >
                            <x-heroicon-m-x-mark class="w-4 h-4" />
                            Clear
                        </button>
                    @endif
                </div>
            </form>
        </div>

        {{-- Last Redemption Result --}}
        @if($lastResult)
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900/40 rounded-full">
                        <x-heroicon-o-check-badge class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Last Redemption Successful</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Order Number</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $lastResult['order_number'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Subscription</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $lastResult['subscription_name'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Drinks Remaining</p>
                        <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-1">{{ $lastResult['drinks_remaining'] }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Used</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $lastResult['drinks_used'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Instructions Card --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-6">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                <div>
                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">How it works</h4>
                    <ol class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-decimal list-inside">
                        <li>Customer shows their subscription QR code from the Lesh Kaffe app.</li>
                        <li>Scan the QR code using a barcode scanner or camera app.</li>
                        <li>Paste the decoded JSON data into the text field above.</li>
                        <li>Click "Process Redemption" to deduct one drink and create the order.</li>
                        <li>Confirm the success message and prepare the customer's drink!</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
