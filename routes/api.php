<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\GiftCardsController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MerchandiseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PetShop\AdminController as PetShopAdminController;
use App\Http\Controllers\PetShop\ExperiencesController;
use App\Http\Controllers\PetShop\OrderController as PetShopOrderController;
use App\Http\Controllers\PetShop\PaymentController as PetShopPaymentController;
use App\Http\Controllers\PetShop\ShippingAddressController as PetShopShippingAddressController;
use App\Http\Controllers\PetShop\UserController as PetShopUserController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketSpecialRequestController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\checkAdmin;
use App\Http\Middleware\checkSuperAdmin;
use App\Http\Middleware\DatabaseConnectionOne;
use App\Http\Middleware\DatabaseConnectionTwo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//user
// signup
// success

Route::post('/register', [UserController::class, 'signup']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

Route::get('/activateAccount/{email}', [UserController::class, 'activateAccount']);
Route::middleware([DatabaseConnectionTwo::class, 'auth:sanctum'])->group(function () {
    Route::patch('/updateUserData', [UserController::class, 'updateUserData']);
    Route::post('/changePassword', [UserController::class, 'changePassword']);
    Route::get('/getUserData', [UserController::class, 'getUserData']);
    Route::post('/helpSupportEmail', [HelpCenterController::class, 'sendEmail']);

    // showCertificateDetails
    Route::get('/showCertificateDetails', [CertificateController::class, 'showCertificateDetails']);
    // updateTravelDate
    Route::put('/updateTravelDate/{code}', [CertificateController::class, 'updateTravelDate']);
    // getCertificatePdf
    Route::get('/getCertificatePdf/{code}', [CertificateController::class, 'getCertificatePdf']);

    // addToCartData
    Route::post('/procceedToCheckout', [PaymentController::class, 'procceedToCheckout']);
    Route::post('/success', [PaymentController::class, 'success']);
    Route::post('/initiatePayment', [PaymentController::class, 'initiatePayment']);


    // GiftCard
    Route::get('/giftcards/{code}',             [GiftCardsController::class, 'check']);
    Route::post('/giftcards/{code}/register',   [GiftCardsController::class, 'register']);
    Route::post('/giftcards/{code}/redeem',     [GiftCardsController::class, 'redeem']);
    // index
    Route::get('/giftcards',     [GiftCardsController::class, 'index']);

    Route::post('/shippingAddress', [ShippingAddressController::class, 'shippingAddress']);
    Route::get('/getAllShippingAddress', [ShippingAddressController::class, 'getAllShippingAddress']);
    Route::get('/getShippingAddress/{id}', [ShippingAddressController::class, 'getShippingAddress']);
    Route::post('/updateExistingShippingAddress/{id}', [ShippingAddressController::class, 'updateExistingShippingAddress']);
    Route::get('/saveExistingShippingAddress/{id}', [ShippingAddressController::class, 'saveExistingShippingAddress']);
    Route::get('/deleteShippingAddress/{id}', [ShippingAddressController::class, 'deleteShippingAddress']);
    Route::get('/encryptedKey', [UserController::class, 'encryptedKey']);
    Route::get('/getOrderHistory', [OrderController::class, 'getOrderHistory']);
    Route::post('/addGuest/{code}', [GuestController::class, 'addGuest']);
    Route::get('/getGuest/{code}', [GuestController::class, 'getGuest']);
    Route::put('/updateGuest/{code}/{id}', [GuestController::class, 'updateGuest']);
    Route::delete('/deleteGuest/{code}/{id}', [GuestController::class, 'deleteGuest']);

    Route::post('/sendOTPMail/{code}', [CertificateController::class, 'sendOTPMail']);
    Route::post('/transferCertificate', [CertificateController::class, 'transferCertificate']);
});
Route::post('/checkExist', [UserController::class, 'checkExist']);
Route::get('/getCertificatePdf/{code}', [CertificateController::class, 'getCertificatePdf']);
// sendActivationMail
Route::get('/sendActivationMail/{email}', [UserController::class, 'sendActivationMail']);
Route::post('/ticketSpecialRequest', [TicketSpecialRequestController::class, 'sendRequestMail']);
Route::post('/sendInvoice', [InvoiceController::class, 'sendInvoice']);
Route::post('/changeResetPassword/{email}', [UserController::class, 'changeResetPassword']);
Route::get('/resetPasswordEmail', [UserController::class, 'sendResetPasswordLink']);
//countries
Route::get('/countries', [CountryController::class, 'getCountries']);
//experience
Route::get('/experiences', [ExperienceController::class, 'experience']);
Route::get('/categories', [ExperienceController::class, 'categories']);
Route::get('/categories/{endpoint}', [ExperienceController::class, 'getExperiences']);
Route::get('/locations/{endpoint}/{search?}', [ExperienceController::class, 'getExperiencesLocation']);
Route::get('/experiences/details/{sku}', [ExperienceController::class, 'getExperienceDetails']);
Route::get('/travels/{sku}', [ExperienceController::class, 'getExperienceDetails']);
Route::get('/globalSearch', [ExperienceController::class, 'globalSearch']);
//tickets
Route::get('/tickets', [TicketController::class, 'listTicket']);
Route::get('/findTicket', [TicketController::class, 'searchTicket']);
Route::get('/searchTicketPerformerVenues', [TicketController::class, 'searchTicketPerformerVenues']);
Route::get('/searchTicketCities', [TicketController::class, 'searchTicketCities']);
Route::get('/eventListTicket', [TicketController::class, 'searchlistTicket']);
Route::get('/eventTicketCitiesDates', [TicketController::class, 'searchTicketCitiesDates']);
Route::get('/searchFilter', [TicketController::class, 'searchFilter']);
Route::get('/filterTicket', [TicketController::class, 'filterTicket']);
Route::get('/buyTicket', [TicketController::class, 'buyTicket']);
Route::get('/unHoldTicket', [TicketController::class, 'unHoldTicket']);
Route::get('/tickets/categories/{category}', [TicketController::class, 'listTicketByCategory']);
Route::get('/tickets/performers/{performer}', [TicketController::class, 'listTicketByPerformer']);
Route::post('/getTicketTax', [TicketController::class, 'getTicketTax']);
Route::get('/checkTicketAvailability', [TicketController::class, 'checkTicketAvailability']);


//hotels
Route::get('hotels_locations', [HotelController::class, 'getLocations']);
Route::get('/hotels', [HotelController::class, 'hotel']);
Route::get('hotels/{city}/{checkin}/{checkout}/{rooms}/{adults}/{childs}/? {queryString} /{nationality}', [HotelController::class, 'searchHotels']);
Route::get('/searchHotel', [HotelController::class, 'searchHotel']);
Route::get('/searchHotelOne', [HotelController::class, 'searchHotelOne']);
Route::post('/reservationRoom', [HotelController::class, 'selectRoom']);
Route::get('/viewMore', [HotelController::class, 'viewMore']);
Route::get('hotelSearch', [HotelController::class, 'hotelSearch']);
Route::get('/hotels-lists', [HotelController::class, 'listHotels']);

//merchandise
Route::get('/searchMerchandise', [MerchandiseController::class, 'searchMerchandise']);
Route::get('/afterSearchMerchandise', [MerchandiseController::class, 'afterSearchMerchandise']);
Route::get('/merchandiseDetails', [MerchandiseController::class, 'merchandiseDetails']);
Route::get('/merchandise', [MerchandiseController::class, 'listmerchandise']);
Route::get('/getGrandParent', [MerchandiseController::class, 'getGrandParent']);
Route::get('/merchandise-category/{category_id}', [MerchandiseController::class, 'getParent']);
Route::get('/merchandise-product/{category_id}', [MerchandiseController::class, 'getProduct']);
Route::get('/searchGrandParent/{searchTerm}', [MerchandiseController::class, 'searchGrandParent']);
Route::get('/searchParent/{searchTerm}', [MerchandiseController::class, 'searchParent']);
Route::get('/searchProduct/{searchTerm}', [MerchandiseController::class, 'searchProduct']);
//tours
Route::get('/tours', [TourController::class, 'featureDestination']);
Route::get('/getAttraction', [TourController::class, 'getAttraction']);
Route::get('/searchListTour', [TourController::class, 'searchListTour']);
Route::post('/afterSearchTour', [TourController::class, 'afterSearchTour']);
Route::get('/getTourDetails/{id}', [TourController::class, 'getTourDetails']);
Route::get('/filterTours', [TourController::class, 'filterTours']);
Route::get('/getTags', [TourController::class, 'getTags']);
Route::post('/checkAvailability/{tour_id}', [TourController::class, 'checkAvailability']);
Route::post('/holdTours', [TourController::class, 'holdTours']);

//rentals
Route::get('/home-villas', [RentalController::class, 'featureRentals']);
Route::get('/viewMoreRentals', [RentalController::class, 'viewMoreRentals']);
Route::post('/reservationCall', [RentalController::class, 'reservationCall']);
Route::get('/getFilterRental', [RentalController::class, 'getFilterRental']);
Route::get('/afterFilterRental', [RentalController::class, 'afterFilter']);
Route::get('/afterSearchArrayRental', [RentalController::class, 'afterSearchArrayRental']);
Route::get('/searchRental', [RentalController::class, 'searchRental']);
Route::get('/afterSearchRental', [RentalController::class, 'afterSearch']);

Route::get('/getMenus', [AdminController::class, 'getMenu']);
Route::get('/getBranding', [AdminController::class, 'getBranding']);
//Payment Charge
Route::get('/getPaymentCharges', [PetShopAdminController::class, 'getPaymentCharges']);
Route::get('/getPaymentCharges/{type}', [PetShopAdminController::class, 'getPaymentChargesByType']);

// locations
Route::post('/locations', [TourController::class, 'locations']);
//admin
Route::prefix('admin')->group(function () {
    Route::put('/updatePaymentCharges', [PetShopAdminController::class, 'updatePaymentCharges']);
    Route::post('/updateBranding/{id}', [AdminController::class, 'updateBranding']);
    Route::get('/toggleMenus/{id}', [AdminController::class, 'hideMenu']);
    Route::get('/getSetting', [AdminController::class, 'getSetting']);
    Route::post('/updateConfigData', [AdminController::class, 'updateConfigData']);
    Route::post('/adminLogout', [AdminController::class, 'logout']);
    Route::get('/showTableAndColumn', [AdminController::class, 'showTableAndColumn']);
    Route::patch('/updateEmail', [AdminController::class, 'updateEmail']);
    Route::get('/getEmail/{id}', [AdminController::class, 'getEmail']);
    Route::get('/getAllEmail', [AdminController::class, 'getAllEmail']);
    Route::get('/getUsers', [AdminController::class, 'getUsers']);
    Route::post('/createAdmin', [AdminController::class, 'createAdmin']);
    Route::get('/removeAdmin', [AdminController::class, 'removeAdmin']);
    Route::get('/admin-list', [AdminController::class, 'getAdmin']);
    //Payment Charge
    Route::post('/editPaymentCharges', [AdminController::class, 'updateChargeAndStatus']);
});
Route::post('/adminLogin', [AdminController::class, 'login']);

