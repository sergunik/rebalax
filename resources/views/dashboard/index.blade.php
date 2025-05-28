@extends('layouts.app')

@section('header')
    Дашборд
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-4">Ласкаво просимо, {{ Auth::user()->name }}!</h2>
            <p class="text-gray-600">Це ваш особистий кабінет.</p>
        </div>
    </div>
@endsection 