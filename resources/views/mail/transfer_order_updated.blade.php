@extends('mail.base')

@section('body')
<p>Hallo</p>

<p>Der Überweisungsauftrag für den Monat {{ $transferOrder->created_on->subDays(1)->format("m.Y") }} ist soeben aktualistert worden.<br>
Hier ist die neue Fassung.</p>

<p>Freundliche Grüsse</p>
<p>Der SchoolHub Bot</p>
<p>Beep Beep Beep</p>
@endsection
