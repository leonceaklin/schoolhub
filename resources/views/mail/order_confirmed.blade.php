@extends('mail.base')

@section('body')
@php $item = $copy->_item @endphp
<img class="item-cover" src="{{ $item->_cover->thumbnailUrl }}" alt="{{ $item->title }}">
<h2 class="item-title">{{ $item->title }}</h2>
<h3 class="item-authors">{{ $item->authors }}</h3>
<h2 class="item-title">CHF {{ $copy->price }}.-</h2>


Hallo {{ $copy->orderedBy->first_name }}<br>
<p>Du hast vor Kurzem "{{ $item->title }}" von {{ $item->authors }}@php if($copy->edition){
  $edition = $copy->_edition;
  echo " (".$edition->number.". Auflage, ".$edition->year."";
  if($edition->name){
    echo ' "'.$edition->name.'"';
  }
  echo ")";
} @endphp im Bookstore bestellt. Vielen Dank dafür.</p>
<p>Referenz-Code: <span class="monospace">{{ $copy->uid }}-{{ $item->isbn }}</span></p>
<img src="{{ url('/images/pickup.svg') }}" class="icon" alt="Abholung und Bezahlung">
<div class="icon-side-text"><h2>Abholung und Bezahlung</h2>
Abholen und bezahlen kannst du deine Bestellung beim Bookstore PickUp neben dem Lichthof. Bitte beachte, dass wir nur Zahlungen in Bar entgegennehmen können. Du brauchst keine Bestätigung der Bestellung. Sag uns einfach, wer du bist.
</div>
<br><br>
<h2>Stornierung</h2>
Hast du etwas falsches bestellt? Du kannst die Bestellung hier stornieren. Es fallen keine Gebühren an.
<a class="button" href="{{ url('/?cancelorder='.$copy->order_hash) }}">Bestellung stornieren</a>
@endsection
