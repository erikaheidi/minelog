@php($gaMeasurementId = config('services.google_analytics.id'))

@if (filled($gaMeasurementId))
    {{-- Cookie consent banner. Google Analytics is only loaded after the visitor opts in. --}}
    <div id="cookie-consent" hidden
         class="fixed inset-x-0 bottom-0 z-50 border-t border-mine-line bg-mine-panel/95 backdrop-blur">
        <div class="mx-auto flex max-w-4xl flex-col gap-3 px-4 py-4 text-sm text-mine-muted sm:flex-row sm:items-center sm:gap-6 sm:px-6">
            <p class="flex-1">
                {{ __('We use analytics cookies to understand how Minelog is used and improve it. See our') }}
                <a href="{{ route('privacy-policy') }}" class="font-semibold text-mine-green-2 underline">{{ __('Privacy Policy') }}</a>.
            </p>
            <div class="flex shrink-0 items-center gap-3">
                <button type="button" id="cookie-decline"
                        class="rounded-lg px-4 py-2 font-semibold text-mine-muted transition hover:text-mine-text">
                    {{ __('Decline') }}
                </button>
                <button type="button" id="cookie-accept"
                        class="rounded-lg bg-mine-green px-4 py-2 font-bold text-white transition hover:bg-mine-green-2">
                    {{ __('Accept') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var GA_ID = @json($gaMeasurementId);
            var STORAGE_KEY = 'minelog_analytics_consent';

            function loadGoogleAnalytics() {
                if (window.__minelogGaLoaded) {
                    return;
                }
                window.__minelogGaLoaded = true;

                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA_ID);
                document.head.appendChild(script);

                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { window.dataLayer.push(arguments); };
                window.gtag('js', new Date());
                window.gtag('config', GA_ID, { anonymize_ip: true });
            }

            function readConsent() {
                try {
                    return localStorage.getItem(STORAGE_KEY);
                } catch (e) {
                    return null;
                }
            }

            function storeConsent(value) {
                try {
                    localStorage.setItem(STORAGE_KEY, value);
                } catch (e) {}
            }

            var banner = document.getElementById('cookie-consent');
            var consent = readConsent();

            if (consent === 'granted') {
                loadGoogleAnalytics();
            } else if (consent !== 'denied' && banner) {
                banner.hidden = false;
            }

            function resolve(value) {
                storeConsent(value);
                if (banner) {
                    banner.hidden = true;
                }
                if (value === 'granted') {
                    loadGoogleAnalytics();
                }
            }

            var acceptButton = document.getElementById('cookie-accept');
            var declineButton = document.getElementById('cookie-decline');

            if (acceptButton) {
                acceptButton.addEventListener('click', function () { resolve('granted'); });
            }
            if (declineButton) {
                declineButton.addEventListener('click', function () { resolve('denied'); });
            }
        })();
    </script>
@endif
