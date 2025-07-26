<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Index');
$routes->setDefaultMethod('main');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Index::response404');
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->post('/', 'Index::index');
$routes->get('/', 'Index::main');
$routes->get('/logoutPage', 'Index::main', ['as' => 'logoutPage']);
$routes->get('/loginPage', 'Index::loginPage');
$routes->post('/mainPage', 'Index::mainPage', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('access/check', 'Access::check');
$routes->post('access/login', 'Access::login', ['filter' => 'auth:mustNotBeLoggedIn']);
$routes->get('access/roketRedirect/(:any)', 'Access::roketRedirect/$1');
$routes->get('access/logout/(:any)', 'Access::logout/$1');
$routes->get('access/captcha/(:any)', 'Access::captcha/$1');
$routes->post('access/getDataOption', 'Access::getDataOption', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/getDataOptionByKey/(:any)/(:any)/(:any)', 'Access::getDataOptionByKey/$1/$2/$3', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/detailProfileSetting', 'Access::detailProfileSetting', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/saveDetailProfileSetting', 'Access::saveDetailProfileSetting', ['filter' => 'auth:mustBeLoggedIn']);

$routes->get('cron/execChatCron', 'Cron::execChatCron');
$routes->post('webhook/whatsapp/oneMsgIO', 'Webhook::whatsappOneMsgIO');
$routes->post('webhook/whatsapp/handleHuman', 'Webhook::handleHuman');
$routes->post('webhook/whatsapp/getSignature', 'Webhook::getSignature');

$routes->post('view/chat', 'View::chat', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/contact', 'View::contact', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/user-level-menu', 'View::userLevelMenu', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/user-admin', 'View::userAdmin', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/system-setting', 'View::systemSetting', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('chat/getDataChatList', 'Chat::getDataChatList', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/getDetailChat', 'Chat::getDetailChat', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/getMoreChatThread', 'Chat::getMoreChatThread', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/getDetailThreadACK', 'Chat::getDetailThreadACK', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/sendMessage', 'Chat::sendMessage', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/updateUnreadMessageCount', 'Chat::updateUnreadMessageCount', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/setMarkAsUnread', 'Chat::setMarkAsUnread', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/setActiveHandleStatus', 'Chat::setActiveHandleStatus', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/getDetailReservation', 'Chat::getDetailReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('chat/saveReservation', 'Chat::saveReservation', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('contact/getDataContact', 'Contact::getDataContact', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('contact/saveContact', 'Contact::saveContact', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('contact/getDetailContact', 'Contact::getDetailContact', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('contact/sendTemplateMessage', 'Contact::sendTemplateMessage', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('userLevelMenu/getDataLevel', 'Settings\UserLevelMenu::getDataLevel', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userLevelMenu/getMenuLevelAdmin', 'Settings\UserLevelMenu::getMenuLevelAdmin', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userLevelMenu/addLevelAdmin', 'Settings\UserLevelMenu::addLevelAdmin', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userLevelMenu/saveLevelMenu', 'Settings\UserLevelMenu::saveLevelMenu', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('userAdmin/getDataUserAdmin', 'Settings\UserAdmin::getDataUserAdmin', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userAdmin/saveUserAdmin', 'Settings\UserAdmin::saveUserAdmin', ['filter' => 'auth:mustBeLoggedIn']);
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
