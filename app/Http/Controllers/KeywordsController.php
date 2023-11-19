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
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Models\OriginalPost;

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
        $videoIds = $this->extractVideoIds($videoDetails);
        $analyzedVideoDetails = $this->analyzeVideoDetails($gToken, $videoIds);
        $channelUsernames = $this->extractChannels($videoDetails);
        $channelDetails = $this->analyzeYoutubeChannels($gToken, $channelUsernames);
        $slicedVideoDetails = array_slice($videoDetails, 0, 10);
        $slicedVideoDetailsUpdated = $this->convertPublishedDates($slicedVideoDetails, $channelDetails, $analyzedVideoDetails);

        return response()->json($slicedVideoDetailsUpdated);
    }

    public function fetchSerpYoutubeVideosOnly(Request $request){
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

        return response()->json($slicedVideoDetails);
    }

    public function fetchSerpGoogleVideos(Request $request){    
        $this->validate($request, [
            'keyword' => 'required',
            'location' => 'required',
            'country' => 'required',
            'language' => 'required'
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
    
        $serpData = $this->serpGoogleVideosData($request->keyword, $request->location, $request->country, $request->language);
    
        // Check if 'inline_videos' is available in the dataset
        if (isset($serpData->channel_results)) {
            $videoDetails = $serpData->channel_results;
            $slicedVideoDetails = array_slice($videoDetails, 0, 10);
    
            return response()->json(['success' => true, 'data' => $slicedVideoDetails], 200);
        } else {
            // 'inline_videos' not available, return false
            return response()->json(['success' => false, 'message' => 'No Google videos for this keyword'], 404);
        }
    }

    private function extractVideoIds($data){
        $videoIds = [];

        foreach ($data as $item) {
            $videoId = substr($item->link, strrpos($item->link, '=') + 1);

            if (!in_array($videoId, $videoIds)) {
                $videoIds[] = $videoId;
            }
        }

        return $videoIds;
    }

    private function extractChannels($data){
        $channels = [];

        // foreach ($data as $item) {
        //     $channelName = substr($item->channel->link, strrpos($item->channel->link, '@') + 1);

        //     if (!in_array($channelName, $channels)) {
        //         $channels[] = $channelName;
        //     }
        // }

        foreach ($data as $item) {
            $channelName = $item->channel->name;
            if (!in_array($channelName, $channels)) {
                $channels[] = $channelName;
            }
        }

        $firstFiveUsernames = array_slice($channels, 0, 2);

        return $firstFiveUsernames;
    }

    private function fss($gToken, $channelUsernames){
        $client = new Client();

        $result = [];
    
        // Limit to the first 5 channel usernames
        $firstFiveUsernames = array_slice($channelUsernames, 0, 5);
    
        $subscriberCounts = [];
    
        foreach ($firstFiveUsernames as $username) {
            $response = $client->request('GET', "https://youtube.googleapis.com/youtube/v3/search?part=snippet,statistics,contentDetails&type&key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "q=" . "TechTalkwithATM", [
                'headers' => [
                    'Authorization' => $gToken,
                ],
            ]);
    
            $channelData = json_decode($response->getBody(), true);
            return $channelData;
    
            if (isset($channelData['items'][0]['statistics']['subscriberCount'])) {
                $subscriberCount = $channelData['items'][0]['statistics']['subscriberCount'];
    
                $result[$username] = [
                    'subscriber_count' => $subscriberCount,
                ];
    
                $subscriberCounts[] = $subscriberCount;
            } else {
                // Handle the case where no channel data is retrieved for the username
                $result[$username] = [
                    'error' => 'No data available',
                ];
            }
        }
    
        // Calculate the requested metrics
        $lowestSubscriberCount = min($subscriberCounts);
        $highestSubscriberCount = max($subscriberCounts);
        $averageSubscriberCount = count($subscriberCounts) > 0 ? array_sum($subscriberCounts) / count($subscriberCounts) : 0;
        sort($subscriberCounts);
        $count = count($subscriberCounts);
        $middle = floor(($count - 1) / 2);
    
        $medianSubscriberCount = ($subscriberCounts[$middle] + $subscriberCounts[$middle + 1 - $count % 2]) / 2;
    
        // Determine category based on median
        if ($medianSubscriberCount <= 500000) {
            $category = 'Small';
        } elseif ($medianSubscriberCount <= 1000000) {
            $category = 'Medium';
        } else {
            $category = 'Large';
        }
    
        return [
            'lowest_subscriber_count' => $lowestSubscriberCount,
            'highest_subscriber_count' => $highestSubscriberCount,
            'average_subscriber_count' => $averageSubscriberCount,
            'median_subscriber_count' => $medianSubscriberCount,
            'subscriber_category' => $category,
            'detailed_results' => $result, // Optionally return detailed results for each username
        ];
    }

    private function analyzeVideoDetails($gToken, $videoIds){
        $client = new Client();
        $videoIdsString = implode(',', $videoIds);
    
        $videoDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/videos?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&part=statistics,snippet,id,status,topicDetails,player,localizations,contentDetails&id=" . $videoIdsString, [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);
    
        $videoDetailsData = json_decode($videoDetailsResponse->getBody(), true)["items"];
        // $videoDetailsArray = json_decode(json_encode($videoDetails), true);
    
        // Merge videoDetailsData with $videoDetails based on videoId
        // $mergedData = array_map(function ($apiData) use ($videoDetailsArray) {
        //     $videoId = $apiData["id"] ?? null;
    
        //     if ($videoId === null) {
        //         return $apiData;
        //     }
    
        //     $existingData = array_filter($videoDetailsArray, function ($item) use ($videoId) {
        //         return $item["id"] === $videoId;
        //     });
    
        //     // If videoId exists in $videoDetails, merge the data
        //     if (!empty($existingData)) {
        //         $mergedData = array_merge($apiData, current($existingData));
        //         return $mergedData;
        //     }
    
        //     return $apiData;
        // }, $videoDetailsData);
    
        return $videoDetailsData;
    }
    
    private function analyzeYoutubeChannels($gToken, $channelUsernames){
    
        $client = new Client();
    
        $matchingChannelIds = [];

        foreach ($channelUsernames as $channelUsername) {
            $searchResponse = $client->request('GET', "https://youtube.googleapis.com/youtube/v3/search?part=snippet&maxResults=3&order=date&key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&q=" . urlencode($channelUsername));
        
            $searchData = json_decode($searchResponse->getBody(), true);
            // return $searchData;
        
            foreach ($searchData['items'] as $item) {
                if (
                    isset($item['snippet']['channelTitle']) &&
                    isset($item['snippet']['channelId']) &&
                    $item['snippet']['channelTitle'] === $channelUsername
                ) {
                    $matchingChannelIds[] = $item['snippet']['channelId'];
                    break;
                }
            }
        }

        $matchingChannelIds = implode(',', $matchingChannelIds);


        $channelDetailsResponse = $client->request('GET', "https://www.googleapis.com/youtube/v3/channels?key=" . env('TUBEDOMINATOR_GOOGLE_APIKEY') . "&id=" . $matchingChannelIds . "&part=statistics,snippet,contentDetails,topicDetails,brandingSettings,localizations", [
            'headers' => [
                'Authorization' => $gToken,
            ],
        ]);

        $channelDetailsData = json_decode($channelDetailsResponse->getBody(), true);

        foreach ($channelDetailsData['items'] as $index => $item) {
    
            if (isset($item['statistics']['subscriberCount'])) {
                $subscriberCount = $item['statistics']['subscriberCount'];
    
                $result[] = [
                    'channel_id' => isset($item["id"]) ? $item["id"] : "",
                    'subscriber_count' => $subscriberCount,
                ];
    
                $subscriberCounts[] = $subscriberCount;
            } else {
                // Handle the case where no subscriber count data is retrieved for the channel
                $result[] = [
                    'channel_id' => isset($item["id"]) ? $item["id"] : "",
                    'error' => 'No subscriber count data available',
                ];
            }
        }
    
        // Calculate the requested metrics
        $lowestSubscriberCount = !empty($subscriberCounts) ? min($subscriberCounts) : 0;
        $highestSubscriberCount = !empty($subscriberCounts) ? max($subscriberCounts) : 0;
        $averageSubscriberCount = count($subscriberCounts) > 0 ? array_sum($subscriberCounts) / count($subscriberCounts) : 0;
    
        if (!empty($subscriberCounts)) {
            sort($subscriberCounts);
            $count = count($subscriberCounts);
            $middle = floor(($count - 1) / 2);
    
            $medianSubscriberCount = ($subscriberCounts[$middle] + $subscriberCounts[$middle + 1 - $count % 2]) / 2;
        } else {
            $medianSubscriberCount = 0;
        }
    
        // Determine category based on median
        if ($medianSubscriberCount <= 500000) {
            $category = 'Small';
        } elseif ($medianSubscriberCount <= 1000000) {
            $category = 'Medium';
        } else {
            $category = 'Large';
        }
    
        return [
            'lowest_subscriber_count' => $lowestSubscriberCount,
            'highest_subscriber_count' => $highestSubscriberCount,
            'average_subscriber_count' => $averageSubscriberCount,
            'median_subscriber_count' => $medianSubscriberCount,
            'subscriber_category' => $category,
            'detailed_results' => $result,
        ];
    }

    private function convertPublishedDates($data, $channelDetails, $analyzedVideoDetails){

        $dateCategories = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
        ];

        foreach ($data as &$item) {
            if (isset($item->published_date)) {
                $formattedDate = $this->convertToActualDate($item->published_date);

                // Analyze the date and update the categories
                $this->analyzeDateCategory($formattedDate, $dateCategories);

                $item->published_date = $formattedDate;
            }
        }

        // Determine the overall category based on the analyzed date categories
        $overallCategory = $this->determineOverallCategory($dateCategories);

        return [
            'data' => $data,
            'date_category' => $overallCategory,
            'channel_details' => $channelDetails,
            'analyzed_video_details' => $analyzedVideoDetails,
        ];
    }

    private function analyzeDateCategory($formattedDate, &$dateCategories) {
        try {
            // Specify the format of the date string
            $date = Carbon::createFromFormat('d/m/Y', $formattedDate);
    
            if ($date->diffInMonths() > 12) {
                $dateCategories['low']++;
            } elseif ($date->diffInMonths() > 6) {
                $dateCategories['medium']++;
            } else {
                $dateCategories['high']++;
            }
        } catch (\Exception $e) {
            // Handle the exception, log it, or throw a new exception
            // depending on your error handling strategy.
            // For now, let's just log the error message.
            Log::error($e->getMessage());
        }
    }

    private function determineOverallCategory($dateCategories){
        if ($dateCategories['low'] > 5) {
            return 'Low';
        } elseif ($dateCategories['medium'] > 5) {
            return 'Medium';
        } else {
            return 'High';
        }
    }

    private function convertToActualDate($publishedDate) {
        $publishedDate = strtolower($publishedDate);
    
        if (strpos($publishedDate, 'ago') !== false) {
            // Handle "X hours/days/weeks ago" format
            $intervalString = substr($publishedDate, 0, strpos($publishedDate, 'ago'));
    
            // Check if the interval string is not empty
            if (!empty($intervalString)) {
                // Attempt to convert the interval string to a Carbon interval
                try {
                    $interval = CarbonInterval::fromString($intervalString);
                    $date = Carbon::now()->sub($interval);
    
                    // If the interval is less than a day and falls between two separate days
                    if ($date->isYesterday()) {
                        return $date->format('d/m/Y');
                    } else {
                        return $date->format('d/m/Y');
                    }
                } catch (\Exception $e) {
                    // Handle the exception, log it, or return a default value
                    return "Invalid date format";
                }
            }
        } else {
            // Handle "X months/years ago" format
            $date = Carbon::now()->subMonths(1); // Default to 1 month ago for non-specific cases
    
            if (strpos($publishedDate, 'month') !== false) {
                $months = intval($publishedDate);
                $date = Carbon::now()->subMonths($months);
            } elseif (strpos($publishedDate, 'year') !== false) {
                $years = intval($publishedDate);
                $date = Carbon::now()->subYears($years);
            }
    
            return $date->format('d/m/Y');
        }
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

    private function serpGoogleVideosData($keyword, $location, $country, $language) {
        $params = [
            'query' => [
                'q' => $keyword,
                'location' => $location,
                'hl' => $language,
                'gl' => $country,
                'api_key' => env('SERP_API_APIKEY'),
            ]
        ];
        // $params = [
        //     'query' => [
        //         'q' => $keyword,
        //         'location' => 'United States',
        //         'hl' => 'en',
        //         'gl' => 'us',
        //         'api_key' => env('SERP_API_APIKEY'),
        //     ]
        // ];
        
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
    
        try {
            $savedIdeas = SavedIdea::where('user_id', $user_id)
            ->where('email', $email)
            ->orderBy('updated_at', 'desc')
            ->get();

            if ($savedIdeas->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No saved ideas found'], 200);
            }
    
            return response()->json(['success' => true, 'data' => $savedIdeas], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve saved ideas'], 500);
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
    
    // YOUTUBE POSTS
    public function saveYoutubePost(Request $request) {
        // Validation rules
        $rules = [
            'video_id' => 'required',
            'video_title' => 'required',
            'video_description' => 'nullable',
            'video_tags' => 'nullable',
            'video_thumbnail' => 'nullable',
            'email' => 'required|email',
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

        $existingOriginalPost = OriginalPost::where("user_id", $user_id)->where("video_id", $request->video_id)->first();
    
        if ($existingOriginalPost) {
            if ($existingOriginalPost->user_id !== $user_id || $existingOriginalPost->email !== $email) {
                return new Response(['success' => true, 'message' => 'Unauthorized access'], 403);
            }

            $existingOriginalPost->video_title = $request->video_title;
            $existingOriginalPost->video_description = $request->video_description;
            $existingOriginalPost->video_tags = $request->video_tags;
            $existingOriginalPost->video_thumbnail = $request->video_thumbnail;
            $existingOriginalPost->save();

            return new Response(['success' => true, 'message' => 'YouTube post updated'], 404);
        }
    
        $originalPost = new OriginalPost();
        $originalPost->user_id = $user_id;
        $originalPost->video_id = $request->video_id;
        $originalPost->video_title = $request->video_title;
        $originalPost->video_description = $request->video_description;
        $originalPost->video_tags = $request->video_tags;
        $originalPost->video_thumbnail = $request->video_thumbnail;
        $originalPost->save();
    
        return new Response(['success' => true, 'message' => 'Saved YouTube post'], 200);
    }
    
    public function getYoutubePosts(Request $request) {
        $this->validate($request, [
            'email' => 'required|email',
            'video_id' => 'required|email',
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
        $video_id = $request->video_id;
    
        $youtubePosts = OriginalPost::where('user_id', $user_id)
            ->where('email', $email)
            ->where('video_id', $video_id)
            ->orderBy('created_at', 'desc')
            ->get();
    
        if ($youtubePosts->isEmpty()) {
            return new Response(['success' => true, 'data' => 'No saved YouTube posts'], 200);
        }
    
        $formattedYoutubePosts = $youtubePosts->map(function ($post) {
            $post->created_at_formatted = date('M j, Y', strtotime($post->created_at));
            return $post;
        });
    
        return new Response(['success' => true, 'data' => $formattedYoutubePosts], 200);
    }
    
    public function updateYoutubePost(Request $request) {
        // Validation rules
        $rules = [
            'video_title' => 'required',
            'video_description' => 'required',
            'video_tags' => 'required',
            'video_thumbnail' => 'required',
            'post_id' => 'required',
            'email' => 'required|email',
        ];
    
        $this->validate($request, $rules);
    
        $email = $request->email;
        $postId = $request->post_id;
    
        try {
            $user_id = $this->grabUserFromToken($request);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Expired token') {
                return new Response(['status' => 'Failed', 'message' => 'Expired token'], 401);
            } else {
                return new Response(['status' => 'Failed', 'message' => 'Invalid token'], 401);
            }
        }
    
        $originalPost = OriginalPost::find($postId);
    
        if (!$originalPost) {
            return new Response(['success' => true, 'message' => 'YouTube post not found'], 404);
        }
    
        if ($originalPost->user_id !== $user_id || $originalPost->email !== $email) {
            return new Response(['success' => true, 'message' => 'Unauthorized access'], 403);
        }
    
        $originalPost->video_title = $request->video_title;
        $originalPost->video_description = $request->video_description;
        $originalPost->video_tags = $request->video_tags;
        $originalPost->video_thumbnail = $request->video_thumbnail;
        $originalPost->save();
    
        return new Response(['success' => true, 'message' => 'Updated YouTube post'], 200);
    }
    
    public function deleteYoutubePost(Request $request, $id) {
        // Validate the request
        $request->validate([
            'email' => 'required|string',
            // 'post_id' => 'required',
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
        $postId = $id;
    
        $originalPost = OriginalPost::find($postId);
    
        if (!$originalPost) {
            return new Response(['status' => 'Failed', 'message' => 'YouTube post not found'], 404);
        }
    
        if ($originalPost->user_id !== $user_id || $originalPost->email !== $email) {
            return new Response(['status' => 'Failed', 'message' => 'Unauthorized access'], 403);
        }
    
        $originalPost->delete();
    
        return new Response(['success' => true, 'message' => 'YouTube post deleted successfully'], 200);
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
