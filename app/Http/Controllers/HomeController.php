<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\User;
use Session;
use App\Notification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user_id = Auth::user()->id;

        $user = User::where('users.id', $user_id)
                ->leftjoin('geo_divisions', 'users.division', '=', 'geo_divisions.id')
                ->leftjoin('geo_districts', 'users.district', '=', 'geo_districts.id')
                ->leftjoin('geo_upazilas', 'users.upazila', '=', 'geo_upazilas.id')
                ->leftjoin('blood_groups', 'users.blood_group', '=', 'blood_groups.id')
                ->first();
        
                // dd($user->toArray());
                
        $notifications = User::where('users.id', $user_id)
                ->leftjoin('notifications', 'users.id', '=', 'notifications.donor_id')
                ->get();

        // $donors = Notification::where('booker_id', $user_id)
        //         ->join('users', 'users.id', '=', 'notifications.donor_id')
        //         ->get();
                
        $donors = Notification::where('booker_id', $user_id)
                ->join('users', 'users.id', '=', 'notifications.donor_id')
                ->get();

                // dd($notifications->toArray());

        // dd($notifications->toArray());

        return view('user.profile', compact('user', 'notifications', 'donors'));
    }

    public function editProfile($id)
    {

        $user = User::find($id);
        // dd($user->toArray());

        $blood_groups = DB::table('blood_groups')->get();
        // dd($blood_groups->toArray());

        $divisions = DB::table('geo_divisions')->get();
        $districts = DB::table('geo_districts')->where('geo_division_id', '=', $user->division)->get();
        $upazilas  = DB::table('geo_upazilas')->where('geo_district_id', '=', $user->district)->get();

        return view('user.editProfile', compact('user', 'blood_groups', 'divisions', 'districts', 'upazilas'));
    }

    public function updateProfile(Request $request, $id)
    {

        $validation = $request->validate([
            'name' => 'required',
            'email' => 'required',
            // 'mobile_no' => 'required|numeric|unique:mobile_no|digits:11',
            'blood_group' => 'required',
            // 'image' => 'required',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
        ]);

        $user = User::find($id);
        // dd($user_id->toArray());

        $image_name = $request->hidden_image;
        $image = $request->file('image');
        
        if($image != '')
        { 
            if($user->image){
                // get previous image from folder
                $avatar = public_path("uploads/avatar/{$user->image}"); 
                // dd($avatar);

                // unlink or remove previous image from folder
                if (file_exists($avatar)) { 
                    unlink($avatar);
                }
            }
            
            $image_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('/uploads/avatar/'), $image_name);
        }


        $user->name = $request->name;
        $user->mobile_no = $request->mobile_no;
        $user->email = $request->email;
        $user->blood_group = $request->blood_group;
        $user->image = $image_name;
        $user->division = $request->division;
        $user->district = $request->district;
        $user->upazila = $request->upazila;

        // dd($user->toArray());

        $user->update();

        return redirect('profile');
    }


    public function blood_donated($id){

        $user_id = Auth::user()->id;


        $date = date("d/m/Y");
        echo 'Current Date: '. $date . '<br>';
        
        $date = strtotime(date("d/m/Y", strtotime($date)) . "+3 months");
        $date = date("d/m/Y", $date);
        echo 'Next 3 month Date: '. $date;

        return redirect()->back();

    }
}
