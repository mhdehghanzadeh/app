<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ConsultantsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\CounselingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\AppointmentsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

//Home    
Route::get('/', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('guest');

// Auth
#Admin Form Login
Route::get('auth/admin-login', [AuthenticatedSessionController::class, 'admin'])
    ->name('auth')
    ->middleware('guest');

#Verify Admin And User with user pass 
Route::post('auth/verify', [AuthenticatedSessionController::class, 'store'])
    ->name('auth.verify')
    ->middleware('guest');

#User Login
Route::get('auth/login', [AuthenticatedSessionController::class, 'user'])
    ->name('login')
    ->middleware('guest');

#Get Phone and find user
Route::post('auth/login', [AuthenticatedSessionController::class, 'login'])
    ->name('auth.attempt')
    ->middleware('guest');

#Show OTP Or Password Form 
Route::get('auth/verify', [AuthenticatedSessionController::class, 'verify'])
    ->name('login.verify')
    ->middleware('guest', 'checksession');


    
#Verify OTP
Route::post('auth/otpverify', [AuthenticatedSessionController::class, 'otp_verify'])
    ->name('auth.otp.verify')
    ->middleware('guest', 'checksession');

#Reset
Route::delete('auth/reset', [AuthenticatedSessionController::class, 'destroy'])
    ->name('auth.reset')
    ->middleware('guest');

#Logout

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

#Password

Route::get('/auth/forgot-password', [AuthenticatedSessionController::class, 'forgotForm'])
    ->middleware('guest')
    ->name('password.forgot');

Route::post('/auth/forgot-password', [AuthenticatedSessionController::class, 'forgotPassword'])
    ->middleware('guest')
    ->name('password.submit');

Route::get('/auth/reset-password/{token}', [AuthenticatedSessionController::class, 'resetForm'])
    ->middleware('guest', 'checktoken')
    ->name('password.reset');

Route::post('/auth/reset-password', [AuthenticatedSessionController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

Route::get('change-password', [ProfileController::class, 'change_password'])
    ->middleware('auth')
    ->name('password.change');

Route::post('change-password', [ProfileController::class, 'update_Password'])
    ->middleware('auth')
    ->name('password.change.update');

// Dashboard

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// Profile
Route::get('profile/edit', [ProfileController::class, 'edit'])
    ->name('profile.edit')
    ->middleware('auth');

Route::put('profile/update', [ProfileController::class, 'update'])
    ->name('profile.update')
    ->middleware('auth');

// Users

Route::get('users', [UsersController::class, 'index'])
    ->name('users')
    ->middleware('auth', 'acl:users.show');

Route::get('users/create', [UsersController::class, 'create'])
    ->name('users.create')
    ->middleware('auth', 'acl:users.create');

Route::post('users', [UsersController::class, 'store'])
    ->name('users.store')
    ->middleware('auth', 'acl:users.create');

Route::get('users/{user}/edit', [UsersController::class, 'edit'])
    ->name('users.edit')
    ->middleware('auth', 'acl:users.edit');

Route::put('users/{user}', [UsersController::class, 'update'])
    ->name('users.update')
    ->middleware('auth', 'acl:users.edit');

Route::delete('users/{user}', [UsersController::class, 'destroy'])
    ->name('users.destroy')
    ->middleware('auth', 'acl:users.destroy');

Route::put('users/{user}/restore', [UsersController::class, 'restore'])
    ->name('users.restore')
    ->middleware('auth', 'acl:users.restore');

// Roles

Route::get('roles', [RolesController::class, 'index'])
    ->name('roles')
    ->middleware('auth', 'acl:roles.show');

Route::get('roles/create', [RolesController::class, 'create'])
    ->name('roles.create')
    ->middleware('auth', 'acl:roles.create');

Route::post('roles', [RolesController::class, 'store'])
    ->name('roles.store')
    ->middleware('auth', 'acl:roles.create');

Route::get('roles/{role}/edit', [RolesController::class, 'edit'])
    ->name('roles.edit')
    ->middleware('auth', 'acl:roles.edit');

Route::put('roles/{role}', [RolesController::class, 'update'])
    ->name('roles.update', 'acl:roles.edit')
    ->middleware('auth');

Route::delete('roles/{role}', [RolesController::class, 'destroy'])
    ->name('roles.destroy')
    ->middleware('auth', 'acl:roles.destroy');

Route::put('roles/{role}/restore', [RolesController::class, 'restore'])
    ->name('roles.restore')
    ->middleware('auth', 'acl:roles.restore');

// Permission

Route::get('permissions', [PermissionsController::class, 'index'])
    ->name('permissions')
    ->middleware('auth', 'acl:permissions.show');

Route::get('permissions/create', [PermissionsController::class, 'create'])
    ->name('permissions.create')
    ->middleware('auth', 'acl:permissions.create');

Route::post('permissions', [PermissionsController::class, 'store'])
    ->name('permissions.store')
    ->middleware('auth', 'acl:permissions.create');

Route::get('permissions/{permission}/edit', [PermissionsController::class, 'edit'])
    ->name('permissions.edit')
    ->middleware('auth', 'acl:permissions.edit');

Route::put('permissions/{permission}', [PermissionsController::class, 'update'])
    ->name('permissions.update')
    ->middleware('auth', 'acl:permissions.edit');

Route::delete('permissions/{permission}', [PermissionsController::class, 'destroy'])
    ->name('permissions.destroy')
    ->middleware('auth', 'acl:permissions.destroy');

// Consultants

Route::get('consultants', [ConsultantsController::class, 'index'])
    ->name('consultants')
    ->middleware('auth', 'acl:consultants.show');

Route::get('consultants/{consultant}/show', [ConsultantsController::class, 'show'])
    ->name('consultants.show')
    ->middleware('auth', 'acl:consultants.show');

Route::get('consultants/create', [ConsultantsController::class, 'create'])
    ->name('consultants.create')
    ->middleware('auth', 'acl:consultants.create');

Route::post('consultants', [ConsultantsController::class, 'store'])
    ->name('consultants.store')
    ->middleware('auth', 'acl:consultants.create');

Route::get('consultants/{consultant}/edit', [ConsultantsController::class, 'edit'])
    ->name('consultants.edit')
    ->middleware('auth', 'acl:consultants.edit');

Route::put('consultants/{consultant}', [ConsultantsController::class, 'update'])
    ->name('consultants.update', 'acl:consultants.edit')
    ->middleware('auth');

Route::delete('consultants/{consultant}', [ConsultantsController::class, 'destroy'])
    ->name('consultants.destroy')
    ->middleware('auth', 'acl:consultants.destroy');

Route::put('consultants/{consultant}/restore', [ConsultantsController::class, 'restore'])
    ->name('consultants.restore')
    ->middleware('auth', 'acl:consultants.restore');

// Contacts

Route::get('contacts', [ContactsController::class, 'index'])
    ->name('contacts')
    ->middleware('auth', 'acl:contacts.show');

Route::get('contacts/{contact}/show', [ContactsController::class, 'show'])
    ->name('contacts.show')
    ->middleware('auth', 'acl:contacts.show');

Route::get('contacts/{contact}/edit', [ContactsController::class, 'edit'])
    ->name('contacts.edit')
    ->middleware('auth', 'acl:contacts.edit');

Route::put('contacts/{contact}', [ContactsController::class, 'update'])
    ->name('contacts.update')
    ->middleware('auth', 'acl:contacts.edit');

Route::put('contact_images/{contact}', [ContactsController::class, 'update_images'])
    ->name('contact.images.update')
    ->middleware('auth', 'acl:contacts.edit');

Route::delete('contact_images/{contact_image}', [ContactsController::class, 'destroy_images'])
    ->name('contact.images.destroy')
    ->middleware('auth', 'acl:tickets');

// Counselings

Route::get('counselings', [CounselingsController::class, 'index'])
    ->name('counselings')
    ->middleware('auth', 'acl:counselings.show');

Route::get('counselings/{counseling}', [CounselingsController::class, 'show'])
    ->name('counselings.show')
    ->middleware('auth', 'acl:counselings.show');

Route::put('counselings/{counseling}', [CounselingsController::class, 'update'])
    ->name('counselings.update')
    ->middleware('auth', 'acl:counselings.edit');

#user

Route::get('counseling', [CounselingsController::class, 'contact_index'])
    ->name('counselings.contact')
    ->middleware('auth');

Route::get('counseling/{counseling}', [CounselingsController::class, 'contact_show'])
    ->name('counselings.contact.show')
    ->middleware('auth', 'checkprofile');

// Payments

Route::get('payments', [PaymentsController::class, 'index'])
    ->name('payments')
    ->middleware('auth', 'acl:payments.show');

Route::get('payments/{payment}/show', [PaymentsController::class, 'show'])
    ->name('payments.show')
    ->middleware('auth', 'acl:payments.show');

#user

Route::post('payments/{consultant}', [PaymentsController::class, 'send'])
    ->name('payments.send')
    ->middleware('auth', 'checkprofile');

Route::get('payments/verify', [PaymentsController::class, 'verify'])
    ->name('payments.verify')
    ->middleware('auth', 'checkprofile');

Route::get('payment', [PaymentsController::class, 'contact_index'])
    ->name('payments.contact')
    ->middleware('auth');


// Support and Tickets

#user

Route::get('support', [SupportController::class, 'index'])
    ->name('support')
    ->middleware('auth');

Route::get('support/create', [SupportController::class, 'create'])
    ->name('support.create')
    ->middleware('auth');

Route::post('support', [SupportController::class, 'store'])
    ->name('support.store')
    ->middleware('auth');

Route::get('support/{ticket}', [SupportController::class, 'show'])
    ->name('support.show')
    ->middleware('auth');

Route::post('support/{ticket}/message', [SupportController::class, 'message'])
    ->name('support.message')
    ->middleware('auth');

Route::delete('support/message/{ticket_message}', [SupportController::class, 'messageDestroy'])
    ->name('support.message.destroy')
    ->middleware('auth');


Route::get('help', [TicketsController::class, 'list'])
    ->name('help')
    ->middleware('auth');
    
Route::get('help/create', [TicketsController::class, 'create'])
    ->name('help.create')
    ->middleware('auth');

Route::post('help', [TicketsController::class, 'store'])
    ->name('help.store')
    ->middleware('auth');
    
#admin

Route::get('tickets', [TicketsController::class, 'index'])
    ->name('tickets')
    ->middleware('auth', 'acl:tickets');

Route::get('tickets/{ticket}', [TicketsController::class, 'show'])
    ->name('tickets.show')
    ->middleware('auth', 'acl:tickets');

Route::put('tickets/{ticket}', [TicketsController::class, 'update'])
    ->name('tickets.update', 'acl:tickets')
    ->middleware('auth');

Route::delete('tickets/{ticket}', [TicketsController::class, 'destroy'])
    ->name('tickets.destroy')
    ->middleware('auth', 'acl:tickets');

Route::put('tickets/{ticket}/restore', [TicketsController::class, 'restore'])
    ->name('tickets.restore')
    ->middleware('auth', 'acl:tickets');

Route::post('tickets/{ticket}/message', [TicketsController::class, 'message'])
    ->name('tickets.message')
    ->middleware('auth', 'acl:tickets');

Route::delete('tickets/message/{ticket_message}', [TicketsController::class, 'messageDestroy'])
    ->name('tickets.message.destroy')
    ->middleware('auth', 'acl:tickets');

// Notifications

Route::get('notifications', [NotificationsController::class, 'index'])
    ->name('notifications')
    ->middleware('auth', 'acl:notifications.show');

Route::get('notifications/create', [NotificationsController::class, 'create'])
    ->name('notifications.create')
    ->middleware('auth', 'acl:notifications.create');

Route::post('notifications', [NotificationsController::class, 'store'])
    ->name('notifications.store')
    ->middleware('auth', 'acl:notifications.create');

Route::get('notifications/{notification}/edit', [NotificationsController::class, 'edit'])
    ->name('notifications.edit')
    ->middleware('auth', 'acl:notifications.edit');

Route::put('notifications/{notification}', [NotificationsController::class, 'update'])
    ->name('notifications.update', 'acl:notifications.edit')
    ->middleware('auth');

Route::delete('notifications/{notification}', [NotificationsController::class, 'destroy'])
    ->name('notifications.destroy')
    ->middleware('auth', 'acl:notifications.destroy');

Route::put('notifications/{notification}/restore', [NotificationsController::class, 'restore'])
    ->name('notifications.restore')
    ->middleware('auth', 'acl:notifications.restore');

#user

Route::get('notification', [NotificationsController::class, 'contact_index'])
    ->name('notification')
    ->middleware('auth');

Route::get('notification/{notification}/show', [NotificationsController::class, 'show'])
    ->name('notification.show')
    ->middleware('auth');

// Images

Route::get('/img/{path}', [ImagesController::class, 'show'])
    ->where('path', '.*')
    ->name('image');

Route::post('/upload-image', [ImagesController::class, 'upload'])
    ->name('image.upload')
    ->middleware('auth', 'acl:posts.create');

// Posts

Route::get('posts', [PostsController::class, 'index'])
    ->name('posts')
    ->middleware('auth', 'acl:posts.show');

Route::get('posts/create', [PostsController::class, 'create'])
    ->name('posts.create')
    ->middleware('auth', 'acl:posts.create');

Route::post('posts', [PostsController::class, 'store'])
    ->name('posts.store')
    ->middleware('auth', 'acl:posts.create');

Route::get('posts/{post}/edit', [PostsController::class, 'edit'])
    ->name('posts.edit')
    ->middleware('auth', 'acl:posts.edit');

Route::put('posts/{post}', [PostsController::class, 'update'])
    ->name('posts.update', 'acl:posts.edit')
    ->middleware('auth');

Route::delete('posts/{post}', [PostsController::class, 'destroy'])
    ->name('posts.destroy')
    ->middleware('auth', 'acl:posts.destroy');

Route::put('posts/{post}/restore', [PostsController::class, 'restore'])
    ->name('posts.restore')
    ->middleware('auth', 'acl:posts.restore');

//Wordpress Categories

Route::post('categories', [PostsController::class, 'store_category'])
    ->name('categories.store')
    ->middleware('auth', 'acl:posts.create');


//Wordpress Tags

Route::get('tags/{q}', [PostsController::class, 'search_tag'])
    ->name('tags.search')
    ->middleware('auth', 'acl:posts.create');

Route::post('tags', [PostsController::class, 'store_tag'])
    ->name('tags.store')
    ->middleware('auth', 'acl:posts.create');

//Wordpress Media


Route::post('media', [PostsController::class, 'store_media'])
    ->name('media.store')
    ->middleware('auth', 'acl:posts.create');



//Appointments

Route::get('appointments', [AppointmentsController::class, 'index'])
    ->name('appointments')
    ->middleware('auth', 'acl:posts.show');

#user
Route::get('appointment', [AppointmentsController::class, 'contact_index'])
    ->name('appointments.contact')
    ->middleware('auth');

Route::get('appointment/create', [AppointmentsController::class, 'contact_create'])
    ->name('appointments.contact.create')
    ->middleware('auth', 'checkprofile');

Route::post('appointment', [AppointmentsController::class, 'contact_store'])
    ->name('appointments.contact.store')
    ->middleware('auth', 'checkprofile');

#static pages
Route::get('guide', [DashboardController::class, 'guide'])
    ->name('guide')
    ->middleware('auth');

