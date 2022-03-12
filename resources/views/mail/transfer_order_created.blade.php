@extends('mail.base')

@section('body')
<p>Hallo</p>

<p>Hier ist der Überweisungsauftrag für alle Exemplare, während des letzten Monats ({{ $transferOrder->created_on->subDays(1)->format("m.Y") }}) verkauft wurden oder deren Besitzer:innen ihre Zahlungsdaten nachgeführt haben.
Der Überweisunsauftrag sollte nach der Erledigung im System als "Erledigt" markiert werden.</p>

<p>Freundliche Grüsse</p>
<p>Der SchoolHub Bot</p>
<p>Beep Beep Beep</p>
@endsection
