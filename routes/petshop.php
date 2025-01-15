<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetShop\AdminController as PetShopAdminController;
use App\Http\Controllers\PetShop\ExperiencesController;
use App\Http\Controllers\PetShop\OrderController as PetShopOrderController;
use App\Http\Controllers\PetShop\PaymentController as PetShopPaymentController;
use App\Http\Controllers\PetShop\ShippingAddressController as PetShopShippingAddressController;
use App\Http\Controllers\PetShop\UserController as PetShopUserController;
use App\Http\Controllers\PetShop\TicketSpecialRequestController;
use App\Http\Middleware\DatabaseConnectionOne;
use Illuminate\Support\Facades\Route;

Route::post('/register', [PetShopUserController::class, 'signup']);
Route::post('/login', [PetShopUserController::class, 'login']);
Route::get('/activateAccount/{email}', [PetShopUserController::class, 'activateAccount']);
Route::post('/reset-password', [PetShopUserController::class, 'sendResetPasswordLink']);
Route::get('/resetPassword/{email}', [PetShopUserController::class, 'resetPasswordForm']);
Route::post('/resetPassword',   [PetShopUserController::class, 'changeResetPassword']);
Route::get('/emailTemplate/{id}/{user_email}', [PetShopUserController::class, 'emailTemplate']);
Route::get('/categories', [ExperiencesController::class, 'categories']);
Route::get('/experiences', [ExperiencesController::class, 'experience']);
// getHistory

// experience
// travelCollection
Route::middleware(['auth:petshop', DatabaseConnectionOne::class])->group(function () {
    Route::get('/logoutUser', [PetShopUserController::class, 'logout']);
    Route::get('/getOrderHistory', [PetShopOrderController::class, 'getHistory']);
    Route::post('/changePassword',  [PetShopUserController::class, 'changePassword']);
    Route::get('/getUserData', [PetShopUserController::class, 'getUserData']);
    Route::post('/shippingAddress', [PetShopShippingAddressController::class, 'shippingAddress']);
    Route::get('/getAllShippingAddress', [PetShopShippingAddressController::class, 'getAllShippingAddress']);
    Route::get('/getShippingAddress/{id}', [PetShopShippingAddressController::class, 'getShippingAddress']);
    Route::post('/updateExistingShippingAddress/{id}', [PetShopShippingAddressController::class, 'updateExistingShippingAddress']);
    Route::get('/saveExistingShippingAddress/{id}', [PetShopShippingAddressController::class, 'saveExistingShippingAddress']);
    Route::get('/deleteShippingAddress/{id}', [PetShopShippingAddressController::class, 'deleteShippingAddress']);
    Route::patch('/updateUserData', [PetShopUserController::class, 'updateUserData']);
    Route::post('/procceedToCheckout', [PetShopPaymentController::class, 'createCheckoutSession']);
    Route::post('/success', [PetShopPaymentController::class, 'success']);
});

//admins
//payment charges
Route::get('/getPaymentCharges', [PetShopAdminController::class, 'getPaymentCharges']);
Route::get('/getPaymentCharges/{type}', [PetShopAdminController::class, 'getPaymentChargesByType']);

//petpoints
Route::get('/getPetPoints', [PetShopAdminController::class, 'getPetPoints']);
Route::get('/getPetPoints/{id}', [PetShopAdminController::class, 'getPetPointsById']);

