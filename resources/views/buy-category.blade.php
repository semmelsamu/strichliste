<x-layouts.auth :title="$category->name . ' kaufen'">
    <x-wrapper class="space-y-inline">
        <h2>{{ $category->name }}</h2>
        <div class="grid grid-cols-3 gap-inline">
            @foreach ($category->articles as $article)
                <button class="card">
                    <div
                        class="grid aspect-video place-items-center bg-black bg-fsim-light"
                    >
                        <x-lucide-file-image />
                    </div>
                    <div class="flex justify-between p-inline">
                        <strong>{{ $article->name }}</strong>
                        <span>{{ $article->price }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </x-wrapper>
</x-layouts.auth>
