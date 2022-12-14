<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Race;
use App\Models\Drivers;
use App\Models\Team;
use App\Models\Results;

Use Carbon\Carbon;
use Auth;

class AdminController extends Controller
{
    public function race_overview()
    {
        $races = Race::orderBy('id', 'desc')->get();
        return view('admin.race.overview', compact('races'));
    }

    public function race_create()
    {
        return view('admin.race.create');
    }

    public function race_store(Request $request)
    {
        Race::create([            
            'name' => $request->track,
            'start' => $request->start_time,
            'user_id' => Auth::id(),
            'active' => 0,
        ]);
        notify()->success('The race ' . $request->track . ' has been saved for ' . Carbon::parse($request->start_time)->format('H:i d/m/Y'));
        return redirect(route('admin.race_overview'));
    }

    public function race_show($id)
    {
        $race = Race::find($id);
        $results = Results::where('race_id', $id)->orderBy('position', 'asc')->get();
        return view('admin.race.show', compact('race', 'results'));
    }

    public function race_results($id)
    {
        $race = Race::find($id);
        $drivers = Drivers::orderBy('team', 'asc')->get();
        $results = Results::where('race_id', $id)->orderBy('position', 'asc')->get();
        return view('admin.race.results.overview', compact('race', 'drivers', 'results'));
    }

    public function insert_race_results(Request $request)
    {
        $checked = 0;
        $points = 0;

        if (isset($request->fastest_lap)) {
            $checked = 1;
        }

        if($request->position == 1) $points = 25;
        if($request->position == 2) $points = 18;
        if($request->position == 3) $points = 15;
        if($request->position == 4) $points = 12;
        if($request->position == 5) $points = 10;
        if($request->position == 6) $points = 8;
        if($request->position == 7) $points = 6;
        if($request->position == 8) $points = 4;
        if($request->position == 9) $points = 2;
        if($request->position == 10) $points = 1;

        if($request->position <= 10 && $checked == 1) $points++;

        $driver = Drivers::where('id', $request->driver)->get();

        $result = Results::create([
            'race_id' => $request->race_id,
            'driver_id' => $request->driver,
            'team_id' => $driver[0]->team,
            'position' => $request->position,
            'fastest_lap' => $checked,
            'points' => $points,
            'other' => $request->non_finish,
        ]);

        notify()->success('Result Sucessfully Saved');
        return redirect(route('admin.race_results', $request->race_id));
    }

    public function race_activate($id)
    {
        $race = Race::find($id);
        $race->active = 1;
        $race->save();

        notify()->success('Race Activated');
        return redirect(route('admin.race_results', $id));
    }

    public function race_complete($id)
    {
        $race = Race::find($id);
        $race->active = 2;
        $race->save();

        notify()->success('Race Completed');
        return redirect(route('admin.race_results', $id));
    }

    public function drivers_overview()
    {
        $drivers = Drivers::orderBy('team', 'asc')->get();
        return view('admin.drivers.overview', compact('drivers'));
    }

    public function drivers_create()
    {
        $teams = Team::all();
        $users = User::all();

        return view('admin.drivers.create', compact('teams', 'users'));
    }

    public function drivers_store(Request $request)
    {
        Drivers::create([            
            'name' => $request->name,
            'team' => $request->team,
            'user_id' => $request->user_id,
        ]);
        notify()->success($request->name . ' has been sucessfully added');
        return redirect(route('admin.drivers_overview'));
    }

    public function drivers_show($id)
    {
        $driver = Drivers::find($id);
        $teams = Team::all();

        return view('admin.drivers.show', compact('driver', 'teams'));
    }

    public function drivers_update(Request $request)
    {
        $driver = Drivers::find($request->driver);
        $driver->team = $request->team;
        $driver->save();
        
        return redirect(route('admin.drivers_overview'));
    }
}