Route::post('/ticketSpecialRequest', [TicketSpecialRequestController::class, 'sendRequestMail']);
Route::get('/getBranding', [PetShopAdminController::class, 'getBranding']);
Route::get('/getMenus', [PetShopAdminController::class, 'getMenu']);
Route::post('/adminLogin', [AdminController::class, 'login']);
Route::prefix('admin')->group(function () {
    Route::put('/updatePaymentCharges', [PetShopAdminController::class, 'updatePaymentCharges']);
    Route::put('/updatePetPoints', [PetShopAdminController::class, 'updatePetPoints']);
    Route::post('/uploadUserPetPoints', [PetShopAdminController::class, 'uploadUserPetPoints']);
    Route::get('/getUserPetPoints', [PetShopAdminController::class, 'getUserPetPoints']);
    Route::get('/getUserPetPoints/{email}', [PetShopAdminController::class, 'getUserPetPointsById']);


    // Route::prefix('superAdmin')->group(function () {
    Route::post('/createAdmin', [PetShopAdminController::class, 'createAdmin']);
    Route::get('/removeAdmin', [PetShopAdminController::class, 'removeAdmin']);
    Route::get('/admin-list', [PetShopAdminController::class, 'getAdmin']);
    // });
    //Ticket Venue
    Route::post('/updatePopularityOfVenues', [PetShopAdminController::class, 'updatePopularityOfVenues']);
    //Payment Charges
    // Route::get('/getPaymentCharges', [PetShopAdminController::class, 'paymentChargesPage']);
    Route::post('/editPaymentCharges', [PetShopAdminController::class, 'updateChargeAndStatus']);
    //End Of Payment Charges
    Route::get('/getUsers', [PetShopAdminController::class, 'getUsers']);
    Route::get('/getOrders', [PetShopAdminController::class, 'getOrderHistory']);
    Route::get('/order-list', [PetShopAdminController::class, 'orderList']);
    Route::get('/insertfeatureHotel', [PetShopAdminController::class, 'insertfeatureHotel']);
    Route::get('/insertNotAllowedHotel', [PetShopAdminController::class, 'insertNotAllowedHotel']);
    Route::get('/deletefeatureHotel', [PetShopAdminController::class, 'deletefeatureHotel']);
    Route::get('/deleteNotAllowedHotel', [PetShopAdminController::class, 'deleteNotAllowedHotel']);
    Route::get('/fetchHotel', [PetShopAdminController::class, 'fetchHotel']);
    Route::post('/updateBranding/{id}', [PetShopAdminController::class, 'updateBranding']);


    // updateNotAllowedHotel
    Route::get('/insertFeatureMerchandise', [PetShopAdminController::class, 'insertFeatureMerchandise']);
    Route::get('/insertNotAllowedMerchandise', [PetShopAdminController::class, 'insertNotAllowedMerchandise']);
    Route::get('/deleteFeatureMerchandise', [PetShopAdminController::class, 'deleteFeatureMerchandise']);
    Route::get('/deleteNotAllowedMerchandise', [PetShopAdminController::class, 'deleteNotAllowedMerchandise']);
    Route::get('/fetchMerchandise', [PetShopAdminController::class, 'fetchMerchandise']);

    Route::get('/insertFeatureTicket', [PetShopAdminController::class, 'insertFeatureTicket']);
    Route::get('/insertNotAllowedTicket', [PetShopAdminController::class, 'insertNotAllowedTicket']);
    Route::get('/deleteFeatureTicket', [PetShopAdminController::class, 'deleteFeatureTicket']);
    Route::get('/deleteNotAllowedTicket', [PetShopAdminController::class, 'deleteNotAllowedTicket']);
    Route::get('/fetchTicket', [PetShopAdminController::class, 'fetchTicket']);
    // featureRentals
    // fetchRental
    Route::get('/fetchRental', [PetShopAdminController::class, 'fetchRental']);
    Route::get('/insertFeatureRental', [PetShopAdminController::class, 'insertFeatureRentals']);
    Route::get('/insertNotAllowedRental', [PetShopAdminController::class, 'insertNotAllowedRentals']);
    Route::get('/deleteFeatureRental', [PetShopAdminController::class, 'deleteFeatureRental']);
    Route::get('/deleteNotAllowedRental', [PetShopAdminController::class, 'deleteNotAllowedRental']);

    //tours
    Route::get('/fetchTour', [PetShopAdminController::class, 'fetchTour']);
    Route::get('/insertFeatureTour', [PetShopAdminController::class, 'insertFeatureTour']);
    Route::get('/insertNotAllowedTour', [PetShopAdminController::class, 'insertNotAllowedTour']);
    Route::get('/deleteFeatureTour', [PetShopAdminController::class, 'deleteFeatureTour']);
    Route::get('/deleteNotAllowedTour', [PetShopAdminController::class, 'deleteNotAllowedTour']);
    // update
    Route::post('/updateConfigData', [PetShopAdminController::class, 'updateConfigData']);
    // updateBulkHotel
    Route::post('/updateBulkHotel', [PetShopAdminController::class, 'updateBulkHotel']);
    // updateBulkTicket
    Route::post('/updateBulkTicket', [PetShopAdminController::class, 'updateBulkTicket']);
    // updateBulkMerchandise
    Route::post('/updateBulkMerchandise', [PetShopAdminController::class, 'updateBulkMerchandise']);
    // updateBulkRental
    Route::post('/updateBulkRental', [PetShopAdminController::class, 'updateBulkRental']);
    Route::post('/updateBulkTour', [PetShopAdminController::class, 'updateBulkTour']);
    //email
    Route::get('/showTableAndColumn', [PetShopAdminController::class, 'showTableAndColumn']);
    Route::post('/updateEmail', [PetShopAdminController::class, 'updateEmail']);
    Route::post('/storeEmail', [PetShopAdminController::class, 'storeEmail']);
    Route::delete('/deleteEmail', [PetShopAdminController::class, 'deleteEmail']);
    Route::get('/toggleMenus/{id}', [PetShopAdminController::class, 'hideMenu']);
    Route::get('/getEmail/{id}', [PetShopAdminController::class, 'getEmail']);
    Route::get('/createEmail', [PetShopAdminController::class, 'createEmail']);
    Route::get('/getAllEmail', [PetShopAdminController::class, 'getAllEmail']);
    // getSetting
    Route::get('/getSetting', [PetShopAdminController::class, 'getSetting']);

    Route::post('/adminLogout', [AdminController::class, 'logout']);
});
