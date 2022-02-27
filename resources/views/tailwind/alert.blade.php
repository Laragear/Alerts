<div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 {{ $alert->classes }}">
    @if($alert->dismissible)
        <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
    @endif
    {!! $alert->message !!}
</div>