@title('Something went wrong')

@extends('layouts.base', ['disableAds' => true])

@section('body')
    <div class="my-20 text-center text-gray-800">
        <h1 class="text-5xl">{{ $title }}</h1>
        <p class="text-lg">
            We've been notified and will try to fix the problem as soon as possible.<br>
            Please <a href="https://github.com/laravelio/portal/issues/new" class="text-green-darker">open a Github issue</a> if the problem persists.
        </p>
    </div>
@endsection
