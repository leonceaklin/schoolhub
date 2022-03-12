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
<p>Du hast vor Kurzem ein Exemplar von "{{ $item->title }}"@php if($copy->_edition){
  $edition = $copy->_edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} @endphp zum Verkauf eingereicht.</p>
<br>
<h2 class="center">{{ __("bookstore.copy_code") }}</h2>
<div class="uid-large">{{ substr($copy->uid, 0,3) }} {{ substr($copy->uid, 3,6) }}</div>
<p>{{ __("bookstore.copy_code_info") }}</p>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="{{ __("bookstore.what_next") }}">
<div class="icon-side-text"><h2>{{ __("bookstore.what_next") }}</h2>
  {{ __("bookstore.copy_submitted_further", ["price" => $copy->price.".-", "commission" => $copy->commission*100, "payback" => $copy->payback]) }}
</div>
<br><br>
<h2>{{ __("bookstore.cancellation") }}</h2>
{{ __("bookstore.submission_cancellation_info") }}
<a class="button" href="{{ url('/bookstore/cancel/'.$copy->order_hash) }}">{{ __("bookstore.cancel_submission") }}</a>
@endsection
