<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$string_pattern = "/^[0-9가-힣a-zA-Z]+$/";
$number_pattern = "/^[0-9]+$/";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "createJwt":
            // jwt 유효성 검사
            http_response_code(200);

            if(!isValidUser($req->userIdx, $req->password)){
                $res->isSuccess = FALSE;
                $res->code = 100;
                $res->message = "유효하지 않은 아이디 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $jwt = getJWToken($req->userIdx, $req->password, JWT_SECRET_KEY); //jwt발급
            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $res->result = $jwt;
            //$res->userIdx = $data->userIdx;
            //$res->password = $data->password;
            //$res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "토큰 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //User
        case "getUsers":
            http_response_code(200);

            //getUsers를 keyword가 있는 걸로
            $keyword = $_GET['keyword'];
            $keyword = iconv('utf-8', 'utf-8', $keyword);

            if(!isValidName($keyword)) {
                $res->isSuccess = FALSE;
                $res->code = 415;
                $res->message = "해당 사용자는 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getUsers($keyword);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "사용자 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserDetail":
            http_response_code(200);
            //유효하지 않은 parse variable이 들어왔을 때 튕겨내는 작업
            $userIdx = $vars['userIdx'];

            if(!isValidIdx($userIdx)) { //해당 userIdx가 있는지 없는지 검사 -> variable 검사
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //$vars=""; 빨간줄을 위한 초기화 작업 생략 가능
            $res->result = getUserDetail($vars["userIdx"]);
            //빨간줄 : php version에 따라 에러 -> 초기화하면 되지만 안해도 됨
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "사용자 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //body가서 post로 정해주고 raw에 json 형태로 정해줌
        //postman에서 정해주면 packet안에 body안에 넣어져 들어감
        //body안에 value를 어떻게 끌어내는지
        //validation 다 해줘야함 -> 이미 있는 유저인지

        case "createUser":
            http_response_code(200);

            $password = isset($req->password) ? $req->password : null;
            $userProfileUrl = isset($req->userProfileUrl) ? $req->userProfileUrl : null;
            $userName = isset($req->userName) ? $req->userName : null;
            $userNum = isset($req->userNum) ? $req->userNum : null;
            $userPhoneNum = isset($req->userPhoneNum) ? $req->userPhoneNum : null;
            $userLocation = isset($req->userLocation) ? $req->userLocation : null;

            if($password == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "password가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($userName == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "userName이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($userNum == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "userNum이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($userPhoneNum == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "userPhoneNum이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($userLocation == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "userLocation이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!preg_match("/^[0-9a-zA-Z]{8,}+$/", $password)) {
                $res->isSuccess = FALSE;
                $res->code = 433;
                $res->message = "password는 숫자, 영문자(대/소)만 포함 할 수 있으면 8자 이상이여야 됩니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($userProfileUrl != null && !preg_match($string_pattern, $userProfileUrl)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "userProfileUrl는 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match($string_pattern, $userName)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "userName은 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($userNum)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "userNum은 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match("/^[0-9]{11,11}+$/", $userPhoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 434;
                $res->message = "userPhoneNum은 String형태에서 숫자로 11자 입력 되어야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($userLocation)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "userLocation은 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($userNum > 999999999) {
                $res->isSuccess = FALSE;
                $res->code = 407;
                $res->message = "회원번호는 9자리 이하여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isValidNum($userNum)) {
                $res->isSuccess = FALSE;
                $res->code = 408;
                $res->message = "이미 사용되고 있는 사용자 번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isValidPhoneNum($userPhoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 409;
                $res->message = "해당 휴대폰으로 이미 사용중인 계정이 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createUser($password, $userProfileUrl, $userName, $userNum, $userPhoneNum, $userLocation);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "사용자 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createUserManner":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $targetIdx = (int)$data->id;

            $targetIdx = isset($req->targetIdx) ? $req->targetIdx : null;
            $mannerIdx = isset($req->mannerIdx) ? $req->mannerIdx : null;

            if($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "targetIdx가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($mannerIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "mannerIdx가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "targetIdx는 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($mannerIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "mannerIdx는 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidMannerIdx($mannerIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "유효하지 않은 매너 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createUserManner($targetIdx, $mannerIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "사용자 매너 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "updateUser" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $targetIdx = (int)$data->userIdx;

            $newProfileUrl = isset($rep->newProfileUrl) ? $req->newProfileUrl : null;
            $newName = isset($req->newName) ? $req->newName : null;

            if($newProfileUrl != null && !preg_match($string_pattern, $newProfileUrl)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "userProfileUrl는 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newName == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newName가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!preg_match($string_pattern, $newName)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "newName은 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = updateUserName($newName, $targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "사용자 프로필 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteUser" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) { // function에 정의
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isDeletedIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 425;
                $res->message = "이미 삭제된 사용자 인덱스 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = deleteUser($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "사용자 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //Product
//        case "getProducts":
//            http_response_code(200);
//
//            $res->result = getProducts();
//            $res->isSuccess = TRUE;
//            $res->code = 200;
//            $res->message = "상품 목록 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;


        case "getProducts":
            http_response_code(200);

            $keyword = $_GET['keyword'];
            $keyword = iconv('utf-8', 'utf-8', $keyword);

            if(!isValidProductTitle($keyword)) {
                $res->isSuccess = FALSE;
                $res->code = 416;
                $res->message = "해당 상품은 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getProducts($keyword);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProductsByCategory":
            http_response_code(200);

            $productCategoryIdx = $vars['productCategoryIdx'];

            if(!isValidProductCategory($productCategoryIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "유효하지 않은 상품 카테고리 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isProductInCategory($productCategoryIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 417;
                $res->message = "카테고리에 해당하는 상품이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getProductsByCategory($productCategoryIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 카테고리로 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProductDetail":
            http_response_code(200);

            $productIdx = $vars['productIdx'];

            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getProductDetail($productIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createProduct":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            $productTitle = isset($req->productTitle) ? $req->productTitle : null;
            $productCategoryIdx = isset($req->productCategoryIdx) ? $req->productCategoryIdx : null;
            $productPrice = isset($req->productPrice) ? $req->productPrice : null;
            $productSuggestPrice = isset($req->productSuggestPrice) ? $req->productSuggestPrice : 1;
            $productContent = isset($req->productContent) ? $req->productContent : null;

            if($productTitle == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productTitle이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productCategoryIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productCategoryIdx가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productPrice == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productPrice가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productContent == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productContent가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_integer($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($productTitle)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($productCategoryIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($productPrice)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productSuggestPrice != 0 && $productSuggestPrice != 1) {
                $res->isSuccess = FALSE;
                $res->code = 435;
                $res->message = "productSuggestPrice는 0 또는 1 이여야 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($productContent)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidProductCategory($productCategoryIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "유효하지 않은 상품 카테고리 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productPrice > 999999999) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "상품 가격은 1000000000 미만이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createProduct($userIdx, $productTitle, $productCategoryIdx, $productPrice, $productSuggestPrice, $productContent);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createProductReview":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            $reviewContent = isset($req->reviewContent) ? $req->reviewContent : null;
            $productReviewImgUrl = isset($req->productReviewImgUrl) ? $req->productReviewImgUrl : null;

            if($reviewContent == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "reviewContent가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_integer($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($reviewContent)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productReviewImgUrl != null && !preg_match($string_pattern, $productReviewImgUrl)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "productReviewImgUrl은 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $productIdx = $vars['productIdx'];

            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isReviewExist($userIdx, $productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 422;
                $res->message = "해당 사용자에 대한 후기가 이미 존재 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = createProductReview($userIdx, $productIdx, $reviewContent, $productReviewImgUrl);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 후기 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createProductChat":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            $productChatContent = isset($req->productChatContent) ? $req->productChatContent : null;
            if($productChatContent == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productChatContent가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_integer($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($req->productChatContent)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "productChatContent는 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $productIdx = $vars['productIdx'];
            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createProductChat($productIdx, $userIdx, $productChatContent);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 채팅 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createProductInterest":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            if(!is_integer($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $productIdx = $vars['productIdx'];
            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isInterestExist($userIdx, $productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "해당 상품에 대한 사용자 관심이 있습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createProductInterest($userIdx, $productIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 관심 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "updateProduct" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $newProductTitle = isset($req->newProductTitle) ? $req->newProductTitle : null;
            $newProductCategoryIdx = isset($req->newProductCategoryIdx) ? $req->newProductCategoryIdx : null;
            $newProductPrice = isset($req->newProductPrice) ? $req->newProductPrice : null;
            $newProductPriceSuggest = isset($req->newProductPriceSuggest) ? $req->newProductPriceSuggest : null;
            $newProductContent = isset($req->newProductContent) ? $req->newProductContent : null;
            $targetIdx = isset($req->targetIdx) ? $req->targetIdx : null;

            if($newProductTitle == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newProductTitle가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductCategoryIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newProductCategoryIdx가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductPrice == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newProductPrice가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductPriceSuggest == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newProductPriceSuggest가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductContent == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "newProductContent가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "targetIdx가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!is_string($newProductTitle)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "newProductTitle가 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($newProductCategoryIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "productCategoryIdx가 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($newProductPrice)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "newProductPrice가 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductPriceSuggest != 0 && $newProductPriceSuggest != 1) {
                $res->isSuccess = FALSE;
                $res->code = 418;
                $res->message = "newProductPriceSuggest가 bool(true/false) 이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($newProductContent)) {
                $res->isSuccess = FALSE;
                $res->code = 431;
                $res->message = "newProductContent가 String 이여야합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "targetIdx가 Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProductIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($newProductPrice > 999999999) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "상품 가격은 1000000000 미만이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = updateProduct($newProductTitle, $newProductCategoryIdx, $newProductPrice, $newProductPriceSuggest, $newProductContent, $targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "updateProductState" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $productInfoState = isset($req->productInfoState) ? $req->productInfoState : null;

            if($productInfoState == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productInfoState가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $targetIdx = $vars['productIdx'];
            if(!isValidProductIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->productInfoState != 0 && $req->productInfoState != 1) {
                $res->isSuccess = FALSE;
                $res->code = 418;
                $res->message = "상품 상태(숨김) 여부는 0 또는 1 이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = updateProductState($req->productInfoState, $targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 상태 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "updateProductSoldState" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $productSoldState = isset($req->productSoldState) ? $req->productSoldState : null;

            if($productSoldState == null) {
                $res->isSuccess = FALSE;
                $res->code = 432;
                $res->message = "productSoldState가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $targetIdx = $vars['productIdx'];

            if(!isValidProductIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($productSoldState != 0 && $productSoldState != 1) {
                $res->isSuccess = FALSE;
                $res->code = 418;
                $res->message = "productSoldState는 0 또는 1 이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = updateProductSoldState($productSoldState, $targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 거래 상태 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "updateProductPullUp" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $targetIdx = $vars['productIdx'];

            if(!isValidProductIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = updateProductPullUp($targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "상품 끌올 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteProduct" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            $productIdx = $vars['productIdx'];

            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isDeletedProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 426;
                $res->message = "이미 삭제된 상품 인덱스 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = deleteProduct($productIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteInterest" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            $productIdx = $vars['productIdx'];

            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isInterestExist($userIdx, $productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "해당 상품에 대한 사용자 관심이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isDeletedInterest($userIdx, $productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 427;
                $res->message = "이미 삭제된 관심입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = deleteInterest($userIdx, $productIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 관심 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //Chat
//        case "getChats":
//            http_response_code(200);
//
//            if(!isValidIdx($userIdx)) {
//                $res->isSuccess = FALSE;
//                $res->code = 406;
//                $res->message = "유효하지 않은 사용자 인덱스입니다.";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            $res->result = getChats($userIdx);
//            $res->isSuccess = TRUE;
//            $res->code = 200;
//            $res->message = "사용자 채팅 목록 조회 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;

        case "getChatDetail":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $chatRoomIdx = $vars['chatRoomIdx'];

            if(!isValidChatRoomIdx($chatRoomIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 414;
                $res->message = "유효하지 않은 채팅방 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getChatDetail($chatRoomIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "사용자 채팅 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteChatRoom" :
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $chatRoomIdx = $vars['chatRoomIdx'];

            if(!isValidChatRoomIdx($chatRoomIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 414;
                $res->message = "유효하지 않은 채팅방 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isDeletedChatRoomIdx($chatRoomIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 428;
                $res->message = "이미 삭제된 채팅방 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = deleteChatRoom($chatRoomIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "채팅방 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //Review
        case "getReviews":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isReviewExistWithUserIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 419;
                $res->message = "해당 사용자에 대한 후기가 존재하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getReviews($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "사용자 후기 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteReview" :
            http_response_code(200);

            $reviewIdx = $vars['reviewIdx'];

            if(!isReviewExistWithReviewIdx($reviewIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 421;
                $res->message = "유효하지 않은 후기 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isDeletedReviewIdx($reviewIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 429;
                $res->message = "이미 삭제된 리뷰 인덱스 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = deleteReview($reviewIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "상품 후기 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "suggestPrice":
            http_response_code(200);

            $jwt = isset($_SERVER["HTTP_X_ACCESS_TOKEN"]) ? $_SERVER["HTTP_X_ACCESS_TOKEN"] : null;

            if($jwt == null) {
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "토큰이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 토큰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $userIdx = (int)$data->userIdx;

            if(!is_integer($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_integer($req->suggestedPrice)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "Int 여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $productIdx = $vars['productIdx'];
            if(!isValidProductIdx($productIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "유효하지 않은 상품 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->suggestedPrice > 999999999) {
                $res->isSuccess = FALSE;
                $res->code = 423;
                $res->message = "상품 제안 가격은 1000000000 미만이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(priceAlreadySuggested($productIdx, $userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 424;
                $res->message = "이미 가격 제안을 1회 했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = suggestPrice($productIdx, $userIdx, $req->suggestedPrice);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "가격제안 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "blockedUser" :
            http_response_code(200);

            if(!isValidIdx($req->userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidIdx($req->targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 406;
                $res->message = "유효하지 않은 차단 사용자 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = blockedUser($req->userIdx, $req->targetIdx);

            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "사용자 차단 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        //Service
        case "getServices":
            http_response_code(200);

            $keyword = $_GET['keyword'];

            if(!isValidServiceName($keyword)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServices($keyword);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getServiceDetail":
            http_response_code(200);

            $serviceIdx = $vars['serviceIdx'];

            if(!isValidServicetIdx($serviceIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않은 서비스 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServiceDetail($vars["serviceIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 상세 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getServiceImage":
            http_response_code(200);

            $serviceIdx = $vars['serviceIdx'];

            if(!isValidServicetIdx($serviceIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않은 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServiceImage($vars["serviceIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 이미지 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getServicePrice":
            http_response_code(200);

            $serviceIdx = $vars['serviceIdx'];

            if(!isValidServicetIdx($serviceIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않은 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServicePrice($vars["serviceIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 가격표 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getServiceToDo":
            http_response_code(200);

            $serviceIdx = $vars['serviceIdx'];

            if(!isValidServicetIdx($serviceIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않은 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServiceToDo($vars["serviceIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 하는 일 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getServiceReview":
            http_response_code(200);

            $serviceIdx = $vars['serviceIdx'];

            if(!isValidServicetIdx($serviceIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않은 서비스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getServiceReview($vars["serviceIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 후기 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createService":
            http_response_code(200);

//            $userIdx = $vars['userIdx'];
//
//            if(!isValidIdx($userIdx)) {
//                $res->isSuccess = FALSE;
//                $res->code = 401;
//                $res->message = $userIdx;
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }

            $res->result = createService($req->userIdx, $req->serviceName, $req->servicePhoneNo, $req->serviceCategory, $req->serviceContent,
                $req->serviceLocation, $req->serviceLatitude, $req->serviceLongitude, $req->serviceAddress, $req->serviceSubAddress,
                $req->serviceOpenTime, $req->serviceLink, $req->serviceLicense);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "서비스 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
