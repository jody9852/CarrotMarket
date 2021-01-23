<?php
header("Content-Type:text/html;charset=utf-8");

//실제 쿼리를 넣으면 그 결과 값을 가져오는 부분

function isValidUser($userIdx, $password){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userIdx = ? AND password = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $password]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

//User
function getUsers($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "select userIdx, userName, userNum, userLocation from User where userName like concat('%', ?, '%');";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select u.userIdx, u.userProfileUrl, u.userName, concat('#', u.userNum) as userNum,
                       concat(u.userMannerDegree, '℃') as userMannerDegree,
                       concat(u.recommerceRate, '%') as recommerceRate,
                       concat(u.responseRate, '%') as responseRate,
                       concat(userLocation, ' ', userNeighborConfirmNum, '회 인증') as locationConfirmCnt,
                       concat(date_format(createdAt, '%Y년 %m월 %d일'), '가입') as registerDate,
                       concat('(최근 ', timestampdiff(day, ua.accessTime, now()), '일 이내 활동)') as userState,
                       ub.badgeCnt, pi.productSoldCnt
                from User as u
                    left join (select userIdx, accessTime from UserAccessHistory) ua on u.userIdx = ua.userIdx
                    left join (select userIdx, count(userIdx) as badgeCnt from UserBadge group by userIdx) as ub on u.userIdx = ub.userIdx
                    left join (select userIdx, count(userIdx) as productSoldCnt from ProductInfo group by userIdx) as pi on u.userIdx = pi.userIdx
                where u.userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //list안에 넣어주여아함
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
    //return $res
    //res는 기본적으로 list형태로 반환 index안해주면 그냥 list형태로 반환
    //클라이언트 입장에서 json으로 parsing할 때 data 포맷하는 방향이 달라 주의
    //명세서에 string인지 list인지 체크
    //api 만들때 validation 매우 중요
    //validation : 있지도 않은 것을 요청했을 때 null이나 다른 값을 반환하지 않게 적절히 작업
}

function isValidIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from User where userIdx =?) as exist;";
    //exist에 있는 내용이 반환 되는지 -> 유효성 검사

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //list안에 넣어주여아함
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    //echo json_encode($res);
    return intval($res[0]['exist']); //정확히 parsing
    //return intval($res[0]); json encode로 어떻게 생겼는지 보려고

    //return $res
    //res는 기본적으로 list형태로 반환 index안해주면 그냥 list형태로 반환
    //클라이언트 입장에서 json으로 parsing할 때 data 포맷하는 방향이 달라 주의
    //명세서에 string인지 list인지 체크
    //api 만들때 validation 매우 중요
    //validation : 있지도 않은 것을 요청했을 때 null이나 다른 값을 반환하지 않게 적절히 작업
}

function createUser($password, $userProfileUrl, $userName, $userNum, $userPhoneNum, $userLocation)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO User (password, userProfileUrl, userName, userNum, userPhoneNum, userLocation) VALUES (?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$password, $userProfileUrl, $userName, $userNum, $userPhoneNum, $userLocation]);

    $st = null;
    $pdo = null;

    return $userName;
}

function isValidName($userName){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userName like concat('%', ?, '%')) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userName]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidNum($userNum){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userNum = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

function isValidPhoneNum($userPhoneNum){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userPhoneNum= ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userPhoneNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

function createUserManner($targetIdx, $mannerIdx)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO MannerReceived (userIdx, mannerIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$targetIdx, $mannerIdx]);

    $st = null;
    $pdo = null;

    return $targetIdx;
}

