<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\LinkClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkRedirectController
{
   public function __invoke(Request $request, string $code): RedirectResponse
   {
        $link = Link::where('code', $code)->firstOrFail();

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
}
