@extends('mail.base')

@section('body')
  {{ __("bookstore.greeting", ["name" => $user->first_name]) }}<br>

{{ __("bookstore.contact_details_needed_introduction", sizeof($user->copies), ["store_name" => "GymLi Bookstore"]) }}
{{ __("bookstore.contact_details_info_needed") }}

@php $fields = ["zip", "city", "iban"]; @endphp
<ul>
  @foreach($fields as $field)
    @if($user->$field == null)
      <li>{{ __("auth.".$field) }}</li>
    @endif
  @endforeach
</ul>
<br><br>
@php
  $soldCopies = $user->copies()->where("status", "sold")->get();
@endphp
{{ __("bookstore.contact_details_sold_copies", sizeof($soldCopies)) }}
<ul>
  @foreach($soldCopies as $copy)
    {{ $copy->_item->title }} - CHF {{ $copy->payback }}
  @endforeach
</ul><br><br>
{{ __("bookstore.contact_details_needed_action") }}
<br><br>
{{ __("bookstore.best_regards") }}
@endsection
