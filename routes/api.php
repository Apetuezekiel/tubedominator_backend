<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeywordsController;
use App\Http\Controllers\UserYoutubeInfo; 
use App\Http\Controllers\UserAccessController; 


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['api.key'])->group(function () {
    Route::post('sign-in', [UserAccessController::class, 'login']);
    Route::post('sign-up', [UserAccessController::class, 'register']);
    Route::post('addUserId', [UserAccessController::class, 'addUserId']);

    Route::get('dataFillerKeyword', [KeywordsController::class, 'dataFillerKeyword']);
    Route::post('saveUserYoutubeInfo', [UserYoutubeInfo::class, 'saveUserYoutubeInfo']);

    Route::post('addToSavedIdeas', [KeywordsController::class, 'addToSavedIdeas']);
    Route::get('getAllSavedIdeas', [KeywordsController::class, 'getAllSavedIdeas']);
    Route::delete('deleteSavedIdea/{id}', [KeywordsController::class, 'deleteSavedIdea']);

    // Youtube Posts
    Route::post('saveYoutubePost', [KeywordsController::class, 'saveYoutubePost']);
    Route::get('getYoutubePosts', [KeywordsController::class, 'getYoutubePosts']);
    Route::delete('deleteYoutubePost/{id}', [KeywordsController::class, 'deleteYoutubePost']);
 
    Route::post('fetchKeywordStat', [KeywordsController::class, 'fetchKeywordStat']); 
    Route::get('fetchKeywordStatGoogle', [KeywordsController::class, 'fetchKeywordStatGoogle']);
    
    Route::get('getMySearchTerm', [KeywordsController::class, 'getMySearchTerm']);
    Route::post('bookmarkSearchTerm', [KeywordsController::class, 'bookmarkSearchTerm']);
    Route::get('allBookmarkSearchTerms', [KeywordsController::class, 'allBookmarkSearchTerms']);
    Route::delete('deleteSavedIdeaBookmarkSearchTerm', [KeywordsController::class, 'deleteSavedIdeaBookmarkSearchTerm']);

    Route::get('fetchUserYoutubeInfo', [UserYoutubeInfo::class, 'fetchUserYoutubeInfo']);
    Route::get('getChannels', [UserYoutubeInfo::class, 'getChannels']);
    Route::get('getSavedUserYoutubeInfo', [UserYoutubeInfo::class, 'getSavedUserYoutubeInfo']);

    Route::get('getKeywordVideos', [UserYoutubeInfo::class, 'getKeywordVideos']);
    Route::post('saveUserKeyword', [KeywordsController::class, 'saveUserKeyword']);
    Route::get('getUserKeyword', [KeywordsController::class, 'getUserKeyword']);
    Route::delete('deleteUserKeyword/{id}', [KeywordsController::class, 'deleteUserKeyword']);

    Route::post('saveUserTemplate', [KeywordsController::class, 'saveUserTemplate']);
    Route::post('updateUserTemplate', [KeywordsController::class, 'updateUserTemplate']);
    Route::get('getUserTemplate', [KeywordsController::class, 'getUserTemplate']);
    Route::post('deleteUserTemplate', [KeywordsController::class, 'deleteUserTemplate']);
    
    Route::post('try', [KeywordsController::class, 'tryy']);
    Route::get('getSavedUserToken', [UserYoutubeInfo::class, 'getSavedUserToken']);
    Route::post('saveUserToken', [UserYoutubeInfo::class, 'saveUserToken']);
    Route::get('ischannelRegistered', [UserYoutubeInfo::class, 'ischannelRegistered']);
    Route::get('getUserEncryptedData', [UserYoutubeInfo::class, 'getUserEncryptedData']);
    Route::get('getYouTubeVideoDetails', [UserYoutubeInfo::class, 'getYouTubeVideoDetails']);



    Route::get('getMyChannels', [UserYoutubeInfo::class, 'getMyChannels']);
    Route::get('fetchMyYoutubeInfo', [UserYoutubeInfo::class, 'fetchMyYoutubeInfo']);
    Route::get('fetchMyPlaylists', [UserYoutubeInfo::class, 'fetchMyPlaylists']);
    Route::get('fetchMyYoutubeVideo', [UserYoutubeInfo::class, 'fetchMyYoutubeVideo']);
    Route::get('fetchMyYoutubeVideos', [UserYoutubeInfo::class, 'fetchMyYoutubeVideos']);
    Route::get('fetchSerpYoutubeVideos', [KeywordsController::class, 'fetchSerpYoutubeVideos']);
    Route::get('fetchSerpYoutubeVideosOnly', [KeywordsController::class, 'fetchSerpYoutubeVideosOnly']);
    Route::get('fetchSerpGoogleVideos', [KeywordsController::class, 'fetchSerpGoogleVideos']);
    Route::put('updateMyYoutubeVideos', [UserYoutubeInfo::class, 'updateMyYoutubeVideos']);
    // Route::get('getMySearchTerm', [UserYoutubeInfo::class, 'getMySearchTerm']);
    // EXPERIMENT
    Route::get('getAllVideosChatGPT', [UserYoutubeInfo::class, 'fetchMyYouTubeVideosWithPlayList']);
    Route::get('getSuggestions', [UserYoutubeInfo::class, 'getSuggestions']);
    
    // DRAFT POST
    Route::post('saveDraftPost', [KeywordsController::class, 'saveDraftPost']);
    Route::delete('deleteDraftPost', [KeywordsController::class, 'deleteDraftPost']);

});