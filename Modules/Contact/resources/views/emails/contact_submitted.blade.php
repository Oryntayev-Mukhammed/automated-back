<p>You have a new contact submission:</p>
<ul>
    <li><strong>Name:</strong> {{ $submission->first_name }} {{ $submission->last_name }}</li>
    <li><strong>Email:</strong> {{ $submission->email }}</li>
    <li><strong>Specialist:</strong> {{ $submission->specialist }}</li>
    <li><strong>Date:</strong> {{ $submission->date }}</li>
    <li><strong>Time:</strong> {{ $submission->time }}</li>
    <li><strong>Locale:</strong> {{ $submission->locale }}</li>
</ul>
@if($submission->message)
    <p><strong>Message:</strong></p>
    <p>{{ $submission->message }}</p>
@endif
