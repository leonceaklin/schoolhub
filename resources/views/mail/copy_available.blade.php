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

{{ __("bookstore.greeting", ["name" => $copy->ownedBy->first_name]) }}<br>

<p>{{ __("bookstore.copy_available_introduction", ["item_name" => $copy->longName]) }}</p>
<br>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="{{ __("bookstore.what_next") }}">
<div class="icon-side-text"><h2>{{ __("bookstore.what_next") }}</h2>
  {{ __("bookstore.copy_available_further", ["price" => $copy->price.".-", "commission" => $copy->commission*100, "payback" => $copy->payback]) }}
</div>
@endsection
