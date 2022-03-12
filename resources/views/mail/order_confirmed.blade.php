@extends('mail.base')

@section('body')
@php $item = $copy->_item @endphp
<div class="cover-effect-wrapper">
<div class="cover-effect">
<img class="item-cover" src="{{ $item->_cover->thumbnailUrl }}" alt="{{ $item->title }}">
</div>
</div>
<h2 class="item-title">{{ $item->title }}</h2>
<h3 class="item-authors">{{ $item->authors }}</h3>
<h2 class="item-title">CHF {{ $copy->price }}.-</h2>


{{ __("bookstore.greeting", ["name" => $copy->orderedBy->first_name]) }}<br>
<p>{{ __("bookstore.order_confirmed_introduction", ["item_name" => $copy->longName, "store_name" => $copy->_store->name]) }}</p>
<p>{{ __("bookstore.reference_code") }} <span class="monospace">{{ $copy->uid }}-{{ $item->isbn }}</span></p>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="{{ __("bookstore.pickup_and_payment") }}">
<div class="icon-side-text"><h2>{{ __("bookstore.pickup_and_payment") }}</h2>
{{ __("bookstore.pickup_info") }}
</div>
<br><br>
<h2>{{ __("bookstore.cancellation") }}</h2>
{{ __("bookstore.order_cancellation_info") }}
<a class="button" href="{{ url('bookstore/cancel/'.$copy->order_hash) }}">{{ __("bookstore.cancel_order") }}</a>
@endsection
