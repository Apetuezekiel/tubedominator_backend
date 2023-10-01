<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\SavedIdea;
use App\Models\userSavedKeyword;
use App\Models\draftPost;
use App\Models\bookmarkedSearchTerm;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class KeywordsController extends Controller
{
    // public function fetchKeywordStat2 (Request $request){
    //     $this->validate($request, [
    //         'keywords' => 'required|string',
    //     ]);

    //     $engine = 'google';
    //     $country = 'us';
    //     $seeds = $request->keywords;
    //     $limit = '3';
    //     $type = 'broad';
    //     $app_id = '54e90ca4';
    //     $app_key = '443e99a9a8c02058f58a8352bbb19d5d';

    //     $url = 'https://api.lc.wordtracker.com/v3/search?engine=google&country=us&seeds=web%20development&limit=3&type=broad&app_id=54e90ca4&app_key=443e99a9a8c02058f58a8352bbb19d5d';
    //     $url2 = "https://api.lc.wordtracker.com/v3/search?engine=$engine&country=$country&seeds=$seeds&limit=$limit&type=$type&app_id=$app_id&app_key=$app_key";

    //     $client = new Client();

    //     try {
    //         $response = $client->get($url2);
    //         $statusCode = $response->getStatusCode();
    //         $body = $response->getBody()->getContents();

    //         return new Response([
    //             'response' => json_decode($body)
    //         ], $statusCode);
    //     } catch (\Exception $e) {
    //         // Handle any errors here
    //         return new Response(['error' => 'Failed to retrieve data from the API'], 500);
    //     }
    // }

    public function getMySearchTerm(Request $request) {
        $this->validate($request, [
            'keyword' => 'required|string',
        ]);
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        $client = new Client();
    
        $response = $client->request('GET', "https://zylalabs.com/api/2180/keyword+youtube+api/2000/get+keywords?keyword=$request->keyword", [
            'headers' => [
                'Authorization' => "Bearer " . env("ZYLA_APIKEY"),
            ],
        ]);
    
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);
    
        // Combine exact and related keywords
        $combinedKeywords = array_merge($responseData->exact_keyword, $responseData->related_keywords);
    
        return new Response([
            'response' => $combinedKeywords
        ], $statusCode);
    }
    
    public function fetchKeywordStatGoogle(Request $request){
        $this->validate($request, [
            'keyword' => 'required|string',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
        
        
        $client = new Client();

        $response = $client->request('GET', "https://zylalabs.com/api/1951/keyword+traffic+and+cpc+api/1689/get+keyword?keyword=$request->keyword", [
            'headers' => [
                'Authorization' => "Bearer 2250|wA32a202HzgEikjO23z3FPFLPlJCFKNpwwS9qsGG",
            ],
        ]);
        
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);

    
        // Loop through the exact_keyword array and calculate Estimated Potential Views
        // foreach ($responseData->exact_keyword as $keyword) {
        //     $estimatedViews = $this->calculateEstimatedViews($keyword->monthlysearch, $keyword->competition_score, $keyword->overallscore);
        //     $keyword->estimated_views = $estimatedViews;
        // }
    
        // // Loop through the related_keywords array and calculate Estimated Potential Views
        // foreach ($responseData->related_keywords as &$relatedKeyword) {
        //     $estimatedViews = $this->calculateEstimatedViews($relatedKeyword->monthlysearch, $relatedKeyword->competition_score, $relatedKeyword->overallscore);
        //     $relatedKeyword->estimated_views = $estimatedViews;
        // }
    
        return new Response([
            'response' => $responseData
        ], $statusCode);
    }

    public function fetchKeywordStat(Request $request){
        $this->validate($request, [
            'keyword' => 'required|string',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
        
        
        $client = new Client();
    
        // $response = $client->request('GET', "https://keyword-research-for-youtube.p.rapidapi.com/yttags.php?keyword=$request->keyword", [
        //     'headers' => [
        //         'X-RapidAPI-Host' => env("RapidApiYoutubeKwHOST"),
        //         'X-RapidAPI-Key' => env("RapidApiKey"),
        //     ],
        // ]);
        $response = $client->request('GET', "https://zylalabs.com/api/2180/keyword+youtube+api/2000/get+keywords?keyword=$request->keyword", [
            'headers' => [
                'Authorization' => "Bearer " . env("ZYLA_APIKEY"),
            ],
        ]);
        
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);

    
        // Loop through the exact_keyword array and calculate Estimated Potential Views
        foreach ($responseData->exact_keyword as $keyword) {
            $estimatedViews = $this->calculateEstimatedViews($keyword->monthlysearch, $keyword->competition_score, $keyword->overallscore);
            $keyword->estimated_views = $estimatedViews;
        }
    
        // Loop through the related_keywords array and calculate Estimated Potential Views
        foreach ($responseData->related_keywords as &$relatedKeyword) {
            $estimatedViews = $this->calculateEstimatedViews($relatedKeyword->monthlysearch, $relatedKeyword->competition_score, $relatedKeyword->overallscore);
            $relatedKeyword->estimated_views = $estimatedViews;
        }
    
        return new Response([
            'response' => $responseData
        ], $statusCode);
    }
    
    private function calculateEstimatedViews($monthlySearch, $competitionScore, $overallScore) {

        // Calculate Potential Click-Through Rate (CTR)
        if ($competitionScore >= 0 && $competitionScore <= 30) {
            $ctr = 0.1; // 10%
        } elseif ($competitionScore > 30 && $competitionScore <= 70) {
            $ctr = 0.05; // 5%
        } else {
            $ctr = 0.02; // 2%
        }

        // Estimate Potential Clicks
        $potentialClicks = $monthlySearch * $ctr;

        // Estimate Video View Percentage
        $viewPercentage = 0.2; // 20%
        $videoViews = $potentialClicks * $viewPercentage;

        // Adjust for Likelihood of Watching Entire Video
        $watchPercentage = 0.4; // 40%
        $estimatedViews = $videoViews * $watchPercentage;

        // Adjust for Overall Score
        $overallFactor = $overallScore / 100;
        $finalEstimatedViews = $estimatedViews * $overallFactor;

        // Output the Estimate
        // echo "Estimated Potential Views on YouTube: " . number_format($finalEstimatedViews);
    
        return $finalEstimatedViews;
    }

    public function addToSavedIdeas(Request $request) {
        $this->validate($request, [
            'video_ideas' => 'required',
            'search_volume' => 'required',
            'keyword_diff' => 'required',
            'potential_views' => 'required',
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $email = $request->email;
        $newVideoIdea = $request->video_ideas;
        $fetchSavedIdea = SavedIdea::where('video_ideas', $newVideoIdea)->first();

        if (!$fetchSavedIdea) { 
            $savedIdea = new SavedIdea(); 
            $savedIdea->user_id = $user_id;
            $savedIdea->email = $email;
            $savedIdea->video_ideas = $request->video_ideas;
            $savedIdea->search_volume = $request->search_volume;
            $savedIdea->keyword_diff = $request->keyword_diff;
            $savedIdea->potential_views = $request->potential_views;
            $savedIdea->save();
            // $fetchSavedIdea->search_volume = $request->search_volume;
        } else {
            $fetchSavedIdea->keyword_diff = $request->keyword_diff;
            $fetchSavedIdea->potential_views = $request->potential_views;
            $fetchSavedIdea->save();
        }


        return new Response(['success' => true, 'message' => 'Idea Saved'], 200);
    }

    public function getAllSavedIdeas(Request $request) {
        $this->validate($request, [
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }   
    
        $email = $request->email;
        // return $user_id;
    
        try {
            $savedIdeas = SavedIdea::where('user_id', $user_id)
            ->where('email', $email)
            ->orderBy('updated_at', 'desc')
            ->get();

            if ($savedIdeas->isEmpty()) {
                return response()->json(['message' => 'No saved ideas found'], 200);
            }
    
            return response()->json(['success' => true, 'data' => $savedIdeas], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve saved ideas'], 500);
        }
    }

    public function tryy (Request $request) {
                try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        return $user_id;
    }

    // public function getAllSavedIdeas(Request $request) {

    //     try {
    //         $user_id = $this->grabUserFromToken($request);
    //     } catch (\Exception $e) {
        
    //         if ($e->getMessage() === 'Expired token') {
    //             return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
    //         } else {
    //             return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
    //         }
    //     }

    //     return $user_id;

    //     $email = $request->email;

    //     $savedIdeas = SavedIdea::where('email', $email);
    //     return response()->json($savedIdeas, 200);
    // }
    
    public function deleteSavedIdea(Request $request, $id) {
        $request->validate([
            'email' => 'required|email',
        ]);
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        $email = $request->email;

        try {
            $savedIdea = SavedIdea::findOrFail($id);
    
            if ($savedIdea->user_id === $user_id && $savedIdea->email === $email) {
                $savedIdea->delete();
                return response()->json(['success' => false, 'message' => 'Idea deleted successfully'], 200);
            } else {
                return response()->json(['success' => false, 'error' => 'Unauthorized to delete this idea'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to delete idea'], 500);
        }
    }
    
    public function saveUserKeyword(Request $request) {
        $this->validate($request, [
            'keyword' => 'required',
            'search_volume' => 'required',
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $existingUserKeyword = userSavedKeyword::where('keyword', $request->keyword)->where('user_id', $user_id)->first();
        if ($existingUserKeyword) {
            $existingUserKeyword->search_volume = $request->search_volume;
            $existingUserKeyword->save();
        return new Response(['success' => true, 'message' => 'updated user keyword'], 200);

        }

        $email = $request->email;

        $userKeyword = new userSavedKeyword();
        $userKeyword->keyword = $request->keyword;
        $userKeyword->email = $email;
        $userKeyword->user_id = $user_id;
        $userKeyword->search_volume = $request->search_volume;
        $userKeyword->save();

        return new Response(['success' => true, 'message' => 'Saved user Keyword'], 200);
    }

    public function getUserKeyword(Request $request) {
        $this->validate($request, [
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $email = $request->email;

        $userKeywords = userSavedKeyword::where('user_id', $user_id)
        ->where('email', $email)
        ->select('keyword', 'email', 'search_volume', 'created_at')
        ->get();

        if ($userKeywords->isEmpty()) {
            return new Response(['success' => "trueNut", 'data' => 'No saved keywords'], 200);
        }

        $formattedUserKeywords = $userKeywords->map(function ($keyword) {
            $keyword->created_at_formatted = date('M j, Y', strtotime($keyword->created_at));
            return $keyword;
        });
    
        return new Response(['success' => true, 'data' => $formattedUserKeywords], 200);

    }

    private function grabUserFromToken($request){
        $key = env('JWT_SECRET');
        $token = explode(" ", $request->header("authorization"))[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $decodedArr = json_decode(json_encode($decoded), true);

        $user_id = $decodedArr['user_id'];
        
        return $user_id;
    }

    // SEARCH TERMS
    public function bookmarkSearchTerm(Request $request) {
        $this->validate($request, [
            'keyword' => 'required',
            'search_volume' => 'required',
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $existingUserKeyword = bookmarkedSearchTerm::where('keyword', $request->keyword)->where('user_id', $user_id)->first();
        if ($existingUserKeyword) {
            $existingUserKeyword->search_volume = $request->search_volume;
            $existingUserKeyword->save();
        return new Response(['success' => true, 'message' => 'updated user bookmarked search term'], 200);

        }

        $email = $request->email;

        $userKeyword = new bookmarkedSearchTerm();
        $userKeyword->keyword = $request->keyword;
        $userKeyword->email = $email;

        $userKeyword->user_id = $user_id;
        $userKeyword->search_volume = $request->search_volume;
        $userKeyword->save();

        return new Response(['success' => true, 'message' => 'Bookmarked User Search Term'], 200);
    }

    public function allBookmarkSearchTerms(Request $request) {
        $this->validate($request, [
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $email = $request->email;

        $userSearchTerms = bookmarkedSearchTerm::where('user_id', $user_id)
        ->where('email', $email)
        ->select('keyword', 'email', 'search_volume', 'created_at')
        ->get();

        if ($userSearchTerms->isEmpty()) {
            return new Response(['success' => "trueNut", 'data' => 'No bookmarked search terms'], 200);
        }

        $formattedUserSearchTerms = $userSearchTerms->map(function ($searchTerm) {
            $searchTerm->created_at_formatted = date('M j, Y', strtotime($searchTerm->created_at));
            return $searchTerm;
        });
    
        return new Response(
            ['success' => true, 'data' => $formattedUserSearchTerms
        ], 200);

    }

    public function deleteSavedIdeaBookmarkSearchTerm(Request $request) {
        // Validate the request
        $request->validate([
            'keyword' => 'required|string',
        ]);
    
        // Get the user ID from the token
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return response()->json(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return response()->json(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        // Get the keyword from the request
        $keyword = $request->keyword;
    
        // Find the search term by user ID and keyword
        $searchTerm = bookmarkedSearchTerm::where('user_id', $user_id)->where('keyword', $keyword)->first();
    
        if (!$searchTerm) {
            // If the search term does not exist
            return response()->json(['success' => false, 'error' => 'Search Term not found'], 404);
        }
    
        // Check if the user is authorized to delete this search term
        if ($searchTerm->user_id === $user_id && $searchTerm->keyword === $keyword) {
            // Delete the search term
            $searchTerm->delete();
            return response()->json(['success' => true, 'message' => 'Search Term deleted successfully'], 200);
        } else {
            return response()->json(['success' => false, 'error' => 'Unauthorized to delete this Search Term'], 401);
        }
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            // Store the uploaded file in a temporary location (e.g., 'storage/app/temp').
            $path = $uploadedFile->store('temp');

            // You can generate a public URL for the stored file using Laravel's asset helper.
            $imageUrl = asset('storage/' . $path);

            // Save the $imageUrl to your database or return it as a response.
            // Implement your database logic here.

            return response()->json(['imageUrl' => $imageUrl]);
        }

        return response()->json(['error' => 'No file uploaded.'], 400);
    }
    
    // DRAFT POSTS
    public function saveDraftPost(Request $request){
        $this->validate($request, [
            'video_id' => 'nullable',
            'search_term' => 'nullable',
            'video_title' => 'nullable',
            'video_description' => 'nullable',
            'video_tags' => 'nullable',
            // 'video_thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // return 1234;
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }


        $thumbnailUrl;
        $video_id = $request->video_id;
        $search_term = $request->search_term;
        $video_title = $request->video_title;
        $video_description = $request->video_description;
        $video_tags = $request->video_tags;
        $video_thumbnail = $request->file('video_thumbnail'); // Retrieve the uploaded file.
    
        $existingUserDraft = draftPost::where('video_id', $request->video_id)->where('user_id', $user_id)->first();
        
        if ($existingUserDraft) {
            // Update the existing draft if it exists.
            $existingUserDraft->video_id = $video_id;
            $existingUserDraft->search_term = $search_term;
            $existingUserDraft->video_title = $video_title;
            $existingUserDraft->video_description = $video_description; 
            $existingUserDraft->video_tags = $video_tags;
    
            // Handle the thumbnail image if it was uploaded.
            if ($video_thumbnail) {
                // Store the uploaded thumbnail in a temporary location.
                $path = $video_thumbnail->store('temp');
                // Generate a public URL for the stored thumbnail.
                $thumbnailUrl = 'https://tubedominator.com/storage/thumbnails/' . $path;
                $existingUserDraft->video_thumbnail = $thumbnailUrl;
            }
    
            $existingUserDraft->save();

            if ($video_thumbnail){
                return new Response(['success' => true, 'thumbNail' => $thumbnailUrl, 'message' => 'Updated User draft'], 200);
            }
    
            return new Response(['success' => true, 'message' => 'Updated user post draft'], 200);
        }
    
        // Create a new draft if it doesn't exist.
        $userDraft = new draftPost();
        $userDraft->user_id = $user_id;
        $userDraft->video_id = $video_id;
        $userDraft->search_term = $search_term;
        $userDraft->video_title = $video_title;
        $userDraft->video_description = $video_description;
        $userDraft->video_tags = $video_tags;
    
        // Handle the thumbnail image if it was uploaded.
        if ($video_thumbnail) {
            // Store the uploaded thumbnail in a temporary location.
            $path = $video_thumbnail->store('temp');
    
            // Generate a public URL for the stored thumbnail.
            $thumbnailUrl = 'https://tubedominator.com/storage/thumbnails/' . $path;
            $userDraft->video_thumbnail = $thumbnailUrl;
        }
    
        $userDraft->save();
    
        if ($video_thumbnail){
            return new Response(['success' => true, 'thumbNail' => $thumbnailUrl, 'message' => 'Saved User draft'], 200);
        }

        return new Response(['success' => true, 'message' => 'Saved User draft'], 200);
    }

    public function getDraftPost(Request $request) {
        $this->validate($request, [
            'email' => 'required',
        ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $email = $request->email;

        $userSearchTerms = bookmarkedSearchTerm::where('user_id', $user_id)
        ->where('email', $email)
        ->select('keyword', 'email', 'search_volume', 'created_at')
        ->get();

        if ($userSearchTerms->isEmpty()) {
            return new Response(['success' => "trueNut", 'data' => 'No bookmarked search terms'], 200);
        }

        $formattedUserSearchTerms = $userSearchTerms->map(function ($searchTerm) {
            $searchTerm->created_at_formatted = date('M j, Y', strtotime($searchTerm->created_at));
            return $searchTerm;
        });
    
        return new Response(
            ['success' => true, 'data' => $formattedUserSearchTerms
        ], 200);

    }

    public function deleteDraftPost(Request $request) {
        // Validate the request
        $request->validate([
            'keyword' => 'required|string',
        ]);
    
        // Get the user ID from the token
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return response()->json(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return response()->json(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        // Get the keyword from the request
        $keyword = $request->keyword;
    
        // Find the search term by user ID and keyword
        $searchTerm = bookmarkedSearchTerm::where('user_id', $user_id)->where('keyword', $keyword)->first();
    
        if (!$searchTerm) {
            // If the search term does not exist
            return response()->json(['success' => false, 'error' => 'Search Term not found'], 404);
        }
    
        // Check if the user is authorized to delete this search term
        if ($searchTerm->user_id === $user_id && $searchTerm->keyword === $keyword) {
            // Delete the search term
            $searchTerm->delete();
            return response()->json(['success' => true, 'message' => 'Search Term deleted successfully'], 200);
        } else {
            return response()->json(['success' => false, 'error' => 'Unauthorized to delete this Search Term'], 401);
        }
    }
}
