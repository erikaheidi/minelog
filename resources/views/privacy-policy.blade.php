<x-layouts.public :title="__('Privacy Policy')">
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-mine-line">
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(to bottom, rgba(94,168,79,0.14), transparent);"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 sm:py-20">
            <h1 class="mx-auto max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">
                {{ __('Privacy Policy') }}
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-mine-muted">
                {{ __('How Minelog handles your data. In short: we collect only what we need to run the service, we host in the EU, and we never sell or share your personal data.') }}
            </p>
            <p class="mt-4 text-sm text-mine-muted">{{ __('Last updated: :date', ['date' => 'July 12, 2026']) }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        <div class="space-y-10">
            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Who we are') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog is a travel log for your Minecraft worlds that lets you save, organize, map, and optionally share waypoints. This Privacy Policy explains what information we collect when you use Minelog, why we collect it, and the choices you have. If you have any questions, contact us at') }}
                    <a href="mailto:contact@camboa.io" class="font-semibold text-mine-green-2 underline">contact@camboa.io</a>.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Information we collect') }}</h2>
                <ul class="mt-4 space-y-3 text-mine-muted">
                    <li class="flex gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-mine-green-2"></span>
                        <span><span class="font-semibold text-mine-text">{{ __('Account information.') }}</span> {{ __('When you sign in with Google, we receive your name, email address, and profile picture from your Google account. We use this to create and secure your Minelog account. We do not receive or store your Google password.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-mine-green-2"></span>
                        <span><span class="font-semibold text-mine-text">{{ __('Content you create.') }}</span> {{ __('The worlds, waypoints, notes, tags, screenshots, and seeds you add to Minelog are stored so we can show them back to you.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-mine-green-2"></span>
                        <span><span class="font-semibold text-mine-text">{{ __('Technical data.') }}</span> {{ __('Like most web services, our servers automatically record basic technical information such as your IP address and browser type in order to operate the service securely and reliably.') }}</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-mine-green-2"></span>
                        <span><span class="font-semibold text-mine-text">{{ __('Usage analytics.') }}</span> {{ __('We use Google Analytics to understand how the service is used in aggregate — for example, which pages are visited and from what type of device. This helps us improve Minelog. See "Third-party services" and "Cookies" below.') }}</span>
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('How we use your information') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We use your information to provide and maintain Minelog: to authenticate you, store and display the worlds and waypoints you create, keep the service secure and working correctly, and understand how the service is used so we can improve it. We do not use your personal data for advertising, and we do not sell, rent, or trade it to anyone.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('What is public and what stays private') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Your worlds and waypoints are private by default and visible only to you. A world becomes publicly viewable only when you choose to flip it to Public — at that point, anyone with the link can browse its gallery, map, and seed without an account. You can switch a world back to private at any time. Your email address is never shown publicly.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Where your data is stored') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog is hosted on Laravel Cloud infrastructure located in the European Union. Your data is processed and stored on EU-based servers.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Third-party services') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We rely on a small number of trusted providers to run Minelog — notably Google (for sign-in and for Google Analytics) and our hosting provider. These services process your data only to the extent needed to provide their function. We use Google Analytics solely to measure aggregate usage, not for advertising, and we do not integrate advertising networks. You can learn how Google handles this data in Google\'s own privacy policy.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Cookies') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We use cookies that are strictly necessary to keep you signed in and to keep the service secure. We also use Google Analytics cookies to measure aggregate usage. We do not use advertising cookies. Where required by law, analytics cookies are only set after you consent, and you can decline or withdraw consent at any time.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Your rights') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Depending on where you live, you may have the right to access, correct, export, or delete your personal data, and to object to or restrict certain processing. You can delete your worlds and waypoints at any time from within Minelog. To delete your account or make any other request, contact us at') }}
                    <a href="mailto:contact@camboa.io" class="font-semibold text-mine-green-2 underline">contact@camboa.io</a>
                    {{ __('and we will respond within a reasonable time.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Data retention') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We keep your account information and content for as long as your account is active. If you delete your account, we remove your personal data and content, except where we are required to retain certain information to comply with legal obligations.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Children') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Minelog is not directed to children under the age required to consent to data processing in their country. If you believe a child has provided us with personal data, please contact us and we will remove it.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Changes to this policy') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('We may update this Privacy Policy from time to time. When we do, we will revise the "Last updated" date above. Significant changes may be announced within the service.') }}
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold tracking-tight">{{ __('Contact') }}</h2>
                <p class="mt-3 text-mine-muted">
                    {{ __('Questions about this policy or your data? Email us at') }}
                    <a href="mailto:contact@camboa.io" class="font-semibold text-mine-green-2 underline">contact@camboa.io</a>.
                </p>
            </section>
        </div>
    </div>
</x-layouts.public>
