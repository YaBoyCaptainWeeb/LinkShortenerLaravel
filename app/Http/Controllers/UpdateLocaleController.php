<?php

namespace App\Http\Controllers;

use App\Enums\AppLocale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateLocaleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, AppLocale $locale): RedirectResponse
    {
        $request->session()->put('locale', $locale->value);

        $user = $request->user();
        if ($user instanceof User)
        {
            $user->locale = $locale;
            $user->save();
        }
        return redirect()->back();
    }
}
