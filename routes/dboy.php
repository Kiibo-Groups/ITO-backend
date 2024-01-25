<?php
    Route::post('dboy/login','DboyController@login');
    Route::get('dboy/logout','DboyController@logout');
    Route::post('dboy/signup','DboyController@signup');
    Route::get('dboy/homepage_ext','DboyController@homepage_ext');
    Route::get('dboy/homepage','DboyController@homepage');
    Route::get('dboy/overview','DboyController@overview');
    Route::get('dboy/startRide','DboyController@startRide');
    Route::get('dboy/userInfo/{id}','DboyController@userInfo');
    Route::post('dboy/updateInfo','DboyController@updateInfo');
    Route::get('dboy/lang','ApiController@lang');
    Route::post('dboy/updateLocation','DboyController@updateLocation');
    Route::get('dboy/staffStatus/{id}','DboyController@staffStatus');
    Route::get('dboy/getPolylines','DboyController@getPolylines');
    Route::post('dboy/rejected','DboyController@rejected');
    Route::get('dboy/chkNotify','DboyController@chkNotify');
    Route::get('dboy/uploadpic_order','DboyController@uploadpic_order');
    Route::post('dboy/notifyClient','DboyController@notifyClient');
    Route::post('dboy/rateComm_event','DboyController@rateComm_event');
    Route::post('dboy/rateCommDboy_event','DboyController@rateCommDboy_event');
    Route::post('dboy/chkUser', 'DboyController@chkUser');
    Route::post('dboy/verifyDocuments', 'DboyController@verifyDocuments');
    Route::post('dboy/uploadDocuments', 'DboyController@uploadDocuments');
    Route::post('dboy/updateRFC', 'DboyController@updateRFC');
    Route::post('dboy/updateData', 'DboyController@updateData');
    Route::get('dboy/emergencyContacts/{id}', 'DboyController@emergencyContacts');
    Route::post('dboy/emergencyContacts', 'DboyController@createEmergencyContacts');
    Route::get('dboy/getKms/{id}', 'DboyController@getKms');
    Route::get('dboy/createStartKm', 'DboyController@createStartKm');
    Route::get('dboy/makeStripePayment', 'DboyController@stripe');
    Route::get('dboy/getHistory/{id}', 'DboyController@getHistory');
    Route::get('dboy/getTypeDriver', 'DboyController@getTypeDriver');
    Route::get('dboy/getBonuses/{id}','DboyController@getBonuses');
    Route::get('dboy/pages','DboyController@pages');
    Route::get('dboy/sendLocationNotification/{user_id}','DboyController@sendLocationNotification');
    Route::post('dboy/forgot','DboyController@forgot');
    Route::post('dboy/verify','DboyController@verify');
    Route::post('dboy/updatePassword','DboyController@updatePassword');
    Route::get('dboy/verifyClanDboy/{id}' ,'DboyController@verifyClanDboy');
    Route::post('dboy/updateImage', 'DboyController@updateImage');
    // Cancelar Viaje por parte del conductor
    Route::post('dboy/cancelCommDboy_event','DboyController@cancelCommDboy_event');
   /**
    * | Solicitudes | 
    *
    */
    Route::post('dboy/createClanRequests','DboyController@createClanRequests');
    Route::post('dboy/checkClanRequests','DboyController@checkClanRequests');
    Route::post('dboy/checkClanRequestsAll','DboyController@checkClanRequestsAll');
    Route::get('dboy/statusAcceptClan/{id}' ,'DboyController@statusAcceptClan');
    Route::get('dboy/statusRejectClan/{id}' ,'DboyController@statusRejectClan');
    /**
    * Solicitud de primera informacion
    */ 
   Route::get('dboy/homepage_init','DboyController@homepage_init');

   /**
    * Get Biometrics
    */
    Route::post('dboy/getBiometrics','DboyController@getBiometrics');

    /**
     * Generador de links para whatsapp
     */
    Route::post('dboy/WhatsappLinkGenerator','DboyController@WhatsappLinkGenerator');
?>