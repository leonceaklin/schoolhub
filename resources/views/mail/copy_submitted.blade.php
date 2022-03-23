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
<p>{{ __("bookstore.copy_submitted_introduction", ["item_name" => $copy->longName]) }}</p>
<br>
<h2 class="center">{{ __("bookstore.copy_code") }}</h2>
<div class="uid-large">{{ substr($copy->uid, 0,3) }} {{ substr($copy->uid, 3,6) }}</div>
<p>{{ __("bookstore.copy_code_info") }}</p>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="{{ __("bookstore.what_next") }}">
<div class="icon-side-text"><h2>{{ __("bookstore.what_next") }}</h2>
  @if($copy->donation)
    {{ __("bookstore.copy_submitted_further_donate", ["price" => "CHF ".$copy->price.".-", "amount" => "CHF ".$copy->charityAmount, "charity_name" => $copy->_charity->name]) }}
  @else
  {{ __("bookstore.copy_submitted_further_sell", ["price" => "CHF ".$copy->price.".-", "commission" => $copy->realCommission*100, "payback" => "CHF ".$copy->payback]) }}
  @endif
</div>
<br><br>
<h2>{{ __("bookstore.cancellation") }}</h2>
{{ __("bookstore.submission_cancellation_info") }}
<a class="button" href="{{ url('/bookstore/cancel/'.$copy->order_hash) }}">{{ __("bookstore.cancel_submission") }}</a>
@endsection
