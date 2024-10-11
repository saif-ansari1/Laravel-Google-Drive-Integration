<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use App\Models\TempUploadedImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GoogleDriveController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->addScope(Drive::DRIVE);
        $this->client->setAccessType('offline');
    }

    public function connect()
    {
        $user = auth()->user();
        // Clear the disconnect session variable if it exists
        session()->forget('google_drive_disconnected');

        if ($user->google_drive_token) {
            return redirect()->route('dashboard');
        }
        auth()->user()->google_drive_token = null;
        auth()->user()->google_drive_refresh_token = null;
        auth()->user()->save();
    
        // Set the prompt parameter to force sign-in
        $this->client->setPrompt('consent');
    
        $authUrl = $this->client->createAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $user = auth()->user();

        if (session('google_drive_disconnected')) {
            session()->forget('google_drive_disconnected');
            return redirect()->route('dashboard')->with('status', 'You have removed your Google Drive.');
        }
        $token = $this->client->fetchAccessTokenWithAuthCode($request->get('code'));
    
        if (!isset($token['error'])) {

            $this->client->setAccessToken($token);

            
            $user->google_drive_token = $token['access_token'];
            if (isset($token['refresh_token'])) {
                $user->google_drive_refresh_token = $token['refresh_token']; 
            } else {
                \Log::warning('Refresh token not provided. User ID: ' . $user->id);
                $user->google_drive_refresh_token = null; 
            }

            $expiresIn = $token['expires_in']; // seconds
            $user->token_expires_at = now()->addSeconds($expiresIn);
    
            $user->save();
    
            // Return a view with session token
            return view('dashboard', ['accessToken' => $token['access_token']]);
        } else {
            return redirect()->route('google.drive.connect')->withErrors('Failed to connect to Google Drive.');
        }
    }

    public function validateAccessToken(Request $request)
    {
        $accessToken = $request->get('access_token');
    
        $client = new \Google_Client();
        $client->setAccessToken($accessToken);
    
        try {
            $response = $client->verifyIdToken($accessToken);
            return response()->json(['valid' => true]);
        } catch (\Exception $e) {
            return response()->json(['valid' => false], 401);
        }
    }

    public function refreshToken()
    {
        $user = auth()->user();

        if ($user->google_drive_refresh_token) {
            
            $this->client->refreshToken($user->google_drive_refresh_token);
            $newAccessToken = $this->client->getAccessToken();

            
            $user->google_drive_token = $newAccessToken['access_token'];
            $user->save();

            return response()->json([
                'access_token' => $newAccessToken['access_token']
            ]);
        } else {
            return response()->json(['error' => 'No refresh token found'], 400);
        }
    }

    public function storeSelectedImages(Request $request)
    {
        $images = $request->input('images'); 

        // dd($images);

        foreach ($images as $image) {
            
            $fileContent = Http::get($image['url'])->body();
            // dd($fileContent);
            
            $fileName = $image['id'] . '-' . $image['name'];
            $filePath = 'uploads/google-drive/' . $fileName;
            
            
            Storage::disk('public')->put($filePath, $fileContent);

            
            TempUploadedImage::create([
                'user_id' => auth()->id(),
                'file_id' => $image['id'],
                'file_name' => $fileName,
                'file_url' => url(Storage::url($filePath)), 
            ]);
        }

        return response()->json(['message' => 'Images stored successfully']);
    }

    public function getRecentImages()
    {
        $userId = auth()->id();
        $recentImages = TempUploadedImage::where('user_id', $userId)->orderBy('created_at', 'desc')->take(5)->get();
        
        $imagesWithDimensions = $recentImages->map(function ($image) {
            // Assuming you store images in 'storage/app/public/uploads/google-drive/'
            $imagePath = storage_path('app/public/uploads/google-drive/' . $image->file_name);
    
            if (file_exists($imagePath)) {
                // Use Intervention Image to get the dimensions
                $imageSize = Image::read($imagePath);
                $image->width = $imageSize->width();
                $image->height = $imageSize->height();
            } else {
                // Fallback if the image file doesn't exist
                $image->width = 0;
                $image->height = 0;
            }
    
            return $image;
        });
        
        return response()->json($imagesWithDimensions);
    }

    public function removeGoogleDriveConnection()
    {
        $user = auth()->user();
        $user->google_drive_token = null;
        $user->google_drive_refresh_token = null;
        $user->save();

        session(['google_drive_disconnected' => true]);

        return redirect()->back()->with('status', 'Google Drive connection removed.');
    }

    // public function isAccessTokenExpired($user)
    // {
    //     return now()->greaterThan($user->token_expires_at);
    // }

    // public function useGoogleDriveApi()
    // {
    //     $user = auth()->user();

    //     if ($this->isAccessTokenExpired($user)) {
    //         dd('aa');
    //         // $newAccessToken = $this->refreshAccessToken($user);
    //         // if (!$newAccessToken) {
    //         //     return response()->json(['error' => 'Unable to refresh access token'], 401);
    //         // }
    //     }else{
    //         dd('ddd');
    //     }

    //     // Now you can use $user->google_drive_token for your API calls
    //     $accessToken = $user->google_drive_token;

    //     // Call your Google Drive API with the access token
    //     // ...
    // }

    public function downloadImage(Request $request)
    {
        $imageId = $request->input('imageId');
        $format = $request->input('format', 'jpg');
        $size = $request->input('size', 'original'); 
       
        $image = TempUploadedImage::findOrFail($imageId); 
    
        $imagePath = public_path('storage/uploads/google-drive/' . basename($image->file_url)); // Ensure correct path

        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image file not found.'], 404);
        }

        $manager = new ImageManager(new Driver());
        
        $img = $manager->read($imagePath);
        
        if ($size !== 'original') {
            switch ($size) {
                case 'tiny':
                    $img->resize(100,69); 
                    break;
                case 'small':
                    $img->resize(250,171); 
                    break;
                case 'medium':
                    $img->resize(500,341); 
                case 'large':
                    $img->resize(1000,681); 
                    break;
            }
        }

        $convertedFileName = 'converted_image_' . uniqid() . '.' . $format;
        $convertedPath = storage_path('app/public/temp/' . $convertedFileName); 

        switch ($format) {
            case 'png':
                $img->toPng()->save($convertedPath);
                break;
            case 'jpg':
            case 'jpeg': 
                $img->toJpg()->save($convertedPath);
                break;
            case 'webp':
                $img->toWebp()->save($convertedPath); 
                break;
            default:
                return response()->json(['error' => 'Unsupported format.'], 400);
        } 

        return response()->json([
            'file_url' => url('storage/temp/' . $convertedFileName), 
            'file_name' => 'image'. uniqid() . '.'   . $format
        ]);
    }
}
