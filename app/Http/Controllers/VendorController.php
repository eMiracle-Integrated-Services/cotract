<?php


namespace App\Http\Controllers;


use App\Actions\FileAction;
use App\Models\Resource;
use App\Models\SafetyVault;
use App\Models\Vendor;
use App\Models\Wallet;
use App\Notifications\Vendor\VendorCreatedNotification;
use App\Traits\ValidationTrait;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = auth()->guard()->user();
    }

    public function becomeAVendor(Request $request)
    {
        ValidationTrait::call($request, [
            'vendor_package_id' => 'required|int|exists:vendor_packages,id',
            'business_name' => 'required|string|unique:vendors',
            'description' => 'required|string',
            'country' => 'nullable|int|exists:countries,id',
            'state' => 'nullable|int|exists:states,id',
            'city' => 'nullable|int|exists:cities,id',
            'address' => 'required|string',
            'week_start'=>'required|int|exists:weeks,id',
            'week_end'=>'required|int|exists:weeks,id',
            'website'=>'nullable|string|url',
            'facebook'=>'nullable|string|url',
            'instagram'=>'nullable|string|url',
            'file' => 'nullable|mimes:jpeg,jpg,png,gif,pdf',
        ]);

        $vendor = Vendor::create([
            'vendor_package_id' => $request->input('vendor_package_id'),
            'user_id'=>$this->user->id,
            'business_name'=>$request->input('business_name'),
            'description'=>$request->input('description'),
            'country_id'=>$request->input('country'),
            'state_id'=>$request->input('state'),
            'city_id'=>$request->input('city'),
            'address'=>$request->input('address'),
            'socials'=>json_encode([
                'website'=>$request->input('website'),
                'facebook'=>$request->input('facebook'),
                'instagram'=>$request->input('instagram'),
            ]),
        ]);

        //store the images
        if($request->file){
            //upload file
            foreach ($request->file('file') as $file)
            {
                $file = FileAction::upload($request);

                Resource::create([
                    'path'=>$file->path,
                    'resourceable_id'=>$vendor->id,
                    'resourceable_type'=>'App\Models\Vendor'
                ]);
            }
        }

        //create vendor wallet and safety_vault account
        //the safety vault is to hold money down so that the vendor can take up-front payment
        SafetyVault::create([
            'vaultable_id'=>$vendor->id,
            'vaultable_type'=>'App\Models\Vendor',
        ]);

        Wallet::create([
            'walletable_id'=>$vendor->id,
            'walletable_type'=>'App\Models\Vendor'
        ]);

        try {
            $this->user->notify(new VendorCreatedNotification());

        }catch (\Throwable $throwable){
            report($throwable);
        }
        return response([
            'status'=>true,
            'message'=>'Vendor account created',
            'data'=>[
                'vendor'=>$vendor
            ]
        ]);
    }

    public function editAVendor(Request $request)
    {
        ValidationTrait::call($request, [
            'vendor_id'=> 'required|int|exists:vendors,id',
            'business_name' => 'nullable|string|unique:vendors',
            'description' => 'nullable|string',
            'country' => 'nullable|int|exists:countries,id',
            'state' => 'nullable|int|exists:states,id',
            'city' => 'nullable|int|exists:cities,id',
            'address' => 'nullable|string',
            'website'=>'nullable|string|url',
            'facebook'=>'nullable|string|url',
            'instagram'=>'nullable|string|url'
        ]);
        $vendor = Vendor::find($request->input('vendor_id'));
        if ($this->user->can('edit', $vendor))
        {
            $vendor->business_name = $request->input('business_name', $vendor->business_name);
            $vendor->description = $request->input('description', $vendor->description);
            $vendor->country_id = $request->input('country', $vendor->country_id);
            $vendor->state_id = $request->input('state', $vendor->state_id);
            $vendor->city_id = $request->input('city', $vendor->city_id);
            $vendor->address = $request->input('address', $vendor->address);
            $socials = json_decode($request->socials);
            $vendor->socials = json_encode([
                'website'=>$request->input('website', $socials['website']),
                'facebook'=>$request->input('facebook', $socials['facebook']),
                'instagram'=>$request->input('instagram', $socials['instagram']),
            ]);
            $request->save();

            return response([
                'status'=>true,
                'message'=>'Vendor updated',
                'data'=>[
                    'vendor'=>$vendor
                ]
            ]);
        }
        return response([
            'status'=>false,
            'message'=>'Access denied',
            'data'=>[]
        ]);
    }
}
