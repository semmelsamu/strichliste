<x-layout.main title="Verlauf">
    <x-header.tally-sheet activeTab="history" />

    <main class="wrapper space-y-section px-wrapper py-section">
        <h1 class="hidden print:block">Transaktionen</h1>
        <livewire:livewire.transaction-history defer />
    </main>
</x-layout.main>
