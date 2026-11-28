{{-- Plain-text email: {!! !!} is used on purpose so apostrophes and ampersands are not turned into HTML codes. --}}
{!! $replyBody !!}

--
Teyvat Travel Services
The Director's desk
{!! config('app.desk_email') !!}

------------------------------------------------------------
On {!! $inquiry->created_at->timezone('Asia/Manila')->format('F j, Y') !!}, {!! $inquiry->name !!} wrote:

{!! $quoted !!}
