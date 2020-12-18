<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// If request simulation --> true
// else set to false
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "LNMWbKHQ0JXLvPSfRLV3HM3MtZ+CnUph6nY5d48+4i1TKm70NrxU3IkiawzBUMxM5zpnYYW3oL4dMdwDchCCtAisNcx+TrGPjrUl5kIOApn/zztB5BBgMRrXy6xbD+6vyUFiF6bRnWorAMbSJPqzgQdB04t89/1O/w1cDnyilFU=";
$channel_secret = "ba0817daac4a0e92ce87f1dd70b9aa5b";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$app = AppFactory::create();
$app->setBasePath("/public");

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});

// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');

    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);

    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    // store JSON data
    $data = json_decode($body, true);

    // Reply text message
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message') {
                if ($event['message']['type'] == 'text') {
                    // send same message as reply to user
                    // $result = $bot->replyText($event['replyToken'], $event['message']['text']);

                    // or we can use replyMessage() instead to send reply message
                    // make text
                    $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);

                    // make multiMessage
                    $multiMessageBuilder = new multiMessageBuilder();
                    $multiMessageBuilder->add($textMessageBuilder);

                    if (lcfirst($event['message']['text']) == 'stiker') {
                        // send sticker
                        $packageId = 1;
                        $stickerId = 2;
                        $stickerMessageBuilder = new StickerMessageBuilder($packageId, $stickerId);
                        $multiMessageBuilder->add($stickerMessageBuilder);
                    } else if (lcfirst($event['message']['text']) == 'gambar') {
                        // send image
                        $imageMessageBuilder = new ImageMessageBuilder('https://lh3.googleusercontent.com/fife/ABSRlIrfeoZODN6nVh5rfEeFe37XAx5qCzKU8R_XCQxCTHxZJNZ5O0g2TZpx3QAUOiuD6InvyvY8sMOA1wDMUOXHlX3O0j8QOdhi9r4Di6KPvYS9bh8xAXjI1CWxJ4uSZtJcKOwmL3cWTT1rGozjBP1ADOHO4orTl4V20yCwRSncrHO3N1tJKNG7l418j1Ln2Z7n2S7KAwGJmp7xBqdN5gH6f4fcNzFT_idNBHfWLPwLtmAPHwIxTXhVOqqSa35rj-JRHSvAjEI8MXGNGmJGWDwwxLTR8m7h207GgN2I8mkGTmG3UXocXp9YhoGLWuJ-BJfqYbqM_QUQtXUFr76dpKahyjK6u0HMxC90gWCxlAqvGatkdsRBdndistG7zxh3CmQQW8vZICNNz-E9kCePSsYBnGdQYxuAiUluMzGKue_5EMpIISpA8Ykr24ybmz__S4OqVKjOJzZvOFSUlbTYQRzRl_dIa-lcjkse136dinCbkBMLveu2pSmT0QZ6W4NVKKvXgP_HGOq6wnkuJX-w9_nLXGz4H5gB1r0YKwzeNJWtfUq3665CunwXWByiDjoDWowSD3RZRSRrhIg8_Uh29qswhFxro1v578iSuzipsNhhq_s_58KNZsaOvhekQcgzZohwaWMW4ZjjExJStMUiXvOxXwrYUF4TJf4j9TqOLvY9HTE2vBGthQnpt8JVr0l3-CSCp-uXX4e839TWNuaxgAjJ67PYcF23mdZRcw=w958-h410-ft', 'https://lh3.googleusercontent.com/fife/ABSRlIrfeoZODN6nVh5rfEeFe37XAx5qCzKU8R_XCQxCTHxZJNZ5O0g2TZpx3QAUOiuD6InvyvY8sMOA1wDMUOXHlX3O0j8QOdhi9r4Di6KPvYS9bh8xAXjI1CWxJ4uSZtJcKOwmL3cWTT1rGozjBP1ADOHO4orTl4V20yCwRSncrHO3N1tJKNG7l418j1Ln2Z7n2S7KAwGJmp7xBqdN5gH6f4fcNzFT_idNBHfWLPwLtmAPHwIxTXhVOqqSa35rj-JRHSvAjEI8MXGNGmJGWDwwxLTR8m7h207GgN2I8mkGTmG3UXocXp9YhoGLWuJ-BJfqYbqM_QUQtXUFr76dpKahyjK6u0HMxC90gWCxlAqvGatkdsRBdndistG7zxh3CmQQW8vZICNNz-E9kCePSsYBnGdQYxuAiUluMzGKue_5EMpIISpA8Ykr24ybmz__S4OqVKjOJzZvOFSUlbTYQRzRl_dIa-lcjkse136dinCbkBMLveu2pSmT0QZ6W4NVKKvXgP_HGOq6wnkuJX-w9_nLXGz4H5gB1r0YKwzeNJWtfUq3665CunwXWByiDjoDWowSD3RZRSRrhIg8_Uh29qswhFxro1v578iSuzipsNhhq_s_58KNZsaOvhekQcgzZohwaWMW4ZjjExJStMUiXvOxXwrYUF4TJf4j9TqOLvY9HTE2vBGthQnpt8JVr0l3-CSCp-uXX4e839TWNuaxgAjJ67PYcF23mdZRcw=w958-h410-ft');
                        $multiMessageBuilder->add($imageMessageBuilder);
                    } else if (lcfirst($event['message']['text']) == 'youtube') {
                        // send text
                        $textMessageBuilder2 = new TextMessageBuilder("https://youtu.be/bpKoG_LLBaM");
                        $multiMessageBuilder->add($textMessageBuilder2);
                    } else if (lcfirst($event['message']['text']) == 'video') {
                        // send video
                        $videoMessageBuilder  = new VideoMessageBuilder('https://r6---sn-xmjxajvh-jb3zl.googlevideo.com/videoplayback?expire=1608302486&ei=NmvcX8icGJjmvwTV166wAQ&ip=103.3.222.244&id=o-AOdXh0slPy9r88COgfkOS9xGu9FNBAiU7oUbyLquAOim&itag=22&source=youtube&requiressl=yes&mh=g-&mm=31%2C26&mn=sn-xmjxajvh-jb3zl%2Csn-i3belnel&ms=au%2Conr&mv=m&mvi=6&pl=24&initcwndbps=958750&vprv=1&mime=video%2Fmp4&ns=tC0m7908btEFeclbt1qgF7IF&ratebypass=yes&dur=30.789&lmt=1608277850523091&mt=1608280613&fvip=6&c=WEB&txp=6316222&n=7CnfRE-FDq3CDdjpa&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cvprv%2Cmime%2Cns%2Cratebypass%2Cdur%2Clmt&sig=AOq0QJ8wRQIhAMSpE6JvtCBYi2UGIJdjAesoKHtA1GBSvwI6yviydF0QAiADCbCUoRv9qtmQfo9vujWQEJx578nwawzYvRvrMPd3eA%3D%3D&lsparams=mh%2Cmm%2Cmn%2Cms%2Cmv%2Cmvi%2Cpl%2Cinitcwndbps&lsig=AG3C_xAwRAIgZW2GI_4IY1JaaZMe2MYnGJsmKMijVAvPuHf3Daw-pmoCIF3uzy2pVkr_y-cw7aUH3ufw5bj1bOsmsKi_zDqcKTVq', 'https://i.ytimg.com/vi/f0u0KQGfaec/hq720.jpg?sqp=-oaymwEZCOgCEMoBSFXyq4qpAwsIARUAAIhCGAFwAQ==&rs=AOn4CLB9jpU2lKWsQ3CfG36L40ZhcfGWaA');
                        $multiMessageBuilder->add($videoMessageBuilder);
                    }

                    // store result
                    $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    // write to JSON
                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});

// buat push message
$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to a user
    $userId = 'U03f2e64bdbc12c90ed48141c3a51ee39';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);

    // sticker example
    // $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
    // $result = &bot->pushMessage($userId, $stickerMessageBuilder);

    $response->getBody()->write("Pesan push berhasil dikirim!");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->get('/multicast', function($req, $response) use ($bot)
{
    // list of users
    $userList = ['U03f2e64bdbc12c90ed48141c3a51ee39'];
 
    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);
 
 
    $response->getBody()->write("Pesan multicast berhasil dikirim");
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->run();
