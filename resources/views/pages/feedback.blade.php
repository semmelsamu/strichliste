<x-layout.main title="Feedback">
    <x-header class="wrapper px-wrapper py-6">
        <h1>Feedback zur Strichliste</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section space-y-content max-w-sm">
        <x-form post="{{ route('feedback.store') }}" class="mx-auto">
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="feedback-website">Website</label>
                <input type="text" id="feedback-website" name="website" tabindex="-1" autocomplete="off" />
            </div>
            <div class="w-full space-y-form-labels">
                <label for="feedback-message" class="block">Kritik, Fragen, Anregungen...</label>
                <textarea
                    id="feedback-message"
                    name="message"
                    class="text-input p-3 w-full min-h-32"
                    required
                    autofocus
                >{{ old('message') }}</textarea>
                @error('message')
                    <p class="form-error-text">{{ $message }}</p>
                @enderror
            </div>

            <x-input.submit class="ml-auto">Absenden</x-input.submit>
        </x-form>

        <p class="text-text-secondary text-center">Du hast technisches Feedback? Dann <a target="_blank" class="underline text-text-primary" href="https://github.com/semmelsamu/strichliste/issues/new">erstelle ein neues Issue auf GitHub</a>!</p>
    </main>
</x-layout.main>
