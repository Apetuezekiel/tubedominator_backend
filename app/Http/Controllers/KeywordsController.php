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
use App\Models\userTemplate;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Storage;

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
            'countryCode' => 'required|string',
            'languageCode' => 'required|string',
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
        
        // Get the values from the request
        $keyword = $request->keyword;
        $countryCode = $request->countryCode;
        $languageCode = $request->languageCode;

        // Set up the Guzzle client
        $client = new Client();

        // Prepare the request body as an array
        $requestBody = [
            'country' => $countryCode,
            'language' => $languageCode,
            'metrics' => true,
            'metrics_currency' => 'USD',
            'type' => 'suggestions',
            'complete' => true,
            'output' => 'json',
            'apikey' => env('KEYWORD_TOOL_APIKEY'),
            'keyword' => $keyword,
        ];

        // Encode the request body as JSON
        $requestBodyJson = json_encode($requestBody);

        // Make the API request
        $response = $client->request('POST', 'https://api.keywordtool.io/v2/search/suggestions/youtube?apikey=' . env('KEYWORD_TOOL_APIKEY'), [
            'body' => $requestBodyJson,
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);
        
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);

        // $responseData = $this->dataFillerKeyword;

        // Assuming $responseData contains the provided JSON response

        $selectedObjects = [];
        $limit = 12;
        $objectCount = 0;

        foreach ($responseData->results as $key => $objects) {
            if (is_array($objects)) {
                foreach ($objects as $object) {
                    $selectedObjects[] = $object;
                    $objectCount++;
                    if ($objectCount === $limit) {
                        break 2;
                    }
                }
            }
        }

        // $flattenedArray = call_user_func_array('array_merge', $selectedObjects);
        $firstTwentyItems = array_slice($selectedObjects, 0, 5);

        foreach ($firstTwentyItems as $key => $result) {
            $searchTerm = $result->string;
            $m1 = $result->m1;
            $m12 = $result->m12;
            $cmp = $result->cmp;
            // return $searchTerm;

            // Make the call to serpYoutubeData for each search term.
            $searchResults = $this->serpYoutubeData($searchTerm);
            $videoResults = $searchResults->video_results;

            // Calculate median views for the video results.
            $medianViews = $this->calculateMedianVideoViews($videoResults);
            $trend = $this->calculateTrendPercentage($m1, $m12);
            $competition = $this->analyzeCompetition($cmp);

            // Add the median views to the current result in $responseData.
            $firstTwentyItems[$key]->estimated_views = $medianViews;
            $firstTwentyItems[$key]->trend = $trend;
            $firstTwentyItems[$key]->keyword = $firstTwentyItems[$key]->string;
            $firstTwentyItems[$key]->monthlysearch = $firstTwentyItems[$key]->volume;
            $firstTwentyItems[$key]->difficulty = $competition;
            $firstTwentyItems[$key]->countryCode = $countryCode;
            $firstTwentyItems[$key]->languageCode = $languageCode;
        }

        $exact_keyword = [];
        $related_keywords = [];
        
        if (count($firstTwentyItems) > 0) {
            $exact_keyword = array_slice($firstTwentyItems, 0, 1);
            $related_keywords = array_slice($firstTwentyItems, 1);
        }

        return new Response([
            'success' => true,
            'response' => [
                'exact_keyword' => $exact_keyword,
                'related_keywords' => $related_keywords,
                'all' => $firstTwentyItems
            ]
        ]);
    }

    public function fetchSerpYoutubeVideos(Request $request){
        $this->validate($request, [
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

        $videoDetails = $this->serpYoutubeData($request->keyword)->video_results;
        $slicedVideoDetails = array_slice($videoDetails, 0, 10);

        foreach ($slicedVideoDetails['items'] as $index => $item) {
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
        
        return response()->json($slicedVideoDetails);
    }

    private function calculateMedianVideoViews($videoResults) {
        $views = array_column($videoResults, 'views');
        sort($views);
        $totalVideos = count($views);
        $median = 0;
    
        if ($totalVideos % 2 === 0) {
            // For an even number of videos, take the average of the middle two elements
            $midIndex = $totalVideos / 2;
            $median = ($views[$midIndex - 1] + $views[$midIndex]) / 2;
        } else {
            // For an odd number of videos, pick the middle element
            $midIndex = ($totalVideos - 1) / 2;
            $median = $views[$midIndex];
        }
    
        return $median;
    }

    private function calculateTrendPercentage($m1, $m2){
        $months = 12; // Define the number of months from m1 to m12

        $initialMonthValue = $m1;
        $finalMonthValue = $m2;

        $percentageTrend = (($finalMonthValue - $initialMonthValue) / $initialMonthValue) * 100;

        $averageTrend = $percentageTrend / $months;
        return $averageTrend;
    }

    private function calculateTrendPercentageOld($data){
        $months = 12; // Define the number of months from m1 to m12

        $result = collect($data)->map(function ($item) use ($months) {
            $initialMonthValue = $item['m1'];
            $finalMonthValue = $item['m12'];

            $percentageTrend = (($finalMonthValue - $initialMonthValue) / $initialMonthValue) * 100;

            $averageTrend = $percentageTrend / $months;

            return [
                'string' => $item['string'],
                'averageTrendPercentage' => $averageTrend
            ];
        });

        return $result;
    }
    
    private function serpYoutubeData($keyword) {
        $params = [
            'query' => [
                'engine' => 'youtube',
                'search_query' => $keyword,
                'api_key' => env('SERP_API_APIKEY'),
            ]
        ];
        $client = new Client();

        try {
            $response = $client->request('GET', 'https://serpapi.com/search', $params);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $responseData = json_decode($body);
    
            return $responseData;
    
        
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle request exceptions
            echo "Error: " . $e->getMessage();
        }

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $responseData = json_decode($body);

        return $responseData;
    }

    private function analyzeCompetition($cmp){
        if ($cmp >= 0.00 && $cmp <= 0.33) {
            return 'Low';
        } elseif ($cmp > 0.33 && $cmp <= 0.66) {
            return 'Medium';
        } elseif ($cmp > 0.66 && $cmp <= 1.00) {
            return 'High';
        } else {
            return 'Invalid cmp value'; // Or handle the out-of-range values as required
        }
    }

    private function calculateEstimatedViewsOld($monthlySearch, $competitionScore, $overallScore) {

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
            'trend' => 'required',
            'category' => 'required',
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
        $fetchSavedIdea = SavedIdea::where('video_ideas', $newVideoIdea)->where("user_id", $user_id)->first();

        // return $fetchSavedIdea;

        if (!$fetchSavedIdea) { 
            $savedIdea = new SavedIdea(); 
            $savedIdea->user_id = $user_id;
            $savedIdea->email = $email;
            $savedIdea->video_ideas = $request->video_ideas;
            $savedIdea->search_volume = $request->search_volume;
            $savedIdea->keyword_diff = $request->keyword_diff;
            $savedIdea->potential_views = $request->potential_views;
            $savedIdea->trend = $request->trend;
            $savedIdea->category = $request->category;
            $savedIdea->save();
            // $fetchSavedIdea->search_volume = $request->search_volume;
        } else {
            $fetchSavedIdea->keyword_diff = $request->keyword_diff;
            $fetchSavedIdea->potential_views = $request->potential_views;
            $fetchSavedIdea->trend = $request->trend;
            $fetchSavedIdea->category = $request->category;
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
                return response()->json(['success' => true, 'message' => 'Idea deleted successfully'], 200);
            } else {
                return response()->json(['success' => false, 'error' => 'Unauthorized to delete this idea'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to delete idea'], 500);
        }
    }
    
    // USER TEMPLATES
    public function saveUserTemplate(Request $request) {
        // Validation rules
        $rules = [
            'title' => 'required',
            'content' => 'required',
            'email' => 'required|email', // Assuming 'email' should be an email
        ];
    
        $this->validate($request, $rules);
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        $userTemplate = new UserTemplate(); // Assuming the model name is 'UserTemplate' (singular form)
        $userTemplate->title = $request->title;
        $userTemplate->content = $request->content; // Adjust for the correct content field
        $userTemplate->email = $request->email;
        $userTemplate->user_id = $user_id;
        $userTemplate->save();
    
        return new Response(['success' => true, 'message' => 'Saved user Template'], 200);
    }

    public function getUserTemplate(Request $request) {
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

        // Retrieve all user templates for the authenticated user in descending order
        $userTemplates = UserTemplate::where('user_id', $user_id)
            ->where('email', $request->email)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($userTemplates->isEmpty()) {
            return new Response(['success' => "trueNut", 'data' => 'No saved Templates'], 200);
        }

        $formattedUserTemplates = $userTemplates->map(function ($template) {
            $template->created_at_formatted = date('M j, Y', strtotime($template->created_at));
            return $template;
        });
    
        return new Response(['success' => true, 'data' => $formattedUserTemplates], 200);

    }

    public function updateUserTemplate(Request $request) {
        // Validation rules
        $rules = [
            'title' => 'required',
            'content' => 'required',
            'template_id' => 'required',
            'email' => 'required|email', // Assuming 'email' should be an email
        ];
    
        $this->validate($request, $rules);

        $email = $request->email;
        $templateId = $request->template_id;
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        // Find the existing user template by ID
        $userTemplate = UserTemplate::find($templateId);
    
        if (!$userTemplate) {
            return new Response(['status' => 'Failed', 'message' => 'Template not found'], 404);
        }
    
        // Check if the template belongs to the authenticated user
        if (
            $userTemplate->user_id !== $user_id ||
            $userTemplate->email !== $email
        ) {
            return new Response(['status' => 'Failed', 'message' => 'Unauthorized access'], 403);
        }
    
        // Update the template with the provided data
        $userTemplate->title = $request->title;
        $userTemplate->content = $request->content; // Adjust for the correct content field
        $userTemplate->save();
    
        return new Response(['success' => true, 'message' => 'Updated user Template'], 200);
    }
    
    public function deleteUserTemplate(Request $request) {
        // Validate the request
        $request->validate([
            'email' => 'required|string',
            'template_id' => 'required',
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

        $email = $request->email;
        $templateId = $request->template_id;
    
        // Find the user template by ID
        $userTemplate = UserTemplate::find($templateId);

        if (!$userTemplate) {
            return new Response(['status' => 'Failed', 'message' => 'Template not found'], 404);
        }

        // Check if the template belongs to the authenticated user
        if ($userTemplate->user_id !== $user_id || $userTemplate->email !== $email) {
            return new Response(['status' => 'Failed', 'message' => 'Unauthorized access'], 403);
        }

        // Delete the user template
        $userTemplate->delete();

        return new Response(['success' => true, 'message' => 'User Template deleted successfully'], 200);
    }

    // USER KEYWORDS
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
            ->select('keyword', 'email', 'search_volume', 'created_at', 'id')
            ->orderBy('created_at', 'desc') // Sort by 'created_at' in descending order
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

    public function deleteUserKeyword(Request $request, $id){
        // Get the user ID from the token
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }

        // Find the search term by user ID and keyword
        $savedKeyword = userSavedKeyword::where('user_id', $user_id)->where('id', $id)->first();
    
        if (!$savedKeyword) {
            // If the search term does not exist
            return response()->json(['success' => false, 'error' => 'Keyword not found'], 404);
        }
        
        $savedKeyword->delete();

        $userSavedKeywords = userSavedKeyword::where('user_id', $user_id)
        ->where('id', '!=', $id)
        ->select('keyword', 'email', 'search_volume', 'created_at', 'id')
        ->orderBy('created_at', 'desc')
        ->get();

        $formattedUserKeywords = $userSavedKeywords->map(function ($keyword) {
            $keyword->created_at_formatted = date('M j, Y', strtotime($keyword->created_at));
            return $keyword;
        });
    
        return response()->json(['success' => true, 'data' => $formattedUserKeywords, 'message' => 'Keyword deleted successfully'], 200);
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
            'video_thumbnail' => 'nullable',
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

        $thumbnailUrl = null;
        $video_id = $request->video_id;
        $search_term = $request->search_term;
        $video_title = $request->video_title;
        $video_description = $request->video_description;
        $video_tags = $request->video_tags; 
        $video_thumbnail = $request->video_thumbnail; // Retrieve the uploaded file.

        if ($video_thumbnail) {
            // Check if $video_thumbnail is a Base64 encoded image
            if (preg_match('/^data:image\/(\w+);base64,/', $video_thumbnail, $matches)) {
                $extension = $matches[1]; // Get the file extension (e.g., 'png', 'jpg')
                $data = substr($video_thumbnail, strpos($video_thumbnail, ',') + 1);
                $data = str_replace(' ', '+', $data);
                $decodedThumbnail = base64_decode($data);
    
                if ($decodedThumbnail) {
                    // Generate a unique filename and save the decoded thumbnail to a specific directory
                    $filename = 'thumbnail_' . time() . '.' . $extension;
                    $thumbnailPath = 'thumbnails/' . $filename;
                    $thumbnailUrl = 'https://tubedominator.com/storage/' . $thumbnailPath;
                    Storage::disk('public')->put($thumbnailPath, $decodedThumbnail);
                }
            } else {
                $thumbnailUrl = $video_thumbnail;
            }
        }
    
        $existingUserDraft = draftPost::where('video_id', $request->video_id)->where('user_id', $user_id)->first();
        
        if ($existingUserDraft) {
            // Update the existing draft if it exists.
            $existingUserDraft->video_id = $video_id;
            $existingUserDraft->search_term = $search_term;
            $existingUserDraft->video_title = $video_title;
            $existingUserDraft->video_description = $video_description; 
            $existingUserDraft->video_tags = $video_tags;
            $existingUserDraft->video_thumbnail = $thumbnailUrl;
            $existingUserDraft->save();

            // Handle the thumbnail image if it was uploaded.
            // if ($video_thumbnail) {
            //     // Store the uploaded thumbnail in a temporary location.
            //     $path = $video_thumbnail->store('temp');
            //     // Generate a public URL for the stored thumbnail.
            //     $thumbnailUrl = 'https://tubedominator.com/storage/thumbnails/' . $path;
            //     $existingUserDraft->video_thumbnail = $thumbnailUrl;
            // }
    
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
        $userDraft->video_thumbnail = $thumbnailUrl;
    
        // // Handle the thumbnail image if it was uploaded.
        // if ($video_thumbnail) {
        //     // Store the uploaded thumbnail in a temporary location.
        //     $path = $video_thumbnail->store('temp');
    
        //     // Generate a public URL for the stored thumbnail.
        //     $thumbnailUrl = 'https://tubedominator.com/storage/thumbnails/' . $path;
        //     $userDraft->video_thumbnail = $thumbnailUrl;
        // }
    
        $userDraft->save();
    
        if ($video_thumbnail){
            return new Response(['success' => true, 'thumbNail' => $thumbnailUrl, 'message' => 'Saved User draft'], 200);
        }

        return new Response(['success' => true, 'message' => 'Saved User draft'], 200);
    }

    public function getDraftPost(Request $request) {
        $this->validate($request, [
            'video_id' => 'required',
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

        $video_id = $request->video_id;

        $userDraft = draftPost::where('user_id', $user_id)
        ->where('video_id', $video_id)
        ->select('video_id', 'search_term', 'video_title', 'video_description', 'video_tags', 'video_thumbnail')
        ->get();

        if ($userDraft->isEmpty()) {
            return new Response(['success' => "trueNut", 'data' => 'No saved Drafts'], 200);
        }
    
        return new Response(
            ['success' => true, 'data' => $userDraft 
        ], 200);

    }

    public function deleteDraftPost(Request $request) {
        // Validate the request
        $request->validate([
            'video_id' => 'required|string',
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
        $video_id = $request->video_id;
    
        // Find the search term by user ID and keyword
        $draftPost = draftPost::where('user_id', $user_id)->where('video_id', $video_id)->first();
    
        if (!$draftPost) {
            // If the search term does not exist
            return response()->json(['success' => false, 'error' => 'draft post not found'], 404);
        }
    
        // Check if the user is authorized to delete this search term
        if ($draftPost->user_id === $user_id && $draftPost->video_id === $video_id) {
            // Delete the search term
            $draftPost->delete();
            return response()->json(['success' => true, 'message' => 'Draft deleted successfully'], 200);
        } else {
            return response()->json(['success' => false, 'error' => 'Unauthorized to delete this Draft'], 401);
        }
    }

    // HELPER FUNCTIONS
    private function grabUserFromToken($request){
        $key = env('JWT_SECRET');
        $token = explode(" ", $request->header("authorization"))[1];
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $decodedArr = json_decode(json_encode($decoded), true);

        $user_id = $decodedArr['user_id'];
        
        return $user_id;
    }

    public function dataFillerKeyword() {
        $data = [
            "results" => [
                "" => [
                    [
                        "string" => "affiliate marketing",
                        "volume" => 248000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "digital marketing",
                        "volume" => 300000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "social media marketing",
                        "volume" => 180000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "email marketing",
                        "volume" => 220000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "content marketing",
                        "volume" => 195000,
                        // ... rest of the data ...
                    ]
                ],
                "affiliate marketing" => [
                    [
                        "string" => "affiliate marketing",
                        "volume" => 248000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "affiliate marketing for beginners",
                        "volume" => 49800,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "advanced affiliate marketing",
                        "volume" => 72000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "affiliate marketing strategies",
                        "volume" => 155000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "affiliate marketing tools",
                        "volume" => 105600,
                        // ... rest of the data ...
                    ]
                ],
                "something else" => [
                    [
                        "string" => "sample keyword 1",
                        "volume" => 50000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "sample keyword 2",
                        "volume" => 75000,
                        // ... rest of the data ...
                    ]
                ],
                "something else 2" => [
                    [
                        "string" => "example keyword 1",
                        "volume" => 10000,
                        // ... rest of the data ...
                    ],
                    [
                        "string" => "example keyword 2",
                        "volume" => 88000,
                        // ... rest of the data ...
                    ]
                ]
            ]
        ];
    
        // Loop through each dataset under "results" and add "sortable": true
        foreach ($data['results'] as &$datasets) {
            foreach ($datasets as &$dataset) {
                $dataset['sortable'] = true;
            }
        }
    
        // Convert the array to JSON and return it
        return response()->json($data);
    }
    
    
}
