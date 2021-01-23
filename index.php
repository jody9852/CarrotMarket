<?php
header("Content-Type:text/html;charset=utf-8");
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
//index.php 파일을 기준으로 라우팅을 해 줌 -> 어떤 api가 어디에 가서 어떤 로직을 수행할지 결정
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']); //get 방식으로 /했을 때 indexController가서 index를 찾아라

    //User
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('POST', '/manner', ['IndexController', 'createUserManner']);
    $r->addRoute('PUT', '/user', ['IndexController', 'updateUser']);
    $r->addRoute('DELETE', '/user', ['IndexController', 'deleteUser']);

    //Product
    $r->addRoute('GET', '/products', ['IndexController', 'getProducts']);
    $r->addRoute('GET', '/productsCategory/{productCategoryIdx}', ['IndexController', 'getProductsByCategory']);
    $r->addRoute('GET', '/products/{productIdx}', ['IndexController', 'getProductDetail']);
    $r->addRoute('POST', '/product', ['IndexController', 'createProduct']);
    $r->addRoute('POST', '/product/{productIdx}/review', ['IndexController', 'createProductReview']);
    $r->addRoute('POST', '/product/{productIdx}/chat', ['IndexController', 'createProductChat']);
    $r->addRoute('POST', '/product/{productIdx}/interest', ['IndexController', 'createProductInterest']);
    $r->addRoute('PUT', '/product', ['IndexController', 'updateProduct']);
    $r->addRoute('PUT', '/product/{productIdx}/state', ['IndexController', 'updateProductState']);
    $r->addRoute('PUT', '/product/{productIdx}/soldState', ['IndexController', 'updateProductSoldState']);
    $r->addRoute('PUT', '/product/{productIdx}/up', ['IndexController', 'updateProductPullUp']);
    $r->addRoute('DELETE', '/product/{productIdx}', ['IndexController', 'deleteProduct']);
    $r->addRoute('DELETE', '/product/{productIdx}/interest', ['IndexController', 'deleteInterest']);

    //chat
    //$r->addRoute('GET', '/user/{userIdx}/chats', ['IndexController', 'getChats']);
    $r->addRoute('GET', '/chat/{chatRoomIdx}', ['IndexController', 'getChatDetail']);
    $r->addRoute('DELETE', '/chat/{chatRoomIdx}', ['IndexController', 'deleteChatRoom']);

    //review
    $r->addRoute('GET', '/reviews', ['IndexController', 'getReviews']);
    $r->addRoute('DELETE', '/review/{reviewIdx}', ['IndexController', 'deleteReview']);

    $r->addRoute('POST', '/product/{productIdx}/price', ['IndexController', 'suggestPrice']);

    $r->addRoute('POST', '/jwt', ['IndexController', 'createJwt']);
//    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);
    $r->addRoute('GET', '/data', ['MainController', 'getDataList']);
//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
    //block user
//    $r->addRoute('POST', '/blocked-user/{userIdx}/{targetIdx}', ['IndexController', 'blockedUser']);

    //Service
//    $r->addRoute('GET', '/services', ['IndexController', 'getServices']);
//    $r->addRoute('GET', '/services/{serviceIdx}', ['IndexController', 'getServiceDetail']);
//    $r->addRoute('GET', '/services/{serviceIdx}/image', ['IndexController', 'getServiceImage']);
//    $r->addRoute('GET', '/services/{serviceIdx}/price', ['IndexController', 'getServicePrice']);
//    $r->addRoute('GET', '/services/{serviceIdx}/todo', ['IndexController', 'getServiceToDo']);
//    $r->addRoute('GET', '/services/{serviceIdx}/news', ['IndexController', 'getServiceNews']);
//    $r->addRoute('GET', '/services/{serviceIdx}/news/{newsIdx}', ['IndexController', 'getServiceNewsDetail']);
//    $r->addRoute('GET', '/services/{serviceIdx}/review', ['IndexController', 'getServiceReview']);
//
//    $r->addRoute('POST', '/service', ['IndexController', 'createService']);
});


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
