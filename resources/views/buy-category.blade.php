<x-layouts.auth
    :title="$category->name . ' kaufen'"
    activeTab="buy"
    :user="$user"
>
    <x-wrapper class="space-y-inline">
        <h2>{{ $category->name }}</h2>
        <div class="grid grid-cols-3 gap-inline">
            @foreach ($category->articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>
    </x-wrapper>
</x-layouts.auth>
