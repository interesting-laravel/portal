@title($thread->subject())

@extends('layouts.default')

@section('content')
    <div class="container mx-auto px-4 pt-4">
        <h1 class="text-3xl text-gray-900 mb-4">
            {{ $title }}
        </h1>
        <div class="flex flex-wrap">
            <div class="w-full md:w-3/4 md:pr-3">
                <div class="reply bg-white p-4 border rounded">
                    <div>
                        <reply :content="{{ json_encode(md_to_html($thread->body())) }}"/>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center text-sm pt-5 border-t mt-4">
                        <div class="flex mb-4 md:mb-0">
                            @include('forum.threads.info.avatar', ['user' => $thread->author()])

                            <div class="mr-6 text-gray-700">
                                <a href="{{ route('profile', $thread->author()->username()) }}" class="text-green-darker mr-2">{{ $thread->author()->name() }}</a> posted
                                {{ $thread->createdAt()->diffForHumans() }}
                            </div>
                        </div>

                        @include('forum.threads.info.tags')
                    </div>

                </div>

                <div class="mt-4">
                    @include('layouts._ads._bsa-cpc')
                </div>

                @foreach ($thread->replies() as $reply)
                    <div class="reply mt-8 bg-white rounded {{ $thread->isSolutionReply($reply) ? 'border-2 border-green-primary' : 'border' }}">
                        
                        @if ($thread->isSolutionReply($reply))
                            <div class="bg-green-primary text-white uppercase px-4 py-2 opacity-75">
                                Solution
                            </div>
                        @endif

                        <div class="p-4">
                            <div>
                                <reply :content="{{ json_encode(md_to_html($reply->body())) }}"/>
                            </div>
                            <div class="flex flex-col md:flex-row md:items-center text-sm pt-5 border-t mt-4">
                                <div class="flex flex-wrap mb-4 md:mb-0 justify-between w-full">
                                    <div class="flex">
                                        @include('forum.threads.info.avatar', ['user' => $reply->author()])

                                        <div class="mr-6 mb-4 md:mb-0 text-gray-700">
                                            <a href="{{ route('profile', $reply->author()->username()) }}" class="text-green-darker">
                                                {{ $reply->author()->name() }}
                                            </a> replied
                                            {{ $reply->createdAt()->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="flex reply-options">

                                        @can(App\Policies\ReplyPolicy::UPDATE, $reply)
                                            <a class="label label-primary" href="{{ route('replies.edit', $reply->id()) }}">
                                                Edit
                                            </a>
                                            <a href="#" @click.prevent="activeModal = 'delete-reply-{{ $reply->id }}'" class="label label-danger">
                                                Delete
                                            </a>
                                        @endcan

                                        @can(App\Policies\ThreadPolicy::UPDATE, $thread)
                                
                                            @if ($thread->isSolutionReply($reply))
                                                <a href="#" @click.prevent="activeModal = 'unmark-solution-{{ $thread->id }}'" class="label">
                                                    Unmark As Solution
                                                </a>

                                                @include('_partials._update_modal', [
                                                    'identifier' => "unmark-solution-{$thread->id}",
                                                    'route' => 'threads.solution.unmark',
                                                    'routeParams' => $thread->slug(),
                                                    'title' => 'Unmark As Solution',
                                                    'body' => '<p>Confirm to unmark this reply as the solution for <strong>"'.e($thread->subject()).'"</strong>.</p>',
                                                ])
                                            @else
                                                <a href="#" @click.prevent="activeModal = 'mark-solution-{{ $reply->id }}'" class="label" >
                                                    Mark As Solution
                                                </a>

                                                @include('_partials._update_modal', [
                                                    'identifier' => "mark-solution-{$reply->id}",
                                                    'route' => 'threads.solution.mark',
                                                    'routeParams' => [$thread->slug(), $reply->id()],
                                                    'title' => 'Mark As Solution',
                                                    'body' => '<p>Confirm to mark this reply as the solution for <strong>"'.e($thread->subject()).'"</strong>.</p>',
                                                ])
                                            @endif
                                            
                                        @endcan
                                    </div>
                                </div>              
                            </div>
                        </div>
                    </div>

                    @include('_partials._delete_modal', [
                        'identifier' => "delete-reply-{$reply->id}",
                        'route' => 'replies.delete',
                        'routeParams' => $reply->id(),
                        'title' => 'Delete Reply',
                        'body' => '<p>Are you sure you want to delete this reply? This cannot be undone.</p>',
                    ])
                @endforeach

                @can(App\Policies\ReplyPolicy::CREATE, App\Models\Reply::class)
                    @if ($thread->isConversationOld())
                        <hr>
                        <p class="text-center">
                            The last reply to this thread was more than six months ago. Please consider <a href="{{ route('threads.create') }}">opening a new thread</a> if you have a similar question.
                        </p>
                    @else
                        <div class="my-8">

                            <form action="{{ route('replies.store') }}" method="POST">
                                @csrf

                                @formGroup('body')
                                    <label for="body">Write a reply</label>
                                    <textarea name="body" id="body" class="editor"></textarea>
                                    @error('body')
                                @endFormGroup

                                <input type="hidden" name="replyable_id" value="{{ $thread->id() }}" />
                                <input type="hidden" name="replyable_type" value="threads" />

                                <div class="flex justify-between items-center mt-4">
                                    <p class="text-sm text-gray-500 mr-8">
                                        Please make sure you've read our <a href="{{ route('rules') }}" class="text-green-dark">Forum Rules</a> before replying to this thread.
                                    </p>

                                    <button type="submit" class="button button-primary">Reply</button>
                                </div>
                            </form>

                        </div>
                    @endif
                @else
                    @if (Auth::guest())
                        <p class="text-center text-gray-800 border-t py-8">
                            <a href="{{ route('login') }}" class="text-green-darker">Sign in</a> to participate in this thread!
                        </p>
                    @else
                        <div class="bg-gray-400 rounded p-4 text-gray-700 my-8">
                            <p>You'll need to verify your account before participating in this thread.</p>
                            <p><a href="{{ route('email.send_confirmation') }}" class="text-green-dark">Click here to resend the verification link.</p>
                        </div>
                    @endif
                @endcan
            </div>  
            <div class="w-full hidden md:w-1/4 md:pl-3 md:pt-4 md:flex items-center flex-col text-center mb-4">
                @include('users._user_info', ['user' => $thread->author(), 'avatarSize' => 100])

                <div class="reply-options">
                @can(App\Policies\ThreadPolicy::UPDATE, $thread)
                    <a class="label" href="{{ route('threads.edit', $thread->slug()) }}">
                        Edit
                    </a>
                @endcan

                @can(App\Policies\ThreadPolicy::UNSUBSCRIBE, $thread)
                    <a class="label" href="{{ route('threads.unsubscribe', $thread->slug()) }}">
                        Unsubscribe
                    </a>
                @elsecan(App\Policies\ThreadPolicy::SUBSCRIBE, $thread)
                    <a class="label" href="{{ route('threads.subscribe', $thread->slug()) }}">
                        Subscribe
                    </a>
                @endcan

                @can(App\Policies\ThreadPolicy::DELETE, $thread)
                    <a class="label label-danger" @click.prevent="activeModal = 'deleteThread'">
                        Delete
                    </a>

                    @include('_partials._delete_modal', [
                        'identifier' => 'deleteThread',
                        'route' => 'threads.delete',
                        'routeParams' => $thread->slug(),
                        'title' => 'Delete Thread',
                        'body' => '<p>Are you sure you want to delete this thread and its replies? This cannot be undone.</p>',
                    ])
                @endcan
                </div>

                <div class="w-full">
                    @include('layouts._ads._forum_sidebar')
                </div>
            </div>
        </div>
    </div>
@endsection
