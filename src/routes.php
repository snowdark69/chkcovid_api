<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;
use Tuupola\Base62;

return function (App $app) {
    $container = $app->getContainer();

    //$app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        $app->get('/', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        //$container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        //return $container->get('renderer')->render($response, 'index.phtml', $args);
        
        $settings = $container->get('settings')['about']; 
        return $this->response->withJson(['AppName' => $settings['AppName'],'Version' => $settings['version'], ]);

    });

    // Login และ รับ Token
    $app->post('/login', function (Request $request, Response $response, array $args) use ($container){
 
        $input = $request->getParsedBody();
        
        $now = new DateTime();
        $future = new DateTime("+2 minutes");
        $server = $request->getServerParams();
        $jti = (new Base62)->encode(random_bytes(16));
        $payload = [
            "cid" => $input['cid'],
            "vstdate" => $input['vstdate'],
            "iat" => $now->getTimestamp(),
            "exp" => $future->getTimestamp(),
            "jti" => $jti, 
            "sub" => $server["PHP_AUTH_USER"]
        ];
        //$password = sha1($input['password']);

        $sql = "SELECT * FROM vn_stat WHERE cid=:cid AND vstdate=:vstdate LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("cid", $input['cid']);
        //$sth->bindParam("password", $password);
        $sth->bindParam("vstdate", $input['vstdate']);
        $sth->execute();

        
        $count = $sth->rowCount();
        if($count){
            //$user = $sth->fetchObject();
            $settings = $this->get('settings'); // get settings array. "date_time" => date("Y-m-d H:i:s")
            //$token = JWT::encode(['cid' => $user->cid, 'vstdate' => $user->vstdate], $settings['jwt']['secret'], "HS256");
            //$token = JWT::encode(["date_time" => date("Y-m-d H:i:s") ,'cid' => $user->cid, 'vstdate' => $user->vstdate], $settings['jwt']['secret'], "HS256");
            $token = JWT::encode($payload, $settings['jwt']['secret'] , "HS256");
            $data["token"] = $token;
            $data["expires"] = $future->getTimestamp();
            //return $this->response->withJson(['token' => $token]);
            return $response->withStatus(201)
                ->withHeader("Content-Type" , "application/json")
                ->write(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }else{
            return $this->response->withJson(['error' => true, 'message' => 'These credentials do not match our records.']);
        }
    });


    $app->group('/api/v1', function () use ($app) {

        $container = $app->getContainer();

        // Get Data By hn (Method GET)
        $app->get('/GetVnStat/{hn}/{vstdate}', function (Request $request, Response $response, array $args) use ($container) {
            $sql = "SELECT * FROM vn_stat WHERE hn='$args[hn]' and vstdate ='$args[vstdate]' ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $product = $stmt->fetchAll();
            if (count($product)) {
                $input = [
                    'status' => 'Success',
                    'message' => 'Read Data Success',
                    'data' => $product
                ];
            } else {
                $input = [
                    'status' => 'Fail',
                    'message' => 'Empty Data',
                    'data' => $product
                ];
            }

            return $this->response->withJson($input);
        });

        // Get Data By hn (Method GET)
        $app->get('/GetLabCovid/{cid}/{vstdate}', function (Request $request, Response $response, array $args) use ($container) {
            $sql = "select lo.lab_order_number,li.lab_items_code,md5(vn.cid) as hcid,lo.lab_items_name_ref,lo.lab_order_remark,
                    lo.lab_order_result,lh.result_note,vn.vstdate,
                    lh.order_date,lh.order_time,lh.report_date,lh.report_time,
                    op.name as reporter_name,op2.name as approve_staff,NOW()
                    from lab_order lo 
                    INNER JOIN lab_items li on li.lab_items_code = lo.lab_items_code 
                    and li.lab_items_code in ('3850','3852','3854','4074','4076','4084','4103','4113','4143','4145','4760','4749','4745','4752','4758')
                    LEFT JOIN lab_head lh on lh.lab_order_number = lo.lab_order_number
                    LEFT JOIN vn_stat vn on vn.vn = lh.vn
                    LEFT JOIN opduser op on op.loginname = lh.reporter_name
                    LEFT JOIN opduser op2 on op2.loginname = lh.approve_staff
                    where lo.lab_order_result is not null
                    and lh.department = 'OPD'
                    and vn.cid ='$args[cid]'
                    and vn.vstdate ='$args[vstdate]'
                    order by lo.lab_order_number desc limit 1 ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $product = $stmt->fetchAll();
            if (count($product)) {
                $input = [
                    'status' => 'Success',
                    'message' => 'Read Data Success',
                    'data' => $product
                ];
            } else {
                $input = [
                    'status' => 'Fail',
                    'message' => 'Empty Data',
                    'data' => $product
                ];
            }

            return $this->response->withJson($input);
        });

    });
};
