@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => ''])
        @if($data['company']['logo'])
            <img class="header-logo" src="{{asset($data['company']['logo'])}}" alt="{{$data['company']['name']}}">
        @else
            {{$data['company']['name']}}
        @endif
        @endcomponent
    @endslot

    {{-- Body --}}
    <!-- Body here -->

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            You have received a new invoice from <b>{{$data['company']['name']}}</b>
            @component('mail::button', ['url' => url('/customer/invoices/pdf/'.$data['invoice']['unique_hash'])])
                View Invoice
            @endcomponent
        @endcomponent
    @endslot
     {{-- Footer --}}
     @slot('footer')
        @component('mail::footer')
            Powered by <a class="footer-link" href="https://craterapp.com">Crater</a>
        @endcomponent
    @endslot
@endcomponent
