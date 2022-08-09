<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Models\Admin\Driver;
use Illuminate\Support\Carbon;
use App\Transformers\Driver\DriverProfileTransformer;
use App\Http\Controllers\Api\V1\BaseController;
use App\Models\Admin\Fleet;
use Illuminate\Http\Request;
use App\Transformers\Driver\DriverTransformer;
use App\Transformers\Owner\FleetTransformer;

class FleetDriversController extends BaseController
{
    protected $driver;
    protected $fleet;


    public function __construct(Driver $driver,Fleet $fleet)
    {
        $this->driver = $driver;

        $this->fleet = $fleet;
    }


    /**
     * List Drivers For Assign Drivers
     * 
     * 
     * */
    public function listDrivers()
    {
        $owner_id = auth()->user()->owner->id;

        $drivers = Driver::where('owner_id',$owner_id)->get();

        $result = fractal($drivers, new DriverTransformer);
    
        return $this->respondOk($result);

    }

    /**
     * Add Driver
     * 
     * 
     * */
    public function addDriver(Request $request){

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile'=>'required|unique:users,mobile',
            'address'=>'required|min:10',
            'profile'=>'required'
        ]);

        $owner_detail = auth()->user()->owner;


        $profile_picture = null;

        if ($uploadedFile = $this->getValidatedUpload('profile', $request)) {
            $profile_picture = $this->imageUploader->file($uploadedFile)
                ->saveDriverProfilePicture();
        }

        $this->database->getReference('drivers/'.$driver->id)->set(['id'=>$driver->id,'vehicle_type'=>null,'active'=>1,'updated_at'=> Database::SERVER_TIMESTAMP]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'mobile' => $mobile,
            'mobile_confirmed' => true,
            'timezone'=>auth()->user()->timezone,
            'country'=>auth()->user()->country,
            'profile_picture'=>$profile_picture,
            'refferal_code'=>str_random(6),
        ]);

        $user->attachRole(Role::DRIVER);

        $created_params = $request->only(['name','mobile','email','address']);

        $created_params['service_location_id'] = $owner_detail->service_location_id;

        $driver = $user->driver()->create($created_params);

        return $this->respondSuccess(null,'driver_added_succesfully');

    }

    

}
