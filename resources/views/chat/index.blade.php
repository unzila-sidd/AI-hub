@extends('layouts.app')

@section('content')

<div class="container-fluid">

<div class="row chat-wrapper">


<!-- Sidebar -->

<div class="col-md-3 sidebar">

    <button
    class="btn btn-primary w-100 mb-3"
    id="newChat">
        + New Chat
    </button>


    <div id="conversationList">

    @foreach($conversations as $conversation)

        <div
        class="conversation"
        data-id="{{ $conversation->id }}">

            {{ $conversation->title }}

        </div>

    @endforeach


    </div>


</div>





<!-- Chat Area -->

<div class="col-md-9 chat-area">


<div
id="messages"
class="messages">


</div>



<div class="input-area">


<textarea
id="message"
class="form-control"
placeholder="Ask anything..."
rows="2"></textarea>


<div class="mt-2">


<button
class="btn btn-success"
id="send">

Send
</button>


<button
class="btn btn-warning"
id="voice">

🎤 Voice
</button>


</div>


</div>


</div>



</div>

</div>



@endsection



@section('scripts')

<script src="{{ asset('js/chat.js') }}"></script>

@endsection
