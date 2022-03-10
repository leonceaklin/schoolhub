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

Hallo {{ $copy->ownedBy->first_name }}<br>
<p>Du hast vor Kurzem ein Exemplar von "{{ $item->title }}"@php if($copy->_edition){
  $edition = $copy->_edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} @endphp zum Verkauf eingereicht.</p>
<br>
<h2 class="center">Exemplar-Code</h2>
<div class="uid-large">{{ substr($copy->uid, 0,3) }} {{ substr($copy->uid, 3,6) }}</div>
<p>Schreibe diesen Code auf einen Zettel und bringe diesen am Buch an.</p>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="Wie weiter?">
<div class="icon-side-text"><h2>Wie weiter?</h2>
Bring das Exemplar in den nächsten Tagen beim Bookstore PickUp vorbei, wo wir den Zustand bestimmen und es anschliessend im Store verfügbar machen werden.
Wir werden es für dich für CHF {{ $copy->price }}.- verkaufen. Abzüglich einer Provision von {{ $copy->commission*100 }}% erhältst du CHF {{ $copy->payback }} von uns nach dem Verkauf.
</div>
<br><br>
<h2>Stornierung</h2>
Möchtest du das Buch doch nicht verkaufen? Dann storniere bitte deine Einreichung.
<a class="button" href="{{ url('/bookstore/cancel/'.$copy->order_hash) }}">Verkauf stornieren</a>
@endsection
