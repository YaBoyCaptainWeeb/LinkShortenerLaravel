<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LinkRedirectController
{
   public function __invoke(Request $request, string $code): RedirectResponse|View
   {
        $link = Link::where('code', $code)->firstOrFail();

        Log::info('Link access', [
            'code' => $code,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'url' => $request->url(),
            'is_bot' => $this->isSocialMediaBot($request->userAgent())
        ]);

        if ($this->isSocialMediaBot($request->userAgent()))
        {
            return view('components.preview.link', compact('link'));
        }

        $this->recordClick($link, $request);

        return redirect()->away($link->url);
   }

   public function recordClick(Link $link, Request $request): void
   {
       LinkClick::create([
           'link_id' => $link->id,
           'ip_address' => $request->ip(),
           'user_agent' => $request->userAgent(),
       ]);

       $link->incrementClicks();
   }

    private function isSocialMediaBot(?string $userAgent): bool
    {
        if (!$userAgent)
        {
            return false;
        }

        $bots = [
            'facebookexternalhit',     // Facebook
            'Twitterbot',              // Twitter/X
            'LinkedInBot',             // LinkedIn
            'WhatsApp',                // WhatsApp
            'TelegramBot',             // Telegram
            'Slackbot',                // Slack
            'Discordbot',              // Discord
            'VKShare',
            'VKRobot',
            'Google-Structured-Data-Testing-Tool'  // Google
        ];

        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }
}
