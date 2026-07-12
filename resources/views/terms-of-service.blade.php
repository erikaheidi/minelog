<x-layouts.public :title="__('Terms of Service')">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-mine-line">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, rgba(94,168,79,0.14), transparent);"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 sm:py-20">
            <h1 class="mx-auto max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">
                {{ __('Terms of Service') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-mine-muted">
                {{ __('The ground rules for using Minelog. By creating an account or using the service, you agree to these terms.') }}
            </p>
            <p class="mt-4 text-sm text-mine-muted">{{ __('Last updated: :date', ['date' => 'July 12, 2026']) }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        <div class="space-y-10">
            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('1. Acceptance of terms') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog is a travel log for your Minecraft worlds that lets you save, organize, map, and optionally share waypoints. By accessing or using Minelog, you agree to be bound by these Terms of Service and by our') }}
                    <a href="{{ route('privacy-policy') }}" class="font-semibold text-mine-green-2 underline">{{ __('Privacy Policy') }}</a>. {{ __('If you do not agree, please do not use the service.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('2. Your account') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('You sign in to Minelog using your Google account. You are responsible for the activity that happens under your account and for keeping your login credentials secure. You must provide accurate information and be old enough to consent to data processing in your country. You may stop using Minelog and request deletion of your account at any time.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('3. Your content') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('You keep ownership of the worlds, waypoints, notes, tags, screenshots, and other content you create in Minelog. You grant us only the limited permission needed to store, display, and operate that content as part of running the service for you. You are responsible for the content you upload and confirm you have the right to use it.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('4. Public worlds') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Your worlds are private by default. If you choose to make a world public, anyone with its link can view its gallery, map, and seed without an account. Only make a world public if you are comfortable sharing that information. You can switch a world back to private at any time.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('5. Acceptable use') }}</h2>
                <p class="mt-3 text-mine-muted">{{ __('When using Minelog, you agree not to:') }}</p>
                <ul class="mt-4 space-y-3 text-mine-muted">
                    @foreach ([
                        __('Break any applicable law or infringe anyone else\'s rights, including intellectual property rights.'),
                        __('Upload content that is illegal, harmful, harassing, hateful, or sexually explicit.'),
                        __('Attempt to gain unauthorized access to the service, other accounts, or our systems.'),
                        __('Interfere with, disrupt, or place undue load on the service, or use it to distribute malware or spam.'),
                        __('Misrepresent your identity or use the service to impersonate others.'),
                    ] as $rule)
                        <li class="flex gap-3">
                            <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-mine-green-2"></span>
                            <span>{{ $rule }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('6. Intellectual property') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog and its original software, design, and branding belong to us and our licensors. These terms do not grant you any right to our trademarks or branding. Minecraft is a trademark of Mojang AB; Minelog is an independent tool and is not affiliated with, endorsed by, or sponsored by Mojang or Microsoft.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('7. Service availability') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog is provided on an "as is" and "as available" basis. We work to keep the service running reliably, but we do not guarantee it will always be uninterrupted, error-free, or available. We may add, change, or remove features, and we may suspend or discontinue the service at any time.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('8. Termination') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('You may stop using Minelog and delete your account at any time. We may suspend or terminate your access if you violate these terms or use the service in a way that could harm Minelog, other users, or third parties.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('9. Disclaimer and limitation of liability') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('To the fullest extent permitted by law, Minelog is provided without warranties of any kind, and we are not liable for any indirect, incidental, or consequential damages, or for any loss of data, arising from your use of the service. Nothing in these terms limits any rights you have that cannot be limited under applicable law.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('10. Changes to these terms') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We may update these Terms of Service from time to time. When we do, we will revise the "Last updated" date above. Your continued use of Minelog after changes take effect means you accept the updated terms.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('11. Contact') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Questions about these terms? Email us at') }}
                    <a href="mailto:contact@camboa.io" class="font-semibold text-mine-green-2 underline">contact@camboa.io</a>.
                </p>
            </section>
        </div>
    </div>
</x-layouts.public>
