<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <h5 class="mb-3">Users</h5>
                <ul class="list-group">
                    <!-- Loop through users -->
                    @foreach ($users as $user)
                        <li class="list-group-item">
                            <a href="{{ route('chat.form', $user->id) }}"
                                class="text-decoration-none">{{ $user->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Chat box -->
            <div class="col-md-9">
                <h3 class="text-center mb-4">Chat with {{ $receiver->name }}</h3>
                <form id="messageForm" action="{{ route('chat.send', $receiver->id) }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-body" style="height: 300px; overflow-y: auto;">
                            <ul class="list-unstyled" id="messagesList">
                                @foreach ($messages as $message)
                                    <li class="mb-2">
                                        <div
                                            class="d-flex {{ $message->sender_id == auth()->user()->id ? 'justify-content-end' : 'justify-content-start' }}">
                                            <label for=""
                                                class="{{ $message->sender_id == auth()->user()->id ? 'text-end' : 'text-start' }}">{{ $message->sender->name }}</label>
                                            <div
                                                class="p-2 {{ $message->sender_id == auth()->user()->id ? 'bg-primary text-white' : 'bg-light' }} rounded">
                                                {{ $message->message }}
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer">
                            <div class="input-group">
                                <input type="text" name="message" class="form-control"
                                    placeholder="Type a message..." required>
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and filles -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Pusher
            var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true
            });

            // Subscribe to the private channel
            var channel = pusher.subscribe('private-chat.{{ $receiver->id }}');
            channel.bind('chatMessage', function(data) {
                console.log(data); // Debugging: check the data

                // Append new message to the chat box
                $('#messagesList').append(`
                    <li class="mb-2">
                        <div class="d-flex ${data.message.sender_id == {{ auth()->user()->id }} ? 'justify-content-end' : 'justify-content-start'}">
                            <label for="" class="${data.message.sender_id == {{ auth()->user()->id }} ? 'text-end' : 'text-start'}">${data.message.sender_id == {{ auth()->user()->id }} ? 'You' : '{{ $receiver->name }}'}</label>
                            <div class="p-2 ${data.message.sender_id == {{ auth()->user()->id }} ? 'bg-primary text-white' : 'bg-light'} rounded">${data.message.message}</div>
                        </div>
                    </li>
                `);

                // Scroll to the bottom of the chat box
                $('.card-body').scrollTop($('.card-body')[0].scrollHeight);
            });

            // Fetch messages every few seconds
            setInterval(function() {
                $.ajax({
                    url: '{{ route('chat.fetch', $receiver->id) }}',
                    type: 'GET',
                    success: function(response) {
                        $('#messagesList').empty();
                        response.messages.forEach(function(message) {
                            $('#messagesList').append(`
                                <li class="mb-2">
                                    <div class="d-flex ${message.sender_id == {{ auth()->user()->id }} ? 'justify-content-end' : 'justify-content-start'}">
                                        <label for="" class="${message.sender_id == {{ auth()->user()->id }} ? 'text-end' : 'text-start'}">${message.sender.name}</label>
                                        <div class="p-2 ${message.sender_id == {{ auth()->user()->id }} ? 'bg-primary text-white' : 'bg-light'} rounded">${message.message}</div>
                                    </div>
                                </li>
                            `);
                        });
                        $('.card-body').scrollTop($('.card-body')[0].scrollHeight);
                    },
                    error: function(response) {
                        console.log('Error:', response);
                    }
                });
            }, 2000); // Fetch messages every 2 seconds

            // Handle message form submission
            $('#messageForm').submit(function(e) {
                e.preventDefault();
                var message = $('input[name=message]').val();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: {
                        message: message,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('input[name=message]').val(''); // Clear input field
                    },
                    error: function(response) {
                        console.log('Error:', response);
                    }
                });
            });
        });
    </script>
</body>

</html>
