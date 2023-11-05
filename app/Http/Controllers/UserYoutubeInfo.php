<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Response;
use App\Models\Registration;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class UserYoutubeInfo extends Controller {
    public function register(Request $request) {
        return 123;
        // Validate the input data
        // $validatedData = $request->validate([
        //     'channel_name' => 'required|string',
        //     'description' => 'required|string',
        //     'business_email' => 'required|email|unique:registrations',
        //     'accept_terms' => 'required|boolean',
        //     'channel_language' => 'required|string',
        //     'competitive_channels' => 'required|string',
        //     'keywords' => 'required|string',
        //     'password' => 'required|string|min:6',
        // ]);

        $validatedData = $request->validate([
            'fullname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:registrations',
            'password' => 'required|string|min:6',
        ]);

        // Create a new registration record
        $registration = new Registration($validatedData);
        $registration->password = Hash::make($validatedData['password']);
        $registration->save();

        return response()->json(['message' => 'Registration successful'], 201);
    }
    
    private function userExists($email, $user_id) {
        // Use the Eloquent model 'Registration' to query the database
        $channelUser = Registration::where("business_email", $email)
            ->where("user_id", $user_id)
            ->first();
    
        // If a row is found, return true; otherwise, return false
        return $channelUser ? true : false;
    }

    // public fun   ction fetchUserYoutubeInfo(){
    //     $apiKey = env("RapidApiKey");

    //     $externalApiUrl = 'https://youtube-v311.p.rapidapi.com/search/';
    //     $externalApiHeaders = [
    //         'X-RapidAPI-Key' => $apiKey,
    //         'X-RapidAPI-Host' => 'youtube-v311.p.rapidapi.com',
    //     ];

    //     $params = [
    //         'part' => 'snippet',
    //         'channelId' => 'UCIaJua9IU_Db15LKAaq_ZYw',
    //         'maxResults' => '5',
    //         'order' => 'relevance',
    //         'safeSearch' => 'moderate',
    //         'type' => 'video,channel,playlist',
    //     ];

    //     $response = Http::withHeaders($externalApiHeaders)->get($externalApiUrl, $params);
    //     $dataFromExternalApi = $response->json();

    //     return $dataFromExternalApi;


    //     // Extract video IDs
    //     $videoIds = [];
    //     foreach ($dataFromExternalApi['items'] as $item) {
    //         $videoIds[] = $item['id']['videoId'];
    //     }


    //     // Fetch video statistics using the video IDs
    //     $videoStatsApiUrl = 'https://youtube-v311.p.rapidapi.com/videos/';
    //     $videoStatsParams = [
    //         'part' => 'snippet,contentDetails,statistics',
    //         'id' => implode(',', $videoIds),
    //         'maxResults' => '5',
    //     ];

    //     $videoStatsResponse = Http::withHeaders($externalApiHeaders)->get($videoStatsApiUrl, $videoStatsParams);
    //     $videoStatsData = $videoStatsResponse->json();

    //     // Combine video details with their corresponding statistics
    //     $combinedData = [];
    //     foreach ($dataFromExternalApi['items'] as $item) {
    //         foreach ($videoStatsData['items'] as $videoStatsItem) {
    //             if ($videoStatsItem['id'] === $item['id']['videoId']) {
    //                 $combinedItem = [
    //                     'publishedAt' => $item['snippet']['publishedAt'],
    //                     'title' => $item['snippet']['title'],
    //                     'description' => $item['snippet']['description'],
    //                     'thumbnailUrl' => $item['snippet']['thumbnails']['high']['url'],
    //                     'channelTitle' => $item['snippet']['channelTitle'],
    //                     'videoId' => $item['id']['videoId'],
    //                     'viewCount' => $videoStatsItem['statistics']['viewCount'],
    //                 ];
    //                 $combinedData[] = $combinedItem;
    //                 break;
    //             }
    //         }
    //     }

    //     return response()->json($combinedData); // Return combined data as JSON
    // }

    public function getMySearchTermOld(Request $request) {    
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
        $response = $client->request('GET', "https://www.googleapis.com/customsearch/v1?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&cx=" . env('TUBEDOMINATOR_GOOGLE_CX') . "&q=$request->searchTerm", []);
    
        $jsonResponse = (string) $response->getBody();
        $dataArray = json_decode($jsonResponse, true);

        return $dataArray;
    }

    public function getMySearchTerm(Request $request) {     
        // try {
        //     $user_id = $this->grabUserFromToken($request);
        // } catch (\Exception $e) {
        //     if ($e->getMessage() === 'Expired token') {
        //         return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
        //     } else {
        //         return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
        //     }
        // }

        // $client = new Client();
        // $response = $client->request('GET', "https://api.google.com/keywordplanner/v1/?apiKey=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&searchTerm=$request->searchTerm", []);
    
        // $jsonResponse = (string) $response->getBody();
        // $dataArray = json_decode($jsonResponse, true);

        // return $dataArray;

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://keyword-traffic.p.rapidapi.com/?keywords%5B0%5D=internet%20marketing%20service&traffic_targets%5B0%5D=google&traffic_targets%5B1%5D=microsoft&match_type=phrase&language=en&language_criterion_id=1000&location=us&location_criterion_id=2840', [
            'headers' => [
                'X-RapidAPI-Host' => 'keyword-traffic.p.rapidapi.com',
                'X-RapidAPI-Key' => 'd3beb4b3dfmsh3407fbcc0da6bc9p1b5fb8jsn9915cf83e826',
            ],
        ]);

        echo $response->getBody();
    }

    public function getMyChannels(Request $request) {    
        $gToken = $request->header("gToken");

        $client = new Client();
    
        $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails,snippet,statistics&mine=true", [
            'headers' => [
                'Authorization' => "Bearer $gToken",
            ],
        ]);
    
        $jsonResponse = (string) $response->getBody();
        $dataArray = json_decode($jsonResponse, true);
    
        $channelInfoList = [];
    
        foreach ($dataArray['items'] as $item) {
            $channelInfo = [
                'channelId' => $item['id'],
                'channelTitle' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnailUrl' => $item['snippet']['thumbnails']['high']['url'],
                'publishedAt' => $item['snippet']['publishedAt'],
                'viewCount' => $item['statistics']['viewCount'],
                'subscriberCount' => $item['statistics']['subscriberCount'],
                'videoCount' => $item['statistics']['videoCount'],
            ];
    
            $channelInfoList[] = $channelInfo;
        }
    
        $limitedChannelList = array_slice($channelInfoList, 0, 10);
        return $limitedChannelList;
    }

    public function fetchMyPlaylists(Request $request){
        // Validate the request parameters
        $this->validate($request, [
            'channel_id' => 'required',
        ]);

        // Get the Google token from the request header
        $gToken = $request->header('gToken');

        // try {
        //     // Attempt to retrieve the user ID from the token
        //     $user_id = $this->grabUserFromToken($request);
        // } catch (\Exception $e) {
        //     // Handle token expiration or invalid token errors
        //     if ($e->getMessage() === 'Expired token') {
        //         return response(['status' => 'Failed', 'message' => 'Expired token'], 401);
        //     } else {
        //         return response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
        //     }
        // }

        // Create an HTTP client instance
        $client = new Client();

        try {
            // Make a GET request to the YouTube API
            $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlists?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=snippet,id,status,player,contentDetails&order=date&maxResults=50", [
                'headers' => [
                    'Authorization' => $gToken,
                ],
            ]);

            // Decode the JSON response into an array
            $responseData = json_decode($response->getBody(), true);

            // Extract the required fields from each item in the "items" array
            $extractedData = collect($responseData['items'])->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'publishedAt' => $item['snippet']['publishedAt'],
                    'channelId' => $item['snippet']['channelId'],
                    'title' => $item['snippet']['title'],
                    'description' => $item['snippet']['description'],
                    'channelTitle' => $item['snippet']['channelTitle'],
                    'privacyStatus' => $item['status']['privacyStatus'],
                    'itemCount' => $item['contentDetails']['itemCount'],
                    'embedHtml' => $item['player']['embedHtml'],
                ];
            });

            // Return the extracted data as a JSON response
            return response()->json($extractedData);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the API request
            return response()->json(['error' => 'Unable to fetch data'], 500);
        }
    }

    public function fetchMyYoutubeChannels(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);

        $gToken = $request->header("gToken");

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
        $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/search?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=snippet%2Cid&order=date&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $responseData = json_decode($response->getBody(), true); // Decode response body
    
        // Initialize an array to store video IDs
        $videoIds = [];
    
        // Loop through the items and extract video IDs
        foreach ($responseData['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
    
        // Remove undefined values from the array
        $videoIds = array_filter($videoIds, function ($value) {
            return $value !== "";
        });
    
        // Convert video IDs array to a comma-separated string
        $videoIdsString = implode(',', $videoIds);
    
        // Make a new API call using the extracted video IDs to get video details
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=statistics,snippet,id,status,topicDetails,player,localizations,liveStreamingDetails&forContentOwner=true&maxResults=25&id=" . $videoIdsString, [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        // return $videoDetailsData;
    
        // Initialize an array to store processed video details
        $videoDetails = [];
    
        // Process video details similar to combinedData
        foreach ($videoDetailsData['items'] as $index => $item) {
            $videoDetailItem = [
                'publishedAt' => $item['snippet']['publishedAt'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnails' => $item['snippet']['thumbnails']['standard'],
                'categoryId' => $item['snippet']['categoryId'],
                'channelId' => $item['snippet']['channelId'],
                'channelTitle' => $item['snippet']['channelTitle'],
                // 'defaultAudioLanguage' => $item['snippet']['defaultAudioLanguage'],
                'liveBroadcastContent' => $item['snippet']['liveBroadcastContent'],
                'player' => $item['player']['embedHtml'],
                'videoId' => $videoIds[$index], // Use video ID from $videoIds array
                'madeForKids' => $item['status']['madeForKids'],
                'privacyStatus' => $item['status']['privacyStatus'],
                'uploadStatus' => $item['status']['uploadStatus'],
                'publicStatsViewable' => $item['status']['publicStatsViewable'],
                'topicCategories' => $item['topicDetails']['topicCategories'],
                // 'actualEndTime' => $item['liveStreamingDetails']['actualEndTime'],
                // 'actualStartTime' => $item['liveStreamingDetails']['actualStartTime'],
                // 'scheduledStartTime' => $item['liveStreamingDetails']['scheduledStartTime'],
                'viewCount' => $item['statistics']['viewCount'],
                'commentCount' => $item['statistics']['commentCount'],
                'likeCount' => $item['statistics']['likeCount'],
                'favoriteCount' => $item['statistics']['favoriteCount'],
            ];
    
            $videoDetails[] = $videoDetailItem;
        }
    
        // Now, $videoDetails contains the processed video details
    
        return response()->json($videoDetails); // Return the processed video details as JSON
    }

    public function fetchMyYoutubeVideo(Request $request){
        $this->validate($request, [
            'channel_id' => 'required', 
            'video_id' => 'required'
        ]);
    
        $gToken = $request->header("gToken");
    
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

    
        // Use videoIds to fetch video details
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=statistics,snippet,id,status,topicDetails,player,localizations,liveStreamingDetails&forContentOwner=true&maxResults=25&id=" . $request->video_id, [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
    
        $videoDetails = [];
    
        foreach ($videoDetailsData['items'] as $index => $item) {
            $channelId = isset($item['snippet']['channelId']) ? $item['snippet']['channelId'] : "";
            
            // Construct the channel link
            $channelLink = "https://www.youtube.com/channel/$channelId";
            $videoId = isset($item["id"]) ? $item["id"] : "";

                // Extract video ID from the player embed code

            // preg_match('/src="https:\/\/www\.youtube\.com\/embed\/([^"]+)"/', $item['player']['embedHtml'], $matches);
            // $videoId = isset($matches[1]) ? $matches[1] : "";

            // Construct the video link
            $videoLink = "https://www.youtube.com/watch?v=$videoId";
            
            $videoDetailItem = [
                'publishedAt' => isset($item['snippet']['publishedAt']) ? $item['snippet']['publishedAt'] : "",
                'title' => isset($item['snippet']['title']) ? $item['snippet']['title'] : "",
                'description' => isset($item['snippet']['description']) ? $item['snippet']['description'] : "",
                'thumbnails' => isset($item['snippet']['thumbnails']['standard']) ? $item['snippet']['thumbnails']['standard'] : "",
                'categoryId' => isset($item['snippet']['categoryId']) ? $item['snippet']['categoryId'] : "",
                'channelId' => $channelId,
                'channelTitle' => isset($item['snippet']['channelTitle']) ? $item['snippet']['channelTitle'] : "",
                'tags' => isset($item['snippet']['tags']) ? $item['snippet']['tags'] : "",
                'liveBroadcastContent' => isset($item['snippet']['liveBroadcastContent']) ? $item['snippet']['liveBroadcastContent'] : "",
                'player' => isset($item['player']['embedHtml']) ? $item['player']['embedHtml'] : "",
                'videoId' => $videoId,
                'madeForKids' => isset($item['status']['madeForKids']) ? $item['status']['madeForKids'] : "",
                'privacyStatus' => isset($item['status']['privacyStatus']) ? $item['status']['privacyStatus'] : "",
                'uploadStatus' => isset($item['status']['uploadStatus']) ? $item['status']['uploadStatus'] : "",
                'publicStatsViewable' => isset($item['status']['publicStatsViewable']) ? $item['status']['publicStatsViewable'] : "",
                'topicCategories' => isset($item['topicDetails']['topicCategories']) ? $item['topicDetails']['topicCategories'] : "",
                'viewCount' => isset($item['statistics']['viewCount']) ? $item['statistics']['viewCount'] : "",
                'commentCount' => isset($item['statistics']['commentCount']) ? $item['statistics']['commentCount'] : "",
                'likeCount' => isset($item['statistics']['likeCount']) ? $item['statistics']['likeCount'] : "",
                'favoriteCount' => isset($item[ 'statistics']['favoriteCount']) ? $item['statistics']['favoriteCount'] : "",
                'channelLink' => $channelLink,
                'videoLink' => $videoLink,
            ];
            $videoDetails[] = $videoDetailItem;
        }
        return response()->json($videoDetails);
    }

    public function fetchMyYoutubeVideos(Request $request){
        $this->validate($request, [
            'channel_id' => 'required', 
            'keyword' => 'required'
        ]);
    
        $gToken = $request->header("gToken");
    
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
    
        // Perform a search to get videoIds
        $searchResponse = $client->request('GET', "https://youtube.googleapis.com/youtube/v3/search?part=snippet&maxResults=3&order=date&key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&q=" . urlencode($request->keyword));
    
        $searchData = json_decode($searchResponse->getBody(), true);
    
        // Extract videoIds from search results
        $videoIds = [];
        foreach ($searchData['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
        
        // Slice the array to only 10 items
        $videoIds = array_slice($videoIds, 0, 5);
    
        // Use videoIds to fetch video details
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=statistics,snippet,id,status,topicDetails,player,localizations,liveStreamingDetails&forContentOwner=true&maxResults=25&id=" . implode(",", $videoIds), [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
    
        $videoDetails = [];
    
        foreach ($videoDetailsData['items'] as $index => $item) {
            $channelId = isset($item['snippet']['channelId']) ? $item['snippet']['channelId'] : "";
            $channelDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&id=" . $channelId . "&part=statistics,snippet,contentDetails,topicDetails,brandingSettings,localizations", [
                'headers' => [
                    'Authorization' => $gToken,
                ],
            ]);
    
            $channelDetailsData = json_decode($channelDetailsResponse->getBody(), true);
            $subscriberCount = $channelDetailsData['items'][0]['statistics']['subscriberCount'] ?? null;
            
            // Construct the channel link
            $channelLink = "https://www.youtube.com/channel/$channelId";
            $videoId = isset($item["id"]) ? $item["id"] : "";

            $videoLink = "https://www.youtube.com/watch?v=$videoId";


            
            $videoDetailItem = [
                'publishedAt' => isset($item['snippet']['publishedAt']) ? $item['snippet']['publishedAt'] : "",
                'title' => isset($item['snippet']['title']) ? $item['snippet']['title'] : "",
                'description' => isset($item['snippet']['description']) ? $item['snippet']['description'] : "",
                'thumbnails' => isset($item['snippet']['thumbnails']['standard']) ? $item['snippet']['thumbnails']['standard'] : "",
                'categoryId' => isset($item['snippet']['categoryId']) ? $item['snippet']['categoryId'] : "",
                'channelId' => $channelId,
                'channelTitle' => isset($item['snippet']['channelTitle']) ? $item['snippet']['channelTitle'] : "",
                'tags' => isset($item['snippet']['tags']) ? $item['snippet']['tags'] : "",
                'liveBroadcastContent' => isset($item['snippet']['liveBroadcastContent']) ? $item['snippet']['liveBroadcastContent'] : "",
                'player' => isset($item['player']['embedHtml']) ? $item['player']['embedHtml'] : "",
                'videoId' => $videoId,
                'madeForKids' => isset($item['status']['madeForKids']) ? $item['status']['madeForKids'] : "",
                'privacyStatus' => isset($item['status']['privacyStatus']) ? $item['status']['privacyStatus'] : "",
                'uploadStatus' => isset($item['status']['uploadStatus']) ? $item['status']['uploadStatus'] : "",
                'publicStatsViewable' => isset($item['status']['publicStatsViewable']) ? $item['status']['publicStatsViewable'] : "",
                'topicCategories' => isset($item['topicDetails']['topicCategories']) ? $item['topicDetails']['topicCategories'] : "",
                'viewCount' => isset($item['statistics']['viewCount']) ? $item['statistics']['viewCount'] : "",
                'commentCount' => isset($item['statistics']['commentCount']) ? $item['statistics']['commentCount'] : "",
                'likeCount' => isset($item['statistics']['likeCount']) ? $item['statistics']['likeCount'] : "",
                'favoriteCount' => isset($item[ 'statistics']['favoriteCount']) ? $item['statistics']['favoriteCount'] : "",
                'channelLink' => $channelLink,
                'videoLink' => $videoLink,
                'subscriberCount' => $subscriberCount,
            ];
            $videoDetails[] = $videoDetailItem;
        }
        return response()->json($videoDetails);
    }
    
    public function getAllVideosChatGPT(Request $request){
        // $this->validate($request, [
        //     'videoIds' => 'required',
        //     'channel_id' => 'required'
        // ]);

        $gToken = $request->header("gToken");

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
    
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlists?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=snippet&mine=true", [
            'headers' => [
                'Authorization' => "Bearer $gToken",
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        return $videoDetailsData;
        // Process video details similar to combinedData
        // foreach ($videoDetailsData['items'] as $index => $item) {
        //     $videoDetailItem = [
        //         'publishedAt' => $item['snippet']['publishedAt'],
        //         'title' => $item['snippet']['title'],
        //         'description' => $item['snippet']['description'],
        //         'thumbnails' => $item['snippet']['thumbnails']['standard'],
        //         'categoryId' => $item['snippet']['categoryId'],
        //         'channelId' => $item['snippet']['channelId'],
        //         'channelTitle' => $item['snippet']['channelTitle'],
        //         'tags' => $item['snippet']['tags'],
        //         // 'defaultAudioLanguage' => $item['snippet']['defaultAudioLanguage'],
        //         'liveBroadcastContent' => $item['snippet']['liveBroadcastContent'],
        //         'player' => $item['player']['embedHtml'],
        //         'videoId' => $item["id"], // Use video ID from $videoIds array
        //         'madeForKids' => $item['status']['madeForKids'],
        //         'privacyStatus' => $item['status']['privacyStatus'],
        //         'uploadStatus' => $item['status']['uploadStatus'],
        //         'publicStatsViewable' => $item['status']['publicStatsViewable'],
        //         'topicCategories' => $item['topicDetails']['topicCategories'],
        //         // 'actualEndTime' => $item['liveStreamingDetails']['actualEndTime'],
        //         // 'actualStartTime' => $item['liveStreamingDetails']['actualStartTime'],
        //         // 'scheduledStartTime' => $item['liveStreamingDetails']['scheduledStartTime'],
        //         'viewCount' => $item['statistics']['viewCount'],
        //         'commentCount' => $item['statistics']['commentCount'],
        //         'likeCount' => $item['statistics']['likeCount'],
        //         'favoriteCount' => $item['statistics']['favoriteCount'],
        //     ];
    
        //     $videoDetails[] = $videoDetailItem;
        // }

        $videoDetails = [];

        foreach ($videoDetailsData['items'] as $index => $item) {
            $videoDetailItem = [
                'publishedAt' => isset($item['snippet']['publishedAt']) ? $item['snippet']['publishedAt'] : "",
                'title' => isset($item['snippet']['title']) ? $item['snippet']['title'] : "",
                'description' => isset($item['snippet']['description']) ? $item['snippet']['description'] : "",
                'thumbnails' => isset($item['snippet']['thumbnails']['standard']) ? $item['snippet']['thumbnails']['standard'] : "",
                'categoryId' => isset($item['snippet']['categoryId']) ? $item['snippet']['categoryId'] : "",
                'channelId' => isset($item['snippet']['channelId']) ? $item['snippet']['channelId'] : "",
                'channelTitle' => isset($item['snippet']['channelTitle']) ? $item['snippet']['channelTitle'] : "",
                'tags' => isset($item['snippet']['tags']) ? $item['snippet']['tags'] : "",
                'liveBroadcastContent' => isset($item['snippet']['liveBroadcastContent']) ? $item['snippet']['liveBroadcastContent'] : "",
                'player' => isset($item['player']['embedHtml']) ? $item['player']['embedHtml'] : "",
                'videoId' => isset($item["id"]) ? $item["id"] : "",
                'madeForKids' => isset($item['status']['madeForKids']) ? $item['status']['madeForKids'] : "",
                'privacyStatus' => isset($item['status']['privacyStatus']) ? $item['status']['privacyStatus'] : "",
                'uploadStatus' => isset($item['status']['uploadStatus']) ? $item['status']['uploadStatus'] : "",
                'publicStatsViewable' => isset($item['status']['publicStatsViewable']) ? $item['status']['publicStatsViewable'] : "",
                'topicCategories' => isset($item['topicDetails']['topicCategories']) ? $item['topicDetails']['topicCategories'] : "",
                'viewCount' => isset($item['statistics']['viewCount']) ? $item['statistics']['viewCount'] : "",
                'commentCount' => isset($item['statistics']['commentCount']) ? $item['statistics']['commentCount'] : "",
                'likeCount' => isset($item['statistics']['likeCount']) ? $item['statistics']['likeCount'] : "",
                'favoriteCount' => isset($item[ 'statistics']['favoriteCount']) ? $item['statistics']['favoriteCount'] : "",
            ];

            $videoDetails[] = $videoDetailItem;
        }
    
        return response()->json($videoDetails);
    }

    public function handle(Request $request){
        // Replace 'YOUR_API_KEY' with your actual API Key
        $apiKey = env("TUBEDOMINATOR_GOOGLE_APIKEY");
        $gToken = $request->header("gToken");
        
        // Use the Guzzle HTTP client to make API requests
        $client = new Client();

        // Get the playlist ID of your uploads (replace 'YOUR_CHANNEL_ID')
        $channelId = 'UCIaJua9IU_Db15LKAaq_ZYw';
        // $uploadsPlaylistUrl = "https://www.googleapis.com/youtube/v3/channels?key=$apiKey&part=contentDetails&id=$channelId";
        
        // $response = $client->get($uploadsPlaylistUrl, [
        //     'headers' => [
        //         'Authorization' => "Bearer $gToken",
        //     ],
        // ]);

        $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails&mine=true", [
            'headers' => [
                'Authorization' => "Bearer $gToken",
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data;

        if (isset($data['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
            $uploadsPlaylistId = $data['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

            // Fetch videos from the uploads playlist
            $videosPlaylistUrl = "https://www.googleapis.com/youtube/v3/playlistItems?key=$apiKey&part=snippet&playlistId=$uploadsPlaylistId&maxResults=50";

            do {
                $response = $client->get($videosPlaylistUrl, [
                    'headers' => [
                        'Authorization' => "Bearer $gToken",
                    ],
                ]);
                $videoData = json_decode($response->getBody(), true);

                foreach ($videoData['items'] as $video) {
                    $videoTitle = $video['snippet']['title'];
                    $videoId = $video['snippet']['resourceId']['videoId'];
                    
                    // Process the video data as needed
                    // You can store it in your database or perform other actions here
                    $this->info("Title: $videoTitle, Video ID: $videoId");
                }

                // Check if there are more pages of videos
                $videosPlaylistUrl = isset($videoData['nextPageToken']) ? "$videosPlaylistUrl&pageToken={$videoData['nextPageToken']}" : null;
            } while ($videosPlaylistUrl);
        } else {
            $this->error('Uploads playlist not found.');
        }

        $this->info('Videos fetched successfully.');
    }
    
    public function fetchMyYouTubeVideoss(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);

        $gToken = $request->header("gToken");

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

        // Step 1: Get the playlist ID of the "uploads" playlist for the given channel
        $playlistIdResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails&id=$request->channel_id", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $playlistIdData = json_decode($playlistIdResponse->getBody(), true);

        if (!isset($playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
            return new Response(['status' => 'Failed', 'message' => 'Uploads playlist not found'], 404);
        }

        $uploadsPlaylistId = $playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        // Step 2: Get the videos from the "uploads" playlist
        $videosResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&part=contentDetails&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $videosData = json_decode($videosResponse->getBody(), true);

        // Initialize an array to store video IDs
        $videoIds = [];

        // Loop through the items and extract video IDs
        foreach ($videosData['items'] as $item) {
            if (isset($item['contentDetails']['videoId'])) {
                $videoIds[] = $item['contentDetails']['videoId'];
            }
        }

        // Step 3: Get video details for each video ID
        $videoIdsString = implode(',', $videoIds);

        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=snippet,statistics&id=" . $videoIdsString, [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        // return $videoDetailsData;

        $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/search?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=UCIaJua9IU_Db15LKAaq_ZYw&part=snippet,id&order=date&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        // Process the response to obtain video details
        $videosData = json_decode($response->getBody(), true);
        // return $videosData;

        $filteredVideos = [];

        foreach ($videosData['items'] as $item) {
            if ($item['id']['kind'] === 'youtube#video') {
                $filteredVideos[] = $item;
            }
        }

        return $filteredVideos;

        return response()->json($videoDetails); // Return the processed video details as JSON
    }

    public function updateMyYoutubeVideos(Request $request){
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        $gToken = $request->header("gToken");
        $videoThumbnail = $request->videoThumbnail;
        $thumbnailUrl = null;

        if ($videoThumbnail) {
            if (preg_match('/^data:image\/(\w+);base64,/', $videoThumbnail, $matches)) {
                $extension = $matches[1];
                $data = substr($videoThumbnail, strpos($videoThumbnail, ',') + 1);
                $data = str_replace(' ', '+', $data);
                $decodedThumbnail = base64_decode($data);

                if ($decodedThumbnail) {
                    $filename = 'thumbnail_' . time() . '.' . $extension;
                    $thumbnailPath = 'thumbnails/' . $filename;
                    $thumbnailUrl = 'https://tubedominator.com/storage/' . $thumbnailPath;
                    Storage::disk('public')->put($thumbnailPath, $decodedThumbnail);
                }
            } else {
                $thumbnailUrl = null;
            }
        }

        $videoSnippetData = [
            'id' => $request->videoId,
            "snippet" => [
                "categoryId" => $request->categoryId,
                "title" => $request->videoTitle,
            ]
        ];

        if (!empty($request->videoDescription)) {
            $videoSnippetData['snippet']['description'] = $request->videoDescription;
        }

        if (!empty($request->videoTags)) {
            $videoSnippetData['snippet']['tags'] = $request->videoTags;
        }

        if ($thumbnailUrl !== null) {
            // Define the path where you want to save the file
            $filePath = storage_path('app/descriptions'); // Change the path as needed

            // Create the directory if it doesn't exist
            if (!is_dir($filePath)) {
                mkdir($filePath, 0755, true);
            }

            // Generate a unique filename or use a specific one
            $filename = 'video_description.txt'; // Change the filename as needed

            // Write the description to the file
            file_put_contents($filePath . '/' . $filename, $request->videoThumbnailHeight,);

            $videoSnippetData["snippet"]["thumbnails"] = [
                "high" => [
                    "url" => $thumbnailUrl,
                    "height" => $request->videoThumbnailHeight,
                    "width" => $request->videoThumbnailWidth
                ]
            ];
        }

        $client = new Client();

        try {
            $videoDetailsResponse = $client->request('PUT', "https://www.googleapis.com/youtube/v3/videos?&part=snippet", [
                'headers' => [
                    'Authorization' => $gToken,
                ],
                'json' => $videoSnippetData
            ]);
        
            if ($videoDetailsResponse->getStatusCode() === 200) {
                $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        
                return response()->json(['status' => 'success', 'data' => [$videoDetailsData, $videoSnippetData]]);
            } else {
                // Handle other response codes here (e.g., 401 Unauthorized)
                $errorResponse = json_decode($videoDetailsResponse->getBody(), true);
                return response()->json(['status' => 'error', 'message' => 'API Error: ' . $errorResponse['error']['message']], $videoDetailsResponse->getStatusCode());
            }

            return response()->json(['status' => 'success', 'data' => $videoSnippetData]);
        } catch (\Exception $e) {
            // Handle general exceptions
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }
    
    private function validateRequest(Request $request){
        $validator = Validator::make($request->all(), [
            'videoTitle' => '""able',
            'videoDescription' => '""able',
            'videoTags' => '""able',
            'videoThumbnail' => '""able',
            'categoryId' => 'required',
            'videoId' => 'required',
            'videoThumbnailUrl' => '""able',
            'videoThumbnailHeight' => '""able',
            'videoThumbnailWidth' => '""able',
        ]);
    
        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid request data');
        }
    }

    public function fetchMyYoutubeInfo(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);

        $gToken = $request->header("gToken");

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
        $response = $client->request('GET', "https://www.googleapis.com/youtube/v3/search?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=snippet%2Cid&order=date&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $responseData = json_decode($response->getBody(), true); // Decode response body
    
        // Initialize an array to store video IDs
        $videoIds = [];
    
        // Loop through the items and extract video IDs
        foreach ($responseData['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
    
        // Remove undefined values from the array
        $videoIds = array_filter($videoIds, function ($value) {
            return $value !== "";
        });
    
        // Convert video IDs array to a comma-separated string
        $videoIdsString = implode(',', $videoIds);
    
        // Make a new API call using the extracted video IDs to get video details
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=$request->channel_id&part=statistics,snippet,id,status,topicDetails,player,localizations,liveStreamingDetails&forContentOwner=true&maxResults=25&id=" . $videoIdsString, [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        // return $videoDetailsData;
    
        // Initialize an array to store processed video details
        $videoDetails = [];
    
        // Process video details similar to combinedData
        foreach ($videoDetailsData['items'] as $index => $item) {
            $videoDetailItem = [
                'publishedAt' => $item['snippet']['publishedAt'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnails' => $item['snippet']['thumbnails']['standard'],
                'categoryId' => $item['snippet']['categoryId'],
                'channelId' => $item['snippet']['channelId'],
                'channelTitle' => $item['snippet']['channelTitle'],
                // 'defaultAudioLanguage' => $item['snippet']['defaultAudioLanguage'],
                'liveBroadcastContent' => $item['snippet']['liveBroadcastContent'],
                'player' => $item['player']['embedHtml'],
                'videoId' => $videoIds[$index], // Use video ID from $videoIds array
                'madeForKids' => $item['status']['madeForKids'],
                'privacyStatus' => $item['status']['privacyStatus'],
                'uploadStatus' => $item['status']['uploadStatus'],
                'publicStatsViewable' => $item['status']['publicStatsViewable'],
                'topicCategories' => $item['topicDetails']['topicCategories'],
                // 'actualEndTime' => $item['liveStreamingDetails']['actualEndTime'],
                // 'actualStartTime' => $item['liveStreamingDetails']['actualStartTime'],
                // 'scheduledStartTime' => $item['liveStreamingDetails']['scheduledStartTime'],
                'viewCount' => $item['statistics']['viewCount'],
                'commentCount' => $item['statistics']['commentCount'],
                'likeCount' => $item['statistics']['likeCount'],
                'favoriteCount' => $item['statistics']['favoriteCount'],
            ];
    
            $videoDetails[] = $videoDetailItem;
        }
    
        // Now, $videoDetails contains the processed video details
    
        // return response()->json($videoDetails); // Return the processed video details as JSON

        foreach ($videoDetails as &$videoDetailItem) {
            $videoId = $videoDetailItem['videoId'];
        
            // Make an API call to find the playlists containing the video
            $playlistSearchResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlists?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&channelId=UCIaJua9IU_Db15LKAaq_ZYw&part=snippet&maxResults=50&videoId=$videoId", [
                'headers' => [
                    'Authorization' => $gToken,
                ],
            ]);
        
            $playlistSearchData = json_decode($playlistSearchResponse->getBody(), true);
        
            // Extract playlist information
            $playlists = [];
            foreach ($playlistSearchData['items'] as $playlistItem) {
                $playlist = [
                    'playlistId' => $playlistItem['id'],
                    'playlistTitle' => $playlistItem['snippet']['title'],
                ];
                $playlists[] = $playlist;
            }
        
            $videoDetailItem['playlists'] = $playlists;
        }
        
        return response()->json($videoDetails);
    }
    
    public function fetchUserYoutubeInfo(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
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
    
        $response = $client->request('GET', "https://youtube-v31.p.rapidapi.com/search?channelId=$request->channel_id&part=snippet%2Cid&order=date&maxResults=50", [
            'headers' => [
                'X-RapidAPI-Host' => env("RapidApiYoutubeV3HOST"),
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);
    
        $responseData = json_decode($response->getBody(), true); // Decode response body
    
        // Initialize an array to store video IDs
        $videoIds = [];
    
        // Loop through the items and extract video IDs
        foreach ($responseData['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
    
        // Remove undefined values from the array
        $videoIds = array_filter($videoIds, function ($value) {
            return $value !== "";
        });
    
        // Convert video IDs array to a comma-separated string
        $videoIdsString = implode(',', $videoIds);
    
        // Make a new API call using the extracted video IDs to get video details
        $videoDetailsResponse = $client->request('GET', 'https://youtube-v31.p.rapidapi.com/videos?part=contentDetails%2Csnippet%2Cstatistics&id=' . $videoIdsString, [
            'headers' => [
                'X-RapidAPI-Host' => env("RapidApiYoutubeV3HOST"),
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        // return $videoDetailsData;
    
        // Initialize an array to store processed video details
        $videoDetails = [];
    
        // Process video details similar to combinedData
        foreach ($videoDetailsData['items'] as $index => $item) {
            $videoDetailItem = [
                'publishedAt' => $item['snippet']['publishedAt'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnails' => $item['snippet']['thumbnails'],
                'channelTitle' => $item['snippet']['channelTitle'],
                'videoId' => $videoIds[$index], // Use video ID from $videoIds array
                'viewCount' => $item['statistics']['viewCount'],
            ];
    
            $videoDetails[] = $videoDetailItem;
        }
    
        // Now, $videoDetails contains the processed video details
    
        return response()->json($videoDetails); // Return the processed video details as JSON
    }

    public function getKeywordVideos(Request $request){
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
    
        // $response = $client->request('GET', 'https://youtube-v311.p.rapidapi.com/search/?part=snippet&maxResults=5&order=relevance&q=web%20development&safeSearch=moderate&type=video%2Cchannel%2Cplaylist', [
        //     'headers' => [
        //         'X-RapidAPI-Host' => env("RapidApiYoutubeV3HOST"),
        //         'X-RapidAPI-Key' => env("RapidApiKey"),
        //     ],
        // ]);

        $queryParamValue = $request->query->get('query');
        $queryParamValue = str_replace(' ', '%', $queryParamValue);


        $response = $client->request('GET', "https://youtube-v311.p.rapidapi.com/search/?part=snippet&maxResults=6&order=relevance&q=$queryParamValue&safeSearch=moderate&type=video", [
            'headers' => [
                'X-RapidAPI-Host' => 'youtube-v311.p.rapidapi.com',
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);
    
        $responseData = json_decode($response->getBody(), true); // Decode response body
    
        // Initialize an array to store video IDs
        $videoIds = [];
    
        // Loop through the items and extract video IDs
        foreach ($responseData['items'] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
    
        // Remove undefined values from the array
        $videoIds = array_filter($videoIds, function ($value) {
            return $value !== "";
        });
    
        // Convert video IDs array to a comma-separated string
        $videoIdsString = implode(',', $videoIds);
        // return $videoIdsString;
    
        // Make a new API call using the extracted video IDs to get video details
        $videoDetailsResponse = $client->request('GET', 'https://youtube-v31.p.rapidapi.com/videos?part=contentDetails%2Csnippet%2Cstatistics&id=' . $videoIdsString, [
            'headers' => [
                'X-RapidAPI-Host' => env("RapidApiYoutubeV3HOST"),
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
        // return $videoDetailsData;
    
        // Initialize an array to store processed video details
        $videoDetails = [];
    
        // Process video details similar to combinedData
        foreach ($videoDetailsData['items'] as $index => $item) {
            $videoDetailItem = [
                'publishedAt' => $item['snippet']['publishedAt'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'thumbnails' => $item['snippet']['thumbnails']['high'],
                'channelTitle' => $item['snippet']['channelTitle'],
                'videoId' => $videoIds[$index], // Use video ID from $videoIds array
                'viewCount' => $item['statistics']['viewCount'],
                'duration' => $item['contentDetails']['duration'],
            ];
    
            $videoDetails[] = $videoDetailItem;
        }
    
        // Now, $videoDetails contains the processed video details
    
        return response()->json($videoDetails); // Return the processed video details as JSON
    }

    // public function getKeywordVideos(Request $request) {
    //     $client = new Client();
    
    // $response = $client->request('GET', "https://youtube-v311.p.rapidapi.com/search/?part=snippet&maxResults=5&order=relevance&q=web%20development&safeSearch=none&  type=video", [
    //         'headers' => [
    //             'X-RapidAPI-Host' => 'youtube-v311.p.rapidapi.com',
    //             'X-RapidAPI-Key' => env("RapidApiKey"),
    //         ],
    //     ]);
    
    //     $jsonResponse = (string) $response->getBody();
    //     $dataArray = json_decode($jsonResponse, true);
        
    //     $videoIds = array_column($dataArray['items'], 'videoId');
    //     return $videoIds;
        
    //     $videosData = [];
    
    //     foreach ($videoIds as $videoId) {
    //     return $videoId->videoId;

    //         $videoResponse = $client->request('GET', "https://youtube-v3-lite.p.rapidapi.com/videos?id={$videoId->videoId}&part=snippet%2CcontentDetails%2Cstatistics", [
    //             'headers' => [
    //                 'X-RapidAPI-Host' => 'youtube-v3-lite.p.rapidapi.com',
    //                 'X-RapidAPI-Key' => env("RapidApiKey"),
    //             ],
    //         ]);
    
    //         $videoJsonResponse = (string) $videoResponse->getBody();
    //         $videoData = json_decode($videoJsonResponse, true);
    
    //         $videosData[] = $videoData;
    //     }
    
    //     return new Response($videosData);
    // }
    
    public function getChannels(Request $request) {
        $this->validate($request, [
            'channelTitle' => 'required'
        ]);
        
        // return $request->channelTitle;

        $client = new Client();
    
        $response = $client->request('GET', "https://youtube-v3-lite.p.rapidapi.com/search?q=$request->channelTitle&part=id%2Csnippet&type=channel", [
            'headers' => [
                'X-RapidAPI-Host' => 'youtube-v3-lite.p.rapidapi.com',
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);
    
        $jsonResponse = (string) $response->getBody();
        $dataArray = json_decode($jsonResponse, true);
    
        $channelInfoList = [];
    
        foreach ($dataArray['items'] as $item) {
            $channelInfo = [
                'channelId' => $item['id']['channelId'],
                'channelTitle' => $item['snippet']['channelTitle'],
                'description' => $item['snippet']['description'],
                'thumbnailUrl' => 'https:' . $item['snippet']['thumbnails']['high']['url']
            ];
    
            $channelInfoList[] = $channelInfo;
        }
    
        $limitedChannelList = array_slice($channelInfoList, 0, 10);
        return $limitedChannelList;

    }
    
    public function createUser(Request $request){
        $this->validate($request, [
            'user_id' => 'required|string',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'fullName' => 'required|string',
            'business_email' => 'required|string',
        ]);

        $newUser = new Registration();
        $newUser->business_email = $userBusinessEmail;
        $newUser->firstName = $request->firstName;
        $newUser->lastName = $request->lastName;
        $newUser->fullName = $request->fullName;
        $newUser->user_id = $request->user_id;
        $newUser->save();

        return new Response([
            'success' => true,
            'message' => 'user added successfully',
        ], 200);
    }

    public function saveUserYoutubeInfo(Request $request){
        // $this->validate($request, [
        //     'channel_name' => 'required|string',
        //     'channel_id' => 'required|string',
        //     'channel_language' => 'required|string',
        //     'description' => 'required|string',
        //     'business_email' => 'required|string',
        //     'accept_terms' => 'required|string',
        //     'keywords' => 'required|string',
        // ]);

        $userBusinessEmail =  $request->business_email;
        $user_id =  $request->user_id;
        if ($this->userExists($userBusinessEmail, $user_id)) {
            return new Response(['success' => false, 'message' => 'You have already added this Channel Email'], 400);
        }

        // $key = env('JWT_SECRET');
        // $token = explode(" ", $request->header("authorization"))[1];
        
        $newUser = Registration::where("id", $request->userRecordId)->first();
        $newUser->channel_name = $request->channel_name;
        $newUser->channel_id = $request->channel_id;
        $newUser->channel_language = $request->channel_language;
        $newUser->description = $request->description;
        $newUser->business_email = $userBusinessEmail;
        $newUser->accept_terms = $request->accept_terms;
        $newUser->keywords = $request->keywords;
        // $newUser->firstName = $request->firstName;
        // $newUser->lastName = $request->lastName;
        // $newUser->fullName = $request->fullName;
        $newUser->channelFirstName = $request->channelFirstName;
        $newUser->channelLastName = $request->channelLastName;
        $newUser->channelFullName = $request->channelFullName;
        $newUser->channel_image_link = $request->channel_image_link;
        $newUser->user_id = $request->user_id;
        $newUser->clerkProfile = $request->clerkProfile;
        // $newUser->token = $token;
        $newUser->save();

        // Generate JWT token
        $jwtSecret = env('JWT_SECRET'); 
        $jwtSecretALG = env('JWT_SECRET_ALG');

        $jwtPayload = [
            'user_id' => $newUser->user_id,
        ];

        $jwtToken = JWT::encode($jwtPayload, $jwtSecret, $jwtSecretALG);

        return new Response([
            'success' => true,
            'message' => 'Channel added successfully',
            'token' => $jwtToken,
        ], 200);
    }

    public function getSavedUserYoutubeInfo(Request $request){
        
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
        $user = Registration::where('user_id', $user_id)->first();

        if (!$user) {
            return new Response([
                'success' => 'false',
                'message' => "unknown user",
            ], 401);
        }
        
        $userInfo = [
            "firstName" => $user->firstName,
            "lastName" => $user->lastName,
            "fullName" => $user->fullName,
            "channelFullName" => $user->channelFullName,
            "channel_id" => $user->channel_id,
            "business_email" => $user->business_email,
            "channel_image_link" => $user->channel_image_link
        ];

        return new Response([
            'success' => 'true',
            'data' => $userInfo,
        ], 200);
    }

    public function getUserEncryptedData(Request $request){
        $user_id =$request->user_id;
        
        $user = Registration::where('user_id', $user_id)->first();

        if (!$user) {
            return new Response([
                'success' => 'false',
                'message' => "unknown user",
            ], 401);
        }
        
        $userInfo = [
            "encryptedData" => $user->clerkProfile,
        ];

        return new Response([
            'success' => 'true',
            'data' => $userInfo,
        ], 200);
    }

    public function getSavedUserToken(Request $request){
        $user_id = $request->user_id;
        
        $user = Registration::where('user_id', $user_id)->first();

        if (!$user) {
            return new Response([
                'success' => 'false',
                'message' => "unknown user",
            ], 401);
        }
        
        $token = $user->token;
   
        return new Response([
            'success' => 'true',
            'token' => $token,
        ], 200);
    }

    public function saveUserToken(Request $request){
        $this->validate($request, [
            'encryptedFullData' => 'required|string',
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
        
        $user = Registration::where('user_id', $user_id)->first();

        if (!$user) {
            return new Response([
                'success' => 'false',
                'message' => "unknown user",
            ], 401);
        }
        
        $user->clerkProfile = $request->encryptedFullData;
        $user->save();
   
        return new Response([
            'success' => 'true',
            'message' => "Token stored for user $user_id",
        ], 200);
    }

    public function isLoggedIn(Request $request) {  
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

    }

    public function ischannelRegistered(Request $request) {
        $this->validate($request, [
            'user_id' => 'required'
        ]);

        $user_id = $request->user_id;
        $user = Registration::where('user_id', $user_id)->first();

        if (!$user) {
            return new Response(['success' => false]);
        } else {
            return new Response(['success' => true]);
        }
    }

    function getYouTubeVideoDetails() {
        $client = new \GuzzleHttp\Client();
    
        $response = $client->request('GET', 'https://youtube-data8.p.rapidapi.com/video/streaming-data/?id=VyHV0BRtdxo', [
            'headers' => [
                'X-RapidAPI-Host' => 'youtube-data8.p.rapidapi.com',
                'X-RapidAPI-Key' => env("RapidApiKey"),
            ],
        ]);        

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);
    
        return $responseData;
    }

    // YOUTUBE POSTS
    public function saveUserVideoToDraft(Request $request){
        // $this->validate($request, [
        //     'channel_name' => 'required|string',
        //     'channel_id' => 'required|string',
        //     'channel_language' => 'required|string',
        //     'description' => 'required|string',
        //     'business_email' => 'required|string',
        //     'accept_terms' => 'required|string',
        //     'keywords' => 'required|string',
        // ]);

        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        // $key = env('JWT_SECRET');
        // $token = explode(" ", $request->header("authorization"))[1];
        
        $newUser = new draftPost();
        $newUser->channel_name = $request->channel_name;
        $newUser->channel_id = $request->channel_id;
        $newUser->channel_language = $request->channel_language;
        $newUser->description = $request->description;
        $newUser->business_email = $userBusinessEmail;
        $newUser->accept_terms = $request->accept_terms;
        $newUser->keywords = $request->keywords;
        $newUser->firstName = $request->firstName;
        $newUser->lastName = $request->lastName;
        $newUser->fullName = $request->fullName;
        $newUser->channelFirstName = $request->channelFirstName;
        $newUser->channelLastName = $request->channelLastName;
        $newUser->channelFullName = $request->channelFullName;
        $newUser->channel_image_link = $request->channel_image_link;
        $newUser->user_id = $request->user_id;
        $newUser->clerkProfile = $request->clerkProfile;
        // $newUser->token = $token;
        $newUser->save();

        // Generate JWT token
        $jwtSecret = env('JWT_SECRET'); 
        $jwtSecretALG = env('JWT_SECRET_ALG');

        $jwtPayload = [
            'user_id' => $newUser->user_id,
        ];

        $jwtToken = JWT::encode($jwtPayload, $jwtSecret, $jwtSecretALG);

        return new Response([
            'success' => 'User channel details saved',
            'message' => 'Channel added successfully',
            'token' => $jwtToken,
        ], 200);
    }
    
     private function grabUserFromToken($request){
        $key = env('JWT_SECRET');
        $token = explode(" ", $request->header("authorization"))[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $decodedArr = json_decode(json_encode($decoded), true);

        $user_id = $decodedArr['user_id'];
        
        return $user_id;
    }

     private function grabUsergAccessToken($request){
        $token = explode(" ", $request->header("authorization"))[1];
        return $token;
    }


    // TESTS 
    public function getSuggestions(Request $request){
        $apiKey = env('TUBEDOMINATOR_GOOGLE_APIKEY');
        $query = $request->input('Holy Ghost');
        $gToken = $request->header("gToken");


        // Create a Guzzle client instance
        $client = new Client();

        // Define the headers with the "Authorization" header
        $headers = [
            'Authorization' => $gToken, // Replace 'gToken' with your actual token value
        ];

        // Make a GET request to the YouTube Data API with headers
        $url = "https://www.googleapis.com/youtube/v3/search?key={$apiKey}&q={$query}&type=video&part=snippet";
        $response = $client->get($url, [
            'headers' => $headers, // Include the headers in the request
        ]);

        // Parse and return autocomplete suggestions from the response
        $suggestions = json_decode($response->getBody(), true);

        return response()->json($suggestions);
    }

    public function ll(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);
    
        $gToken = $request->header("gToken");
    
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
    
        // Step 1: Get the playlist ID of the "uploads" playlist for the given channel
        $playlistIdResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails&id=$request->channel_id", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $playlistIdData = json_decode($playlistIdResponse->getBody(), true);
    
        if (!isset($playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
            return new Response(['status' => 'Failed', 'message' => 'Uploads playlist not found'], 404);
        }
    
        $uploadsPlaylistId = $playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
    
        // Step 2: Get all videos (including public, private, and unlisted) from the "uploads" playlist
        $videosResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&part=contentDetails&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $videosData = json_decode($videosResponse->getBody(), true);
        return $videosData;
        // Initialize an array to store video details
        $videoDetails = [];
    
        // Loop through the items and extract video IDs
        foreach ($videosData['items'] as $item) {
            if (isset($item['contentDetails']['videoId'])) {
                $videoId = $item['contentDetails']['videoId'];
    
                // Step 3: Get video details for each video ID, regardless of privacy status
                $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=snippet&id=$videoId", [
                    'headers' => [
                        'Authorization' => $gToken,
                    ],
                ]);
    
                $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);
    
                // Process and store video details
                $videoDetailItem = [
                    'publishedAt' => $videoDetailsData['items'][0]['snippet']['publishedAt'],
                    'title' => $videoDetailsData['items'][0]['snippet']['title'],
                    'description' => $videoDetailsData['items'][0]['snippet']['description'],
                    // Add other video details as needed
                    // ...
                    'videoId' => $videoId,
                ];
    
                // Identify the playlist(s) this video belongs to (you may need to make additional API requests)
                // ...
    
                $videoDetails[] = $videoDetailItem;
            }
        }
    
        // Now, $videoDetails contains the processed video details, including playlist information
    
        return response()->json($videoDetails);
    }

    public function fetchMyYouTubeVideossss(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);

        $gToken = $request->header("gToken");

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

        // Step 1: Get the playlist ID of the "uploads" playlist for the given channel
        $playlistIdResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails&id=$request->channel_id", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $playlistIdData = json_decode($playlistIdResponse->getBody(), true);

        if (!isset($playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
            return new Response(['status' => 'Failed', 'message' => 'Uploads playlist not found'], 404);
        }

        $uploadsPlaylistId = $playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        // Step 2: Get all videos (including public, private, and unlisted) from the "uploads" playlist
        // $videosResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&part=contentDetails&maxResults=50", [
        //     'headers' => [
        //         'Authorization' => $gToken,
        //     ],
        // ]);
        $videosResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&part=contentDetails&maxResults=50&type=video&status=private", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $videosData = json_decode($videosResponse->getBody(), true);
        return $videosData;

        // Initialize an array to store video details
        $videoDetails = [];

        // Loop through the items and extract video IDs
        foreach ($videosData['items'] as $item) {
            if (isset($item['contentDetails']['videoId'])) {
                $videoId = $item['contentDetails']['videoId'];

                // Step 3: Get video details for each video ID, regardless of privacy status
                $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=snippet&id=$videoId", [
                    'headers' => [
                        'Authorization' => $gToken,
                    ],
                ]);

                $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);

                // Process and store video details
                $videoDetailItem = [
                    'publishedAt' => $videoDetailsData['items'][0]['snippet']['publishedAt'],
                    'title' => $videoDetailsData['items'][0]['snippet']['title'],
                    'description' => $videoDetailsData['items'][0]['snippet']['description'],
                    // Add other video details as needed
                    // ...
                    'videoId' => $videoId,
                ];

                // Identify the playlist(s) this video belongs to (you may need to make additional API requests)
                // ...

                $videoDetails[] = $videoDetailItem;
            }
        }

        // Now, $videoDetails contains the processed video details, including playlist information

        return response()->json($videoDetails);
    }

    public function fetchMyYouTubeVideosWithPlayList(Request $request){
        $this->validate($request, [
            'channel_id' => 'required'
        ]);

        $gToken = $request->header("gToken");

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

        // Step 1: Get the playlist ID of the "uploads" playlist for the given channel
        $playlistIdResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=contentDetails&id=$request->channel_id", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $playlistIdData = json_decode($playlistIdResponse->getBody(), true);

        if (!isset($playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'])) {
            return new Response(['status' => 'Failed', 'message' => 'Uploads playlist not found'], 404);
        }

        $uploadsPlaylistId = $playlistIdData['items'][0]['contentDetails']['relatedPlaylists']['uploads'];

        // Step 2: Get all videos (including public, private, and unlisted) from the "uploads" playlist
        $videosResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&part=contentDetails&maxResults=50", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $videosData = json_decode($videosResponse->getBody(), true);

        // Initialize an array to store video details
        $videoDetails = [];

        // Loop through the items and extract video IDs
        foreach ($videosData['items'] as $item) {
            if (isset($item['contentDetails']['videoId'])) {
                $videoId = $item['contentDetails']['videoId'];

                // Step 3: Get video details for each video ID, regardless of privacy status
                $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=snippet&id=$videoId", [
                    'headers' => [
                        'Authorization' => $gToken,
                    ],
                ]);

                $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true);

                // Identify the playlist(s) this video belongs to
                // Step 4: Get playlist information for each video
                $playlistInformationResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/playlistItems?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&playlistId=$uploadsPlaylistId&videoId=$videoId&part=snippet", [
                    'headers' => [
                        'Authorization' => $gToken,
                    ],
                ]);

                $playlistInformationData = json_decode($playlistInformationResponse->getBody(), true);

                // Process and store video details, including playlist information
                $videoDetailItem = [
                    'publishedAt' => $videoDetailsData['items'][0]['snippet']['publishedAt'],
                    'title' => $videoDetailsData['items'][0]['snippet']['title'],
                    'description' => $videoDetailsData['items'][0]['snippet']['description'],
                    'videoId' => $videoId,
                    'playlistInformation' => $playlistInformationData['items'][0]['snippet'], // Adjust as needed
                ];

                $videoDetails[] = $videoDetailItem;
            }
        }

        // Now, $videoDetails contains the processed video details, including playlist information

        return response()->json($videoDetails);
    }
}