@extends('mail.base')

@section('body')
@php $item = $copy->_item @endphp
<img class="item-cover" src="{{ $item->_cover->thumbnailUrl }}" alt="{{ $item->title }}">
<h2 class="item-title">{{ $item->title }}</h2>
<h3 class="item-authors">{{ $item->authors }}</h3>

Hallo {{ $copy->ownedBy->first_name }}<br>
<p>Du hast ein Exemplar von "{{ $item->title }}"@php if($copy->edition){
  $edition = $copy->_edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} @endphp zum Verkauf abgegeben. Wir haben es nun überprüft und im Store verfügbar gemacht.</p>
<br>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="Wie weiter?">
<div class="icon-side-text"><h2>Wie weiter?</h2>
Wir werden es für dich nun für CHF {{ $copy->price }}.- verkaufen. Abzüglich einer Provision von {{ $copy->commission*100 }}% erhältst du CHF {{ $copy->payback }} von uns nach dem Verkauf.
</div>
@endsection
