@unless (app()->environment('local'))
    <div
        x-data="inactivityTimeout('{{ route('tally-sheet.auth.logout') }}')"
    ></div>
@endunless
