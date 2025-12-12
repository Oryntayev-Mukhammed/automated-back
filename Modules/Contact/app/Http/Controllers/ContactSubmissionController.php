<?php

namespace Modules\Contact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Contact\Entities\ContactSubmission;
use Modules\Contact\Mail\ContactSubmittedMail;

class ContactSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'nullable|string|max:150',
            'last_name' => 'nullable|string|max:150',
            'email' => 'required|email',
            'specialist' => 'nullable|string|max:150',
            'date' => 'nullable|date',
            'time' => 'nullable|string|max:50',
            'message' => 'nullable|string|max:2000',
            'locale' => 'nullable|string|max:8',
        ]);

        $submission = ContactSubmission::create([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'specialist' => $data['specialist'] ?? null,
            'date' => $data['date'] ?? null,
            'time' => $data['time'] ?? null,
            'message' => $data['message'] ?? null,
            'locale' => $data['locale'] ?? 'en',
            'status' => 'new',
            'meta' => $request->except(['first_name','last_name','email','specialist','date','time','message','locale']),
        ]);

        $to = config('contact.notify_email', env('CONTACT_NOTIFY_EMAIL'));
        if ($to) {
            Mail::to($to)->send(new ContactSubmittedMail($submission));
        }

        return response()->json([
            'success' => true,
            'message' => 'Saved',
        ]);
    }
}
