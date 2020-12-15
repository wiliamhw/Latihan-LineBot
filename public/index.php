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
                        $result = $bot->replyMessage($event['replyToken'], $imageMessageBuilder);
                        // $multiMessageBuilder->add($imageMessageBuilder);

                    } else if (lcfirst($event['message']['text']) == 'audio') {
                        // send audio
                        $audioMessageBuilder = new AudioMessageBuilder('https://open.spotify.com/track/0cSkn2l67csUljEy0EEBPn', '4:35');
                        $result = $bot->replyMessage($event['replyToken'], $audioMessageBuilder);
                        // $multiMessageBuilder->add($audioMessageBuilder);

                    } else if (lcfirst($event['message']['text']) == 'video') {
                        // send video
                        $videoMessageBuilder = new VideoMessageBuilder('https://youtu.be/bpKoG_LLBaM', 'https://i.ytimg.com/vi/bpKoG_LLBaM/hqdefault.jpg?sqp=-oaymwEZCOADEI4CSFXyq4qpAwsIARUAAIhCGAFwAQ==&rs=AOn4CLA8lWC19xbLTXoRwl5tr1kOU7tkCg');
                        $result = $bot->replyMessage($event['replyToken'], $videoMessageBuilder);
                        // $multiMessageBuilder->add($videoMessageBuilder);

                    } else if (lcfirst($event['message']['text']) == 'hi') {
                        // send text
                        $textMessageBuilder2 = new TextMessageBuilder("Hello");
                        $multiMessageBuilder->add($textMessageBuilder2);
                    }

                    // store result
                    if (
                        lcfirst($event['message']['text']) != 'gambar' and
                        lcfirst($event['message']['text']) != 'audio'  and
                        lcfirst($event['message']['text']) != 'video'
                    ) {
                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
                    }

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

$app->run();
