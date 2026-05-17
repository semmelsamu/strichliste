<button class="card">
    <div class="grid aspect-video place-items-center bg-black bg-fsim-light">
        <x-lucide-file-image />
    </div>
    <div class="flex justify-between p-inline">
        <strong>{{ $article->name }}</strong>
        <span>{{ Number::currency($article->price) }}</span>
    </div>
</button>