function isValidMannerIdx($mannerIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Manner WHERE mannerIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$mannerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

function updateUserName($newName, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET userName = ? where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$newName, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;$pdo = null;

    return $targetIdx;
}

function deleteUser($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE User SET isDeleted = 'Y' WHERE userIdx = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;$pdo = null;

    return $userIdx;
}

function isDeletedIdx($userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userIdx = ? and isDeleted= 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
//Product
//function getProducts()
//{
//    $pdo = pdoSqlConnect();
//
//    $query = "select pi.productIdx, pi.productTitle,
//                    u.userLocation,
//                    pi.productPrice,
//                    case
//                        when (timestampdiff(year, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
//                            then concat(timestampdiff(year, if(updatedAt is null, createdAt, updatedAt), now()), '년 전')
//                        when (timestampdiff(month, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
//                            then concat(timestampdiff(month, if(updatedAt is null, createdAt, updatedAt), now()), '달 전')
//                        when (timestampdiff(day, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
//                            then concat(timestampdiff(day, if(updatedAt is null, createdAt, updatedAt), now()), '일 전')
//                        when (timestampdiff(hour, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
//                            then concat(timestampdiff(hour, if(updatedAt is null, createdAt, updatedAt), now()), '시간 전')
//                        when (timestampdiff(minute, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
//                            then concat(timestampdiff(minute, if(updatedAt is null, createdAt, updatedAt), now()), '분 전')
//                        else concat(timestampdiff(second, if(updatedAt is null, createdAt, updatedAt), now()), '초 전')
//                    end as productTimeAt,
//                    (select count(productIdx) from ProductChat where productIdx = pi.productIdx) as productChatCnt,
//                    (select count(productIdx) from ProductInterest where productIdx = pi.productIdx) as productInterestCnt
//            from ProductInfo pi
//                inner join (select userIdx, userLocation from User) as u on pi.userIdx = u.userIdx
//            where pi.productSold = 'N' and pi.productInfoState = 'S';";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}

function getProducts($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "select pi.productIdx, pi.productTitle,
                       i.imgUrl as productImgUrl,
                       u.userLocation,
                       pi.productPrice,
                       case
                            when (timestampdiff(year, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(year, if(pullUpTime is null, createdAt, pullUpTime), now()), '년 전')
                            when (timestampdiff(month, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(month, if(pullUpTime is null, createdAt, pullUpTime), now()), '달 전')
                            when (timestampdiff(day, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(day, if(pullUpTime is null, createdAt, pullUpTime), now()), '일 전')
                            when (timestampdiff(hour, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(hour, if(pullUpTime is null, createdAt, pullUpTime), now()), '시간 전')
                            when (timestampdiff(minute, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(minute, if(pullUpTime is null, createdAt, pullUpTime), now()), '분 전')
                            else concat(timestampdiff(second, if(pullUpTime is null, createdAt, pullUpTime), now()), '초 전')
                       end as productTimeAt,
                       (select count(productIdx) from ProductChat where productIdx = pi.productIdx) as productChatCnt,
                       (select count(productIdx) from ProductInterest where productIdx = pi.productIdx) as productInterestCnt
                from ProductInfo pi
                    left join (select infoIdx, min(imgIdx), imgUrl from Image where infoType = 'P' group by infoIdx) as i on pi.productIdx = i.infoIdx
                    inner join (select userIdx, userLocation from User) as u on pi.userIdx = u.userIdx
                where pi.productSold = 0 and pi.productInfoState = 1 and productTitle like concat('%', ?, '%');";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isValidProductTitle($productTitle){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductInfo WHERE productTitle like concat('%', ?, '%') AND productSold = 0 AND productInfoState = 1) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productTitle]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function getProductsByCategory($productCategoryIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select pi.productIdx, pi.productTitle,
                       i.imgUrl as productImgUrl,
                       u.userLocation,
                       pi.productPrice,
                       case
                            when (timestampdiff(year, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(year, if(pullUpTime is null, createdAt, pullUpTime), now()), '년 전')
                            when (timestampdiff(month, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(month, if(pullUpTime is null, createdAt, pullUpTime), now()), '달 전')
                            when (timestampdiff(day, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(day, if(pullUpTime is null, createdAt, pullUpTime), now()), '일 전')
                            when (timestampdiff(hour, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(hour, if(pullUpTime is null, createdAt, pullUpTime), now()), '시간 전')
                            when (timestampdiff(minute, if(pullUpTime is null, createdAt, pullUpTime), now()) > 0)
                                then concat(timestampdiff(minute, if(pullUpTime is null, createdAt, pullUpTime), now()), '분 전')
                            else concat(timestampdiff(second, if(pullUpTime is null, createdAt, pullUpTime), now()), '초 전')
                       end as productTimeAt,
                       (select count(productIdx) from ProductChat where productIdx = pi.productIdx) as productChatCnt,
                       (select count(productIdx) from ProductInterest where productIdx = pi.productIdx) as productInterestCnt
                from ProductInfo pi
                    left join (select infoIdx, min(imgIdx), imgUrl from Image where infoType = 'P' group by infoIdx) as i on pi.productIdx = i.infoIdx
                    inner join (select userIdx, userLocation from User) as u on pi.userIdx = u.userIdx
                where pi.productSold = 0 and pi.productInfoState = 1 and productCategoryIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productCategoryIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isProductInCategory($productCategoryIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductInfo WHERE productCategoryIdx = ? and productInfoState = 1 and productSold = 0) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productCategoryIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function isValidProductCategoryName($productCategoryName){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductCategory WHERE productCategoryName = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productCategoryName]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function getProductDetail($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select u.*,
                       pi.productIdx, i.imgUrl as productImgUrl, pi.productTitle,
                       (select productCategoryName from ProductCategory where productCategoryIdx = pi.productCategoryIdx) as productName,
                       case
                            when (timestampdiff(year, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
                                then concat(timestampdiff(year, if(updatedAt is null, createdAt, updatedAt), now()), '년 전')
                            when (timestampdiff(month, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
                                then concat(timestampdiff(month, if(updatedAt is null, createdAt, updatedAt), now()), '달 전')
                            when (timestampdiff(day, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
                                then concat(timestampdiff(day, if(updatedAt is null, createdAt, updatedAt), now()), '일 전')
                            when (timestampdiff(hour, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
                                then concat(timestampdiff(hour, if(updatedAt is null, createdAt, updatedAt), now()), '시간 전')
                            when (timestampdiff(minute, if(updatedAt is null, createdAt, updatedAt), now()) > 0)
                                then concat(timestampdiff(minute, if(updatedAt is null, createdAt, updatedAt), now()), '분 전')
                            else concat(timestampdiff(second, if(updatedAt is null, createdAt, updatedAt), now()), '초 전')
                       end as productTimeAt,
                       pi.productContent,
                       (select count(productIdx) from ProductChat) as productChatCnt,
                       (select count(productIdx) from ProductInterest) as productInterestCnt,
                       pi.productViewCnt,
                       if(pin.productIdx is null, 0, 1) as userInterestCheck,
                       pi.productPrice, pi.productPriceSuggest
                from ProductInfo as pi
                    left join (select infoIdx, min(imgIdx), imgUrl from Image where infoType = 'P' group by infoIdx) as i on pi.productIdx = i.infoIdx
                    inner join (select userIdx, userProfileUrl, userName, userLocation, userMannerDegree from User) as u on pi.userIdx = u.userIdx
                    left join (
                        select productIdx, uc.userIdx, userName
                        from ProductInterest
                            inner join (select userIdx, userName from User) as uc on uc.userIdx = ProductInterest.userIdx
                        where ProductInterest.useridx = 1) as pin on pi.productIdx = pin.productIdx
                where pi.productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    //list안에 넣어주여아함
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
    //return $res
    //res는 기본적으로 list형태로 반환 index안해주면 그냥 list형태로 반환
    //클라이언트 입장에서 json으로 parsing할 때 data 포맷하는 방향이 달라 주의
    //명세서에 string인지 list인지 체크
    //api 만들때 validation 매우 중요
    //validation : 있지도 않은 것을 요청했을 때 null이나 다른 값을 반환하지 않게 적절히 작업
}

function isValidProductIdx($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from ProductInfo where productIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


function createProduct($userIdx, $productTitle, $productCategoryIdx, $productPrice, $productPriceSuggest, $productContent)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ProductInfo (userIdx, productTitle, productCategoryIdx, productPrice, productPriceSuggest, productContent) VALUES (?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productTitle, $productCategoryIdx, $productPrice, $productPriceSuggest, $productContent]);

    $st = null;
    $pdo = null;

    return $productTitle;
}

function isValidProductCategory($productCategoryIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductCategory WHERE productCategoryIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productCategoryIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

function createProductReview($consumerIdx, $productIdx, $reviewContent, $productReviewImgUrl)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ProductReview(consumerIdx, productIdx, reviewContent, productReviewImgUrl) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$consumerIdx, $productIdx, $reviewContent, $productReviewImgUrl]);

    $st = null;
    $pdo = null;

    return $productIdx;
}

function isReviewExist($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductReview WHERE consumerIdx = ? and productIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}

function createProductChat($productIdx, $productChatFromIdx, $productChatContent)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ProductChat (productIdx, productChatFromIdx, productChatContent) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $productChatFromIdx, $productChatContent]);

    $st = null;
    $pdo = null;

    return $productIdx;
}

function createProductInterest($userIdx, $productIdx)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ProductInterest (userIdx, productIdx) VALUE (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx]);

    $st = null;
    $pdo = null;

    return $productIdx;
}

//function isValidProductInterest($userIdx, $productIdx){
//    $pdo = pdoSqlConnect();
//    $query = "SELECT EXISTS(SELECT * FROM ProductInterest WHERE userIdx = ? and productIdx = ?) AS exist;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$userIdx, $productIdx]);
//
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st=null;$pdo = null;
//
//    return intval($res[0]["exist"]);
//}
function updateProduct($newProductTitle, $newProductCategoryidx, $newProductPrice, $newProductPriceSuggest, $newProductContent, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInfo SET productTitle = ?, productCategoryIdx = ?, productPrice = ?, productPriceSuggest = ?, productContent = ? where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$newProductTitle, $newProductCategoryidx, $newProductPrice, $newProductPriceSuggest, $newProductContent, $targetIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $targetIdx;
}
function updateProductState($productInfoState, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInfo SET productInfoState = ? where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productInfoState, $targetIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $targetIdx;
}
function updateProductSoldState($productSoldState, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInfo SET productSold = ? where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productSoldState, $targetIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $targetIdx;
}
function updateProductPullUp($targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInfo SET pullUpTime = current_timestamp where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$targetIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $targetIdx;
}
function deleteProduct($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInfo SET isDeleted = 'Y' WHERE productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;$pdo = null;

    return $productIdx;
}
function isDeletedProductIdx($productIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductInfo WHERE productIdx = ? and isDeleted= 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function deleteInterest($userIdx, $productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductInterest SET isDeleted = 'Y' WHERE userIdx = ? and productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $productIdx;
}
function isDeletedInterest($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductInterest WHERE userIdx = ? and productIdx = ? and isDeleted= 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function isInterestExist($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductInterest WHERE userIdx= ? and productIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
//Chat
function getChats($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select userIdx, userName, userNum, userLocation from User where userName like concat('%', ?, '%');";

    $userIdx = 1;

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function getChatDetail($chatRoomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select pi.productIdx, pc.productChatRoomIdx,
                       i.imgUrl as productImgUrl,
                       pi.productTitle, pi.productPrice, pi.productSold,
                       pr.productReviewCnt,
                       if(pr.productReviewCnt is null, 0, 1) as productReview,
                       date_format(createdAt, '%Y년 %m월 %d일') as chatDate,
                       concat(date_format(createdAt, '%h:%i'), if(date_format(createdAt, '%h') > 11, ' 오후', ' 오전')) as chatTime,
                       pc.productChatFromIdx, pc.productChatContent,
                       pc.readState
                from ProductChat as pc
                    inner join (select productIdx, productTitle, productSold, productPrice from ProductInfo) as pi on pc.productIdx = pi.productIdx
                    left join (select infoIdx, min(imgIdx), imgUrl from Image where infoType = 'P' group by infoIdx) as i on pi.productIdx = i.infoIdx
                    left join (select productIdx, count(productIdx) as productReviewCnt from ProductReview) as pr on pc.productIdx = pr.productIdx
                where pc.productChatRoomIdx= ?;";

    $st = $pdo->prepare($query);
    $st->execute([$chatRoomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isValidChatRoomIdx($chatRoomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from ProductChat where productChatRoomIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$chatRoomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}
function deleteChatRoom($chatRoomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductChat SET isDeleted = 'Y' WHERE productChatRoomIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$chatRoomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $chatRoomIdx;
}
function isDeletedChatRoomIdx($chatRoomIdx) {
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductChat WHERE productChatRoomIdx = ? and isDeleted= 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$chatRoomIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
//Review
function getReviews($userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select pr.productIdx, pr.consumerIdx,
                       u.userProfileUrl, u.userName, u.userLocation,
                       pr.reviewContent,
                       case
                           when (timestampdiff(year, createdAt, now()) > 0)
                               then concat(timestampdiff(year, createdAt, now()), '년 전')
                           when (timestampdiff(month, createdAt, now()) > 0)
                               then concat(timestampdiff(month, createdAt, now()), '달 전')
                           when (timestampdiff(day, createdAt, now()) > 0)
                               then concat(timestampdiff(day, createdAt, now()), '일 전')
                           when (timestampdiff(hour, createdAt, now()) > 0)
                               then concat(timestampdiff(hour, createdAt, now()), '시간 전')
                           when (timestampdiff(minute, createdAt, now()) > 0)
                               then concat(timestampdiff(minute, createdAt, now()), '분 전')
                           else concat(timestampdiff(second, createdAt, now()), '초 전')
                       end as productReviewCreatedAt
                from ProductReview as pr
                    inner join (select userIdx, userProfileUrl, userName, userLocation from User) as u on pr.consumerIdx = u.userIdx
                    inner join (select productIdx, userIdx from ProductInfo) as pi on pr.productIdx = pi.productIdx
                where pi.userIdx = ?
                order by createdAt desc;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isReviewExistWithUserIdx($userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(
                SELECT *
                FROM ProductReview as pr
                    inner join (select productIdx, userIdx from ProductInfo) as pi on pr.productIdx = pi.productIdx
                WHERE pi.userIdx like ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function deleteReview($reviewIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ProductReview SET isDeleted = 'Y' WHERE productReviewIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $st=null;$pdo = null;

    return $reviewIdx;
}
function isReviewExistWithReviewIdx($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductReview WHERE productReviewIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function isDeletedReviewIdx($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ProductReview WHERE productReviewIdx = ? and isDeleted= 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);
}
function suggestPrice($productIdx, $userIdx, $suggestedPrice)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ProductPriceSuggest (productIdx, userIdx, suggestedPrice) VALUES (?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $userIdx, $suggestedPrice]);

    $st = null;
    $pdo = null;

    return $suggestedPrice;
}
function priceAlreadySuggested($productIdx, $userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from ProductPriceSuggest where productIdx =? and userIdx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}
function blockedUser($userIdx, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO BlockUser (userIdx, blockedUserIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $st=null;$pdo = null;

    return $userIdx;
}
//Service
function getServices($keyword)
{
    $pdo = pdoSqlConnect();

    $query = "select s.serviceIdx, s.userIdx,
                    #i.imgUrl,
                    s.serviceName, s.serviceLocation,
                    (select count(serviceIdx) from ServiceReview) as serviceReviewCnt,
                    (select count(serviceIdx) from ServiceInterest) as serviceInterestCnt
                from ServiceInfo as s
                    #inner join (select infoIdx, imgUrl, createdAt from Image where infoType = 'S' order by createdAt limit 1) as i on s.serviceIdx = i.infoIdx
                where serviceName like concat('%', ?, '%');";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isValidServiceName($keyword)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from ServiceInfo where serviceIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

function getServiceDetail($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select s.serviceIdx, s.userIdx,
                       #i.imgUrl,
                       s.serviceName, s.serviceLocation,
                       (select count(serviceIdx) from ServiceReview) as serviceReviewCnt,
                       (select count(serviceIdx) from ServiceInterest) as serviceInterestCnt,
                       if(si.serviceIdx is null, si2.userName, si.userName) as serviceInterestUserName,
                       if(si.serviceIdx is null, False, True) as userInterestCheck,
                       s.servicePhoneNo,
                       if(sa.serviceIdx is null, '', sa.servicealert) as serviceAlert,
                       if(sb.serviceIdx is null, '', sb.serviceBenefitContent) as serviceBenefitContent,
                       serviceContent,
                       serviceLatitude, serviceLongitude,
                       serviceAddress, serviceSubAddress,
                       serviceOpenTime, serviceLink, serviceLicense
                from ServiceInfo as s
                    inner join (select userIdx, userName from User) as u on s.userIdx = u.userIdx
                    #inner join (select infoIdx, imgUrl, createdAt from Image where infoType = 'S' order by createdAt) as i on s.serviceIdx = i.infoIdx
                    left join (
                        select serviceIdx, uc.userIdx, userName
                        from ServiceInterest
                            inner join (select userIdx, userName from User) as uc on uc.userIdx = ServiceInterest.userIdx
                        where ServiceInterest.useridx = 1) as si on s.serviceIdx = si.serviceIdx
                    inner join (
                        select serviceIdx, uc.userIdx, userName
                        from ServiceInterest
                            inner join (select userIdx, userName from User) as uc on uc.userIdx = ServiceInterest.userIdx
                        order by createdAt desc limit 1) as si2 on s.serviceIdx = si2.serviceIdx
                    left join (select serviceIdx, servicealert from ServiceAlert) as sa on s.serviceIdx = sa.serviceIdx
                    left join (select serviceIdx, serviceBenefitContent from ServiceBenefit) as sb on s.serviceIdx = sb.serviceIdx
                 where s.serviceIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isValidServicetIdx($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT exists(select * from ServiceInfo where serviceIdx =?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    //echo json_encode($res);
    return intval($res[0]['exist']);
}

function getServiceImage($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select infoIdx, imgUrl
                from Image
                where infoIdx = ? and infoType = 'S';";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getServicePrice($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select sp.serviceIdx, sp.serviceMenu, sp.servicePrice,
                       if(spc.servicepricecontent is null, '', spc.servicepricecontent) as servicePriceContent,
                       servicePriceRep
                from ServicePrice sp
                    left join (select servicepriceIdx, servicePriceContent from ServicePriceContent) as spc on sp.servicePriceIdx = spc.servicePriceIdx
                where sp.serviceIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getServiceToDo($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select serviceIdx, serviceToDO from ServiceToDo where serviceIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getServiceReview($serviceIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select sv.serviceReviewIdx,
                       u.userIdx, userProfileUrl, userName, userLocation, userNeighborConfirmNum,
                       sv.serviceReview, sv.serviceReviewLike,
                       si.serviceName,
                       src.serviceReviewComment,
                       date_format(src.createdAt, '%Y.%m.%d') as serviceReviewCommentDate
                from ServiceReview sv
                    inner join (select userIdx, userProfileUrl, userName, userLocation, userNeighborConfirmNum from User) as u on sv.consumerIdx = u.userIdx
                    inner join (select serviceIdx, serviceName from ServiceInfo) as si on sv.serviceIdx = si.serviceIdx
                    inner join ServiceReviewComment as src on sv.serviceReviewIdx = src.serviceReviewIdx
                where sv.serviceIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$serviceIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function createService($userIdx, $serviceName, $servicePhoneNo, $serviceCategory, $serviceContent,
                         $serviceLocation, $serviceLatitude, $serviceLongitude, $serviceAddress, $serviceSubAddress,
                         $serviceOpenTime, $serviceLink, $serviceLicense)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO ServiceInfo (userIdx, serviceName, servicePhoneNo, serviceCategory, serviceContent,
                         serviceLocation, serviceLatitude, serviceLongitude, serviceAddress, serviceSubAddress,
                         serviceOpenTime, serviceLink, serviceLicense) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $serviceName, $servicePhoneNo, $serviceCategory, $serviceContent,
        $serviceLocation, $serviceLatitude, $serviceLongitude, $serviceAddress, $serviceSubAddress,
        $serviceOpenTime, $serviceLink, $serviceLicense]);

    $st = null;
    $pdo = null;

    return $userName;
}

// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
