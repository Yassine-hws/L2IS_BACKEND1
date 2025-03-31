<?php
namespace App\Http\Controllers;

use App\Revue;
use App\Ouvrage;
use App\Project;
use App\Report;
use App\Brevet;
use App\Conference;
use App\Seminar;
use App\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Get the start_date and end_date from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // If no dates are provided, we consider the whole range (optional: you can set defaults)
        if (!$startDate) {
            $startDate = Carbon::now()->subYear(); // Default to 1 year ago if no start date is provided
        }
        if (!$endDate) {
            $endDate = Carbon::now(); // Default to today if no end date is provided
        }

        // Ensure that the dates are in a proper Carbon format (start of day and end of day)
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        // Count the number of revues within the date range
        $revuesCount = Revue::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de revues : " . $revuesCount);

        // Count the number of ouvrages within the date range
        $ouvragesCount = Ouvrage::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre d'ouvrages : " . $ouvragesCount);

        // Count the number of projets within the date range
        $projetsCount = Project::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de projets : " . $projetsCount);

        // Count the number of rapports within the date range
        $rapportsCount = Report::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de rapports : " . $rapportsCount);

        // Count the number of brevets within the date range
        $brevetsCount = Brevet::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de brevets : " . $brevetsCount);

        // Count the number of conferences within the date range
        $conferencesCount = Conference::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de conférences : " . $conferencesCount);

        // Count the number of séminaires within the date range
        $seminairesCount = Seminar::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de séminaires : " . $seminairesCount);

        // Count the number of members within the date range
        $membersCount = Member::whereBetween('created_at', [$startDate, $endDate])->count();
        \Log::info("Nombre de membres : " . $membersCount);

        // Return the statistics as a JSON response
        return response()->json([
            'revues' => $revuesCount,
            'ouvrages' => $ouvragesCount,
            'projets' => $projetsCount,
            'rapports' => $rapportsCount,
            'brevets' => $brevetsCount,
            'conferences' => $conferencesCount,
            'seminaires' => $seminairesCount,
            'members' => $membersCount,
        ]);
    }
}
