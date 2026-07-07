<x-layout.main title="Feedbacks">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Feedbacks</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section max-w-prose">
        @forelse ($feedbacks as $feedback)
            <div class="card p-inline mb-inline">
                <p class="whitespace-pre-wrap">{{ $feedback->message }}</p>
                <p class="mt-2 text-sm text-text-secondary">{{ $feedback->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-text-secondary">Noch kein Feedback eingegangen.</p>
        @endforelse
    </main>
</x-layout.main>
