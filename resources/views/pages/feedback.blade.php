<x-layout.main title="Feedback">
    <x-header class="wrapper px-wrapper py-6">
        <h1>Feedback zur Strichliste</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form post="{{ route('feedback.store') }}" class="mx-auto max-w-sm">
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
    </main>
</x-layout.main>
