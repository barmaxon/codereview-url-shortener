<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClickResource;
use App\Models\Click;
use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClickController extends Controller
{
    public function click(Request $request, Link $link): RedirectResponse
    {
        /** @var Click $click */
        Click::query()->create([
            'link_id' => $link->getKey(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        $url = $link->long_url;
        if(!preg_match('/https?:\/\//i', $url)) {
            $url = "http://$url";
        }
        return redirect()->away($url);
    }

    public function list(Request $request): array
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $result = Click::query()
            ->selectRaw('user_agent, ip, COUNT(id) as count')
            ->whereBetween('datetime', [
                $request->get('from'),
                Carbon::parse($request->get('to'))->addDay()->toDateString()
            ])
            ->groupBy(['ip', 'user_agent'])
            ->get();

        return [
            'data' => [
                'total_views' => $result->sum('count'),
                'unique_views' => $result->count(),
            ]
        ];
    }

    public function show(Link $link): array
    {
        $result = Click::query()
            ->selectRaw("DATE(datetime) as date, user_agent, ip, COUNT(id) as count")
            ->where('link_id', $link->getKey())
            ->groupBy([DB::raw('DATE(datetime)'), 'ip', 'user_agent'])
            ->get();

        return [
            'data' => $result->groupBy('date')->map(fn(Collection $collection) => [
                'date' => $collection[0]['date'],
                'total_views' => $collection->sum('count'),
                'unique_views' => $collection->count(),
            ])->values()
        ];
    }
}
