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
            You have received a new payment from <b>{{$data['company']['name']}}</b>
            @component('mail::button', ['url' => url('/payments/pdf/'.$data['payment']['unique_hash'])])
                View Payment
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
