<?php
    Route::get('/dboy/chat/groups', 'ChatController@Groups');
    Route::get('/dboy/chat/getGroupUnique/{user}/{clan}','ChatController@getGroupUnique');
    Route::get('/dboy/chat/groupAllMembers/{id}', 'ChatController@groupAllMembers');
    Route::post('/dboy/chat/createGroups', 'ChatController@createGroup');
    Route::post('/dboy/chat/groupTrips', 'ChatController@groupTrips');
    Route::post('/dboy/chat/groupLikes', 'ChatController@groupLikes');
    Route::post('/dboy/chat/groupAddMember', 'ChatController@groupAddMember');

    /**
    *
    * Funciones para Chat
    *
    */
    Route::get('/dboy/chat/getChats/{id}','ChatController@getChats');
    Route::get('/dboy/chat/getClanChat/{user_id}/{clan}','ChatController@getClanChat');
    Route::post('/dboy/chat/sendChat','ChatController@sendChat'); 
    Route::get('/dboy/chat/getChat/{user_id}/{channel}/{event}','ChatController@getChat');
    Route::get('/dboy/chat/createIdPusher/{user_id}','ChatController@createIdPusher');
    Route::get('/dboy/chat/getOrderChat/{id}','ChatController@getOrderChat');
?>