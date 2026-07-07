<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        return view('pages.feedback.list', [
            'feedbacks' => Feedback::latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('pages.feedback.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return redirect()->route('feedback.success');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Feedback::create($validated);

        return redirect()->route('feedback.success')->with('toast', ['type' => 'success', 'message' => 'Feedback wurde erfolgreich gesendet']);
    }

    public function success(): View
    {
        return view('pages.feedback.success');
    }
}
