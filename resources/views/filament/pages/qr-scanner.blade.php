<x-filament-panels::page>
    <div x-data="qrScannerApp()" x-init="init()" style="max-width: 900px; margin: 0 auto;">

        {{-- Scanner Card --}}
        <div style="background: white; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <div style="padding: 10px; background: #f0fdf4; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#16a34a" style="width: 28px; height: 28px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">Webcam QR Scanner</h2>
                    <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Point a QR code at your webcam to scan and process.</p>
                </div>
            </div>

            {{-- Camera View --}}
            <div style="position: relative; width: 100%; max-width: 500px; margin: 0 auto 20px auto;">
                <div id="qr-reader" style="width: 100%; border-radius: 12px; overflow: hidden; border: 2px solid #d1d5db;"></div>

                {{-- Scanner status overlay (when not scanning) --}}
                <div x-show="!scanning" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.6); border-radius: 12px; gap: 12px; min-height: 200px;">
                    {{-- Live camera button (only works on HTTPS/localhost) --}}
                    <button @click="startScanner()" style="padding: 14px 28px; background: #16a34a; color: white; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" /></svg>
                        Live Camera
                    </button>

                    <span style="color: #ccc; font-size: 12px;">— or —</span>

                    {{-- File/Image scan button (works on HTTP!) --}}
                    <label style="padding: 14px 28px; background: #4f46e5; color: white; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" /></svg>
                        Scan from Image / Take Photo
                        <input type="file" accept="image/*" capture="environment" @change="scanFromFile($event)" style="display: none;" />
                    </label>

                    <p style="color: #9ca3af; font-size: 11px; text-align: center; margin: 0; max-width: 280px;">
                        Live Camera requires HTTPS. Use "Scan from Image" on HTTP.
                    </p>
                </div>
            </div>

            {{-- Controls --}}
            <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 16px;">
                <button x-show="scanning" @click="stopScanner()" style="padding: 10px 20px; background: #ef4444; color: white; font-weight: 600; font-size: 13px; border: none; border-radius: 8px; cursor: pointer;">
                    Stop Camera
                </button>
                <label x-show="scanning" style="padding: 10px 20px; background: #4f46e5; color: white; font-weight: 600; font-size: 13px; border: none; border-radius: 8px; cursor: pointer;">
                    📷 Scan Image Instead
                    <input type="file" accept="image/*" capture="environment" @change="scanFromFile($event)" style="display: none;" />
                </label>
            </div>

            {{-- Manual Input Fallback --}}
            <details style="margin-top: 12px;">
                <summary style="font-size: 13px; color: #6b7280; cursor: pointer; user-select: none;">Manual JSON Input (fallback)</summary>
                <div style="margin-top: 10px;">
                    <textarea
                        x-model="manualInput"
                        rows="3"
                        style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; font-family: monospace; font-size: 12px; resize: vertical;"
                        placeholder='{"type":"subscription_redeem","user_id":"1","subscription_id":"1","user_subscription_id":"1"}'
                    ></textarea>
                    <button @click="processManual()" style="margin-top: 8px; padding: 8px 16px; background: #4f46e5; color: white; font-weight: 600; font-size: 13px; border: none; border-radius: 8px; cursor: pointer;">
                        Process Manual Input
                    </button>
                </div>
            </details>
        </div>

        {{-- Scanned Result --}}
        <div x-show="scannedData" x-transition style="background: #f0fdf4; border-radius: 16px; border: 1px solid #bbf7d0; padding: 24px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#16a34a" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span style="font-size: 16px; font-weight: 700; color: #166534;">QR Code Detected!</span>
            </div>
            <pre x-text="scannedData" style="background: white; padding: 12px; border-radius: 8px; font-size: 12px; overflow-x: auto; border: 1px solid #dcfce7; margin-bottom: 16px;"></pre>

            <div style="display: flex; gap: 10px;">
                <button @click="submitToServer()" :disabled="processing" style="padding: 12px 24px; background: #16a34a; color: white; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; opacity: 1;" :style="processing && 'opacity: 0.5; cursor: not-allowed;'">
                    <span x-text="processing ? 'Processing...' : '✓ Confirm Redemption'"></span>
                </button>
                <button @click="scannedData = ''" style="padding: 12px 24px; background: #f3f4f6; color: #374151; font-weight: 600; font-size: 14px; border: 1px solid #d1d5db; border-radius: 10px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>

        {{-- Last Redemption Result --}}
        @if($lastResult)
            <div style="background: #ecfdf5; border-radius: 16px; border: 1px solid #a7f3d0; padding: 24px; margin-bottom: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #065f46; margin: 0 0 16px 0;">✅ Last Redemption Successful</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                    <div style="background: white; padding: 14px; border-radius: 10px; border: 1px solid #d1fae5;">
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;">Order Number</p>
                        <p style="font-size: 14px; font-weight: 700; color: #111827; margin: 6px 0 0 0;">{{ $lastResult['order_number'] }}</p>
                    </div>
                    <div style="background: white; padding: 14px; border-radius: 10px; border: 1px solid #d1fae5;">
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;">Subscription</p>
                        <p style="font-size: 14px; font-weight: 700; color: #111827; margin: 6px 0 0 0;">{{ $lastResult['subscription_name'] }}</p>
                    </div>
                    <div style="background: white; padding: 14px; border-radius: 10px; border: 1px solid #d1fae5;">
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;">Drinks Remaining</p>
                        <p style="font-size: 14px; font-weight: 700; color: #16a34a; margin: 6px 0 0 0;">{{ $lastResult['drinks_remaining'] }}</p>
                    </div>
                    <div style="background: white; padding: 14px; border-radius: 10px; border: 1px solid #d1fae5;">
                        <p style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;">Total Used</p>
                        <p style="font-size: 14px; font-weight: 700; color: #111827; margin: 6px 0 0 0;">{{ $lastResult['drinks_used'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- html5-qrcode CDN --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function qrScannerApp() {
            return {
                scanning: false,
                scannedData: '',
                manualInput: '',
                processing: false,
                scanner: null,

                init() {
                    // Pre-warm
                },

                async startScanner() {
                    try {
                        // Check if camera API is available (requires HTTPS or localhost)
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            alert('Live camera is not available on HTTP. Please use "Scan from Image / Take Photo" button instead.');
                            return;
                        }

                        this.scanner = new Html5Qrcode("qr-reader");

                        await this.scanner.start(
                            { facingMode: "environment" },
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 },
                                aspectRatio: 1.0,
                            },
                            (decodedText) => {
                                // Success callback
                                this.scannedData = decodedText;
                                this.stopScanner();

                                // Audio feedback
                                try {
                                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                    const osc = ctx.createOscillator();
                                    osc.type = 'sine';
                                    osc.frequency.value = 880;
                                    osc.connect(ctx.destination);
                                    osc.start();
                                    setTimeout(() => osc.stop(), 150);
                                } catch(e) {}
                            },
                            (errorMessage) => {
                                // Ignore scan errors (no QR in frame)
                            }
                        );

                        this.scanning = true;
                    } catch (err) {
                        alert('Camera error: ' + err.message + '\n\nMake sure you allow camera access and are on HTTPS.');
                    }
                },

                async stopScanner() {
                    if (this.scanner && this.scanning) {
                        try {
                            await this.scanner.stop();
                        } catch (e) {}
                        this.scanning = false;
                    }
                },

                async scanFromFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    try {
                        // Create a temporary scanner instance for file scanning
                        const fileScanner = new Html5Qrcode("qr-reader");

                        const result = await fileScanner.scanFile(file, true);

                        // Success!
                        this.scannedData = result;

                        // Beep
                        try {
                            const ctx = new (window.AudioContext || window.webkitAudioContext)();
                            const osc = ctx.createOscillator();
                            osc.type = 'sine';
                            osc.frequency.value = 880;
                            osc.connect(ctx.destination);
                            osc.start();
                            setTimeout(() => osc.stop(), 150);
                        } catch(e) {}

                        await fileScanner.clear();
                    } catch (err) {
                        alert('No QR code found in the image. Please try again with a clearer image.');
                    }

                    // Reset file input so same file can be re-selected
                    event.target.value = '';
                },

                processManual() {
                    if (this.manualInput.trim()) {
                        this.scannedData = this.manualInput.trim();
                    }
                },

                async submitToServer() {
                    this.processing = true;

                    // Set the Livewire property and call the method
                    @this.set('qrData', this.scannedData);

                    await @this.call('processRedemption');

                    this.processing = false;
                    this.scannedData = '';

                    // Restart scanner after short delay
                    setTimeout(() => {
                        if (!this.scanning) {
                            this.startScanner();
                        }
                    }, 2000);
                }
            };
        }
    </script>
</x-filament-panels::page>
